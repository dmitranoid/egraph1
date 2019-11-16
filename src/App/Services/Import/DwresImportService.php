<?php


namespace App\Services\Import;


use App\Exceptions\ApplicationException;
use Envms\FluentPDO\Query;
use PDO;
use PDOException;

class DwresImportService implements ImportServiceInterface
{

    /** @var Query */
    private $srcFPdo;

    /** @var Query */
    private $dstFPdo;

    public function __construct(PDO $srcPdo, PDO $dstPdo)
    {
        $this->srcFPdo = new Query($srcPdo);
        $this->dstFPdo = new Query($dstPdo);
    }

    public function doFullImport()
    {
        // TODO удалить старые данные ???


        $importData = $this->srcFPdo
            ->from('res')
            ->innerJoin('pst on pst.id_res = res.id')
            ->innerJoin('fider on fider.id_pst = pst.id')
            ->innerJoin('tp on tp.id_fider = fider.id')
            ->order('res.id, res.id_filial, pst.id, fider.id, tp.id')
            ->select('res.id id_res, res.id_filial, res.name res_name, res.code')
            ->select('pst.id id_pst,  pst.name pst_name, pst.shortname pst_shortname')
            ->select('fider.id id_fider, fider.name fider_name')
            ->select('tp.id id_tp, tp.name tp_name, tp.obj_type tp_obj_type, tp.sobj_type tp_sobj_type, tp.code_tp');


        $prevRes = $prevPst = $prevFider = $prevTp = null;

        foreach ($importData as $item) {

            $a = $item;
            // сменилсяРЭС
            if (strcmp($prevRes, $item['RES_NAME']) != 0) {
                $prevRes = $item['RES_NAME'];
                $prevPst = $prevFider = $prevTp = null;
            }

            // сменилась ПС
            if (strcmp($prevPst, $item['PST_NAME']) != 0) {
                try {
                    // new EnergoObject()
                    $this->dstFPdo
                        ->insertInto('energoObject')
                        ->values([
                            'id_res' => $item['RES_NAME'],
                            'code' => $item['PST_NAME'],
                            'name' => $item['PST_NAME'],
                            'type' => 'ПС',
                            'voltage' => '',
                            'status' => true
                        ])
                        ->execute();
                } catch (PDOException $e) {
                    throw new ApplicationException('import Error', 0, $e);
                }
                $prevPst = $item['PST_NAME'];
                $prevFider = $prevTp = null;
            }

            // смена фидера
            if (strcmp($prevFider, $item['FIDER_NAME']) != 0) {
                try {
                    // точка подключения со стороны ПС
                    // new EnergoConnection()
                    $this->dstFPdo
                        ->insertInto('energoConnection')
                        ->values([
                            'id_energoObject' => $prevPst,
                            'code' => $item['FIDER_NAME'],
                            'name' => $item['PST_NAME'] . '-' . $item['FIDER_NAME'],
                            'voltage' => '',
                            'status' => true
                        ])
                        ->execute();
                } catch (PDOException $e) {
                    throw new ApplicationException('import Error', 0, $e);
                }

                $prevFider = $item['FIDER_NAME'];
                $fiderConnection = $item['FIDER_NAME']; // fider code
                $prevTp = null;
            }

            // смена ТП/КТП
            // new EnergoObject()
            $this->dstFPdo
                ->insertInto('energoObject')
                ->values([
                    'id_res' => $prevRes,
                    'code' => $item['TP_NAME'],
                    'name' => $item['TP_NAME'],
                    'type' => $item['TP_SOBJ_TYPE'],
                    'voltage' => '',
                    'status' => true
                ])
                ->execute();

            // точка подключения к ПС со стороны ТП
            // new EnergoConnection()
            $this->dstFPdo
                ->insertInto('energoConnection')
                ->values([
                    'id_energoObject' =>$item['TP_NAME'],
                    'code' => $item['TP_NAME'],
                    'name' => $item['FIDER_NAME'] . '-' . $item['TP_NAME'] ,
                    'voltage' => '',
                    'status' => true
                ])
                ->execute();

            $tpConnection = $item['TP_NAME'];

            // линия от фидера к ТП
            // new EnergoLink()
            $this->dstFPdo
                ->insertInto('energoLink')
                ->values([
                    'id_srcConnection' => $fiderConnection ?? '(null)',
                    'id_dstConnection' => $tpConnection,
                    'code' => $item['FIDER_NAME'] . ' / ' . $item['TP_NAME'],
                    'name' => 'BЛ',
                    'status' => true
                ])
                ->execute();
        }
    }
}