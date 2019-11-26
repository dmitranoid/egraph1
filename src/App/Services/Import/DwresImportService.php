<?php


namespace App\Services\Import;


use App\Exceptions\ApplicationException;
use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Query;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class DwresImportService implements ImportServiceInterface
{

    /** @var Query */
    private $srcFPdo;

    /** @var Query */
    private $dstFPdo;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PDO $srcPdo, PDO $dstPdo, LoggerInterface $logger)
    {
        $this->srcFPdo = new Query($srcPdo);
        $this->dstFPdo = new Query($dstPdo);
        $this->logger = $logger;
    }

    /**
     * Импортировать файл Dwres
     * @throws ApplicationException
     */
    public function import()
    {
        // регионы (РЭСы) в dwres
        $regionList = $this->srcFPdo
            ->from('res r')
            ->innerJoin('filial f on r.id_filial = f.id')
            ->innerJoin('energosys e on f.id_energosys = e.id')
            ->select('e.name energosys_name, f.name filial_name, r.name res_name, r.code res_code');

        foreach ($regionList as $region) {
            //проверяем есть ли код РЭС, без него нельзя привязать данные
            if (empty($region['RES_CODE'])) {
                throw new ApplicationException('не задан код РЭС для ' . implode('/', [$region['ENERGOSYS_NAME'], $region['FILIAL_NAME'], $region['RES_NAME']]));
            }
            // TODO нужно ли удалять старые данные ???
            // TODO возможно нужно сделать флаг locked у данных, чтобы не удалять заведенные не из dwres
            // удаляем данные РЭСа
            // связи
            $this->dstFPdo
                ->deleteFrom('main.energoLink')
                ->where('code_region', $region['RES_CODE'])
                ->execute();
            //точки подключения
            $this->dstFPdo->getPdo()->exec(sprintf('
                delete from energoConnection 
                where code_energoObject in (
                    select obj.code 
                    from energoObject obj
                    where code_region = \'%s\'
                )
                ', $region['RES_CODE'])
            );
            // объекты
            $this->dstFPdo
                ->deleteFrom('energoObject')
                ->where('code_region', $region['RES_CODE'])
                ->execute();
        }

        // выбираем  РЭС - ПС - фидер - ТП/РП
        $importData = $this->srcFPdo
            ->from('res')
            ->innerJoin('pst on pst.id_res = res.id')
            ->innerJoin('fider on fider.id_pst = pst.id')
            ->innerJoin('tp on tp.id_fider = fider.id')
            ->select('res.id id_res, res.id_filial, res.name res_name, res.code res_code')
            ->select('pst.id id_pst, pst.name pst_name, pst.shortname pst_shortname')
            ->select('fider.id id_fider, fider.name fider_name')
            ->select('tp.id id_tp, tp.name tp_name, tp.code_tp tp_code, tp.obj_type tp_obj_type, tp.sobj_type tp_sobj_type, tp.code_tp')
            ->order('res.id, res.id_filial, pst.id, fider.id, tp.id');

        $prevRes = $prevPst = $prevPstCode = $prevFider = $prevTp = null;
        /** @var string $errorPst Имя последней ошибочной ПС */
        $errorPst = null;

        foreach ($importData as $item) {
            /**
             * @comment
             * так как в некоторых РЭС фидер именуется как ВЛ-750
             * а в некоторых просто 750, обрабатываем до цифр
             * т.е. ВЛ-750 -> 750
             */
            $item['FIDER_NAME'] = trim(str_replace(['ВЛ-', 'КЛ-'], '', $item['FIDER_NAME']));

            $item = array_map('trim', $item);
            // сменился РЭС
            if (strcmp($prevRes, $item['RES_CODE']) != 0) {
                $prevRes = $item['RES_CODE'];
                $prevPst = $prevPstCode = $prevFider = $prevTp = null;
            }

            // сменилась ПС
            if (strcmp($prevPst, $item['PST_NAME']) != 0) {

                $substationGpo = $this->substationGpoByName($item['PST_NAME']);

                if (empty($substationGpo)) {
                    // если не нашли, пропускаем вставку
                    if ($errorPst != $item['PST_NAME']) {
                        // повторные сообщения не печатаем
                        $this->logger->error('ПС \'pst_name\' не найдена в справочнике ОДУ', ['pst_name' => $item['PST_NAME']]);
                    }
                    $errorPst = $item['PST_NAME'];
                    continue;
                }
                $prevPst = $item['PST_NAME'];
                $prevPstCode = $substationGpo['code'];
                // new EnergoObject()
                try {
                    $this->dstFPdo
                        ->insertInto('energoObject')
                        ->values([
                            'code_region' => $prevRes,
                            'code' => $prevPstCode,
                            'localcode' => $prevPstCode,
                            'name' => $substationGpo['name'],
                            'type' => $substationGpo['type'],
                            'voltage' => $substationGpo['u'],
                            'status' => true
                        ])
                        ->execute();
                } catch (\Envms\FluentPDO\Exception $e) {
                    throw new ApplicationException('ошибка при импорте из dwres', 0, $e);
                }

                $prevFider = $prevTp = null;
            }

            // смена фидера
            if (strcmp($prevFider, $item['FIDER_NAME']) != 0) {
                try {
                    // точка подключения со стороны ПС
                    // new EnergoConnection()
                    $fiderConnection = $prevPstCode . '.ф' . $item['FIDER_NAME']; // fider code

                    $this->dstFPdo
                        ->insertInto('energoConnection')
                        ->values([
                            'code_energoObject' => $prevPstCode,
                            'code' => $fiderConnection,
                            'name' => $item['PST_NAME'] . '/' . $item['FIDER_NAME'],
                            'voltage' => '',  // TODO напряжение
                            'status' => true
                        ])
                        ->execute();
                } catch (PDOException $e) {
                    throw new ApplicationException('import Error', 0, $e);
                }

                $prevFider = $item['FIDER_NAME'];
                $prevTp = null;
            }

            // смена ТП/КТП
            // new EnergoObject()
            $tpCodeFull = $prevRes . '-' . $item['TP_CODE'];
            $this->dstFPdo
                ->insertInto('energoObject')
                ->values([
                    'code_region' => $prevRes,
                    'code' => $tpCodeFull,
                    'localcode' => $item['TP_CODE'],
                    'name' => $item['TP_NAME'],
                    'type' => $item['TP_SOBJ_TYPE'],
                    'voltage' => '10',   // TODO напряжение нужно смотреть на трансформаторе
                    'status' => true
                ])
                ->execute();

            // точка подключения к ПС со стороны ТП
            // new EnergoConnection()

            $tpConnection = $tpCodeFull . '.осн';

            $this->dstFPdo
                ->insertInto('energoConnection')
                ->values([
                    'code_energoObject' => $tpCodeFull,
                    'code' => $tpConnection,
                    'name' => $item['FIDER_NAME'] . '/' . $item['TP_NAME'],
                    'voltage' => '10',      // TODO напряжение нужно смотреть на трансформаторе
                    'status' => true
                ])
                ->execute();

            // фидер к ТП
            // new EnergoLink()
            $this->dstFPdo
                ->insertInto('energoLink')
                ->values([
                    'code_srcConnection' => $fiderConnection ?? '(null)',
                    'code_dstConnection' => $tpConnection,
                    'code_region' => $prevRes,
                    'code' => $item['FIDER_NAME'] . '/' . $item['TP_NAME'],
                    'name' => 'BЛ',
                    'status' => true
                ])
                ->execute();
        }
    }

    private function substationGpoByName($substationName): ?array
    {
        $query = $this->dstFPdo
            ->from('gpo.gpops')
            ->select('*', true)
            ->where('name', mb_strtoupper($substationName));
        $substation = $query->fetch();
        return is_array($substation) ? $substation : null;
    }
}