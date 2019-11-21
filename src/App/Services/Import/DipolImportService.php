<?php
declare(strict_types=1);

namespace App\Services\Import;


use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Query;
use PDO;
use Psr\Log\LoggerInterface;

final class DipolImportService implements ImportServiceInterface
{
    protected $regionMatching = [
        'gan' => ['dipol' => '1', 'dipolFull' => '5051511', 'dwres' => '111111'],
        'lah' => ['dipol' => '2', 'dipolFull' => '5051512', 'dwres' => '111112'],
        'barg' => ['dipol' => '3', 'dipolFull' => '5051513', 'dwres' => '111113'],
        'iva' => ['dipol' => '4', 'dipolFull' => '5051514', 'dwres' => '111114'],
        'ber' => ['dipol' => '5', 'dipolFull' => '5051515', 'dwres' => '111115'],
        'bars' => ['dipol' => '6', 'dipolFull' => '5051516', 'dwres' => '111116'],
    ];

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
        // TODO костыль sqlite3 uppercase, вынести в отдельный модуль
        $dstPdo->sqliteCreateFunction('php_upper', function ($string) {
            return mb_strtoupper($string);
        }, 1, PDO::SQLITE_DETERMINISTIC);
        $dstPdo->sqliteCreateFunction('php_lower', function ($string) {
            return mb_strtolower($string);
        }, 1, PDO::SQLITE_DETERMINISTIC);
        $dstPdo->sqliteCreateFunction('php_strcmp',
            function ($string1, $string2) {
                $result = strcmp(mb_strtolower($string1), mb_strtolower($string2));
                return $result;
            },
            2,
            PDO::SQLITE_DETERMINISTIC
        );
        $this->logger = $logger;
    }

    public function updateGeoCoords($region = null)
    {
        $this->updateSubstCoords($region);
        $this->updateTpCoords($region);
    }

    /**
     * Координаты подстанций
     * @param string|null $region код района ЭС или null для всех
     * @throws Exception
     */
    protected function updateSubstCoords($region = null)
    {
        $regionsDipol = $this->srcFPdo
            ->from('PDEPARTMENTS')
            ->select('PCONCERN_CODE, PRUP_CODE, PFAS_CODE, PDEPARTMENT_CODE, PDEPARTMENT_NAME, PDEPARTMENT_TYPE');
        $regionsDipol = $regionsDipol->fetchAll();

        $substationsDipol = $this->srcFPdo
            ->from('PSUBSTATIONS')
            ->select('PRUP_CODE, PFAS_CODE, P_CODE, P_NAME, P_VOLTAGE, P_TYPE, LATITUDE_X3, LONGITUDE_X3');
        $substationsDipol = $substationsDipol->fetchAll();

        foreach ($substationsDipol as $substDipol) {
            $substationForUpdate = $this->dstFPdo
                ->from('energoObject')
                ->where('type', 'ПС')
                ->where('php_strcmp(name, ?) = 0', $substDipol['P_NAME'])
                ->select('code_region, code, name');
            if (!empty($region)) {
                // по РЭС
                $substationForUpdate->where('code_region', [$region]);
            }
            $substationForUpdate = $substationForUpdate->fetch();

            if (!empty($substationForUpdate)) {
                //обновляем координаты ПС
                if (!(empty($substDipol['LATITUDE_X3']) || empty($substDipol['LONGITUDE_X3']))) {
                    $coordsUpdated = $this->dstFPdo
                        ->update('geoCoords', ['latitude' => $substDipol['LATITUDE_X3'], 'longitude' => $substDipol['LONGITUDE_X3']])
                        ->where('code_energoObject', $substationForUpdate['code'])
                        ->execute();
                    if (!$coordsUpdated) {
                        $this->dstFPdo
                            ->insertInto('geoCoords',
                                [
                                    'code_energoObject' => $substationForUpdate['code'],
                                    'latitude' => $substDipol['LATITUDE_X3'],
                                    'longitude' => $substDipol['LONGITUDE_X3']
                                ])
                            ->execute();
                    }
                } else {
                    $this->logger->error('не заполнены координаты в Диполь для ' . $substationForUpdate['code'] . '-' . $substationForUpdate['name']);
                }
                // TODO пока класс напряжения берем из Dipol, надо подумать откуда правильнее
                $this->dstFPdo
                    ->update('energoObject', ['voltage' => $substDipol['P_VOLTAGE']])
                    ->where('code', $substationForUpdate['code'])
                    ->execute();
            }
        }
    }

    protected function updateTpCoords($region)
    {
        $regionsDipol = $this->srcFPdo
            ->from('PDEPARTMENTS')
            ->select('PCONCERN_CODE, PRUP_CODE, PFAS_CODE, PDEPARTMENT_CODE, PDEPARTMENT_NAME, PDEPARTMENT_TYPE');
        $regionsDipol = $regionsDipol->fetchAll();

        $tpPdoWhereDipolRegions = [];
        if (!empty($region)) {
            if (is_array($region)) {
                foreach ($region as $dwresRegion) {
                    $tpPdoWhereDipolRegions[] = $this->dwres2dipolFull($dwresRegion);
                }
            } else {
                $tpPdoWhereDipolRegions[] = $this->dwres2dipolFull($region);
            }
        }
        // получаем ТП из диполя
        $tpDipolList = $this->srcFPdo
            ->from('tp')
            ->leftJoin('pdocs on pdocs pdocs.doc_code = tp.doc_code')
            ->leftJoin('tp_sys_types on tp.substation_type_id = tp_sys_types.id')
            ->select('tp.doc_code, type_txt tp_type, pdocs.doc_name tp_no, address, tp_num, line_voltage, latitude_x3, longitude_x3')
            ->where('template_code', 'TP');
        $tpDipolList = $tpDipolList->fetchAll();
        // фильтруем по РЭС
        if (!empty($region)) {
            $tpDipolList = array_filter(
                $tpDipolList,
                function ($item) use ($tpPdoWhereDipolRegions){
                    return in_array($this->dipolRegionFromDocCode($item['doc_code']), $tpPdoWhereDipolRegions);
                });
        }

        // TODO может получиться что несколько ТП с одинаковым номером в разных РЭСах если они попадают в фильтр, посмотреть
        $tpForUpdate = $this->dstFPdo
            ->from('energoObject')
            ->where('type', 'ТП')
            ->where('php_strcmp(code, ?) = 0', sprintf('%s-%s', $tpDipol['tp_type'], $tpDipol['tp_no']))
            ->where('code_region', $region)
            ->select('code_region, code, name')
            ->fetch();
        if (!empty($tpForUpdate))
    }

    private function dwres2dipolFull($code): ?string
    {
        foreach ($this->regionMatching as $name => $item) {
            if (strcmp($item['dwres'], $code)) {
                return $item['dipolFull'];
            }
        }
        return null;
    }

    private function dwres2dipol($code): ?string
    {
        foreach ($this->regionMatching as $name => $item) {
            if (strcmp($item['dwres'], $code)) {
                return $item['dipol'];
            }
        }
        return null;
    }

    private function dipol2dwres($code): ?string
    {
        foreach ($this->regionMatching as $name => $item) {
            if (strcmp($item['dipol'], $code)) {
                return $item['dwres'];
            }
        }
        return null;
    }

    /**  dipol doccode 50515101-TP-279 -> 50515101
     * @param $docCode
     * @return string|null
     */
    private function dipolRegionFromDocCode($docCode):?string
    {
        $code = substr($docCode, 0, strpos($docCode, '-')-1);
        // TODO на всякий случай не забыть проверить на разных РЭСах
        return strlen($code) >=8 ? $code : null;
    }
}