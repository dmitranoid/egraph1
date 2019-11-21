<?php
declare(strict_types=1);


namespace App\Services\Export;


use Envms\FluentPDO\Query;
use PDO;

class ExportEnergoTree
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->fpdo = new Query($pdo);
    }

    public function export($region = null, $level = null): array
    {
        $objectsList = $this->fpdo
            ->from('energoObject')
            ->select('*')
            ->where('code_region', $region)
            ->fetchAll();

        $linksList = $this->fpdo
            ->from('energoLink link')
            ->innerJoin('energoConnection src on src.code = link.code_srcConnection')
            ->innerJoin('energoConnection dst on dst.code = link.code_dstConnection')
            ->select('link.*, src.code_energoObject src_energoObject, dst.code_energoObject dst_energoObject')
            ->where('code_region', $region)
            ->fetchAll();

        $connectionsList = $this->fpdo
            ->from('energoConnection conn')
            ->innerJoin('energoObject obj on obj.code = conn.code_energoObject')
            ->select('conn.*')
            ->where('obj.code_region', $region)
            ->fetchAll();

        $voltageLevels = ['330', '220', '110', '35', '10', '4'];

        $data = $objectsList;
        return $data;
    }

}