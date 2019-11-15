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

            // сменилсяРЭС
            if (strcmp($prevRes, $item['res_name']) != 0) {
                $prevRes = $item['res_name'];
                $prevPst = $prevFider = $prevTp = null;
            }

            // сменилась ПС
            if (strcmp($prevPst, $item['pst_name']) != 0) {
                try {
                    // new EnergoObject()
                    $this->dstFPdo
                        ->insertInto('energoObject')
                        ->values([
                            'id_res' => $item['id_res'],
                            'code' => $item['pst_name'],
                            'name' => $item['pst_name'],
                            'type' => 'ПС',
                            'voltage' => '',
                            'status' => true
                        ])
                        ->execute();
                } catch (PDOException $e) {
                    throw new ApplicationException('import Error', 0, $e);
                }
                $prevPst = $item['pst_name'];
                $prevFider = $prevTp = null;
            }

            // смена фидера
            if (strcmp($prevFider, $item['fider_name']) != 0) {
                try {
                    // new EnergoConnection()
                    $this->dstFPdo
                        ->insertInto('energoConnection')
                        ->values([
                            'id_energoObject' => $prevPst,
                            'code' => $item['fider_name'],
                            'name' => $item['fider_name'],
                            'voltage' => '',
                            'status' => true
                        ])
                        ->execute();
                } catch (PDOException $e) {
                    throw new ApplicationException('import Error', 0, $e);
                }

                $prevFider = $item['fider_name'];
                $fiderConnection = $item['fider_name']; // fider code
                $prevTp = null;
            }

            // смена ТП/КТП
            // new EnergoObject()
            $this->dstFPdo
                ->insertInto('energoObject')
                ->values([
                    'id_res' => $prevRes,
                    'code' => $item['code_tp'] . '' . $item['tp_name'],
                    'name' => $item['tp_name'],
                    'type' => $item['tp_obj_type'],
                    'voltage' => '',
                    'activity' => true
                ])
                ->execute();

            // точка подключения к ТП
            // new EnergoConnection()
            $this->dstFPdo
                ->insertInto('energoConnection')
                ->values([
                    'id_energoObject' =>  $item['code_tp'] . '-' . $item['tp_name'],
                    'code' => $item['fider_name'],
                    'name' => $item['tp_name'],
                    'voltage' => '',
                    'status' => true
                ])
                ->execute();

            $tpConnection = $item['code_tp'] . '-' . $item['tp_name'];

            // линия от фидера к ТП
            // new EnergoLink()
            $this->dstFPdo
                ->insertInto('energoLink')
                ->values([
                    'id_srcConnection' => $fiderConnection,
                    'id_dstConnection' => $tpConnection,
                    'code' => $item['code_tp'] . '' . $item['tp_name'],
                    'name' => 'BЛ',
                    'activity' => true
                ])
                ->execute();
        }

    }
}