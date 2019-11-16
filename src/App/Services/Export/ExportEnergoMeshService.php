<?php
declare(strict_types=1);

namespace App\Services\Export;


use Envms\FluentPDO\Query;
use PDO;

final class ExportEnergoMeshService
{

    /**
     * @var Query
     */
    private $fpdo;

    public function __construct(PDO $pdo)
    {
        $this->fpdo = new Query($pdo);
    }


    public function exportCytoscapeJson(): string
    {
        $nodes = $this->fpdo
            ->from('energoConnection conn')
            ->join('energoObject obj on conn.id_energoObject = obj.code')
            ->select('conn.id conn_id, conn.name conn_name, conn.code conn_code, obj.name obj_name, obj.type')
            ->fetchAll();
        $edges = $this->fpdo
            ->from('energoLink')
            ->fetchAll();

        $cytoscapeData = [
        ];

        foreach ($nodes as $node) {
            $cytoscapeData[] = [
                'data' => ['id' => $node['conn_code']]
            ];
        }

        foreach ($edges as $edge) {
            $cytoscapeData[] = [
                'data' => [
                    'id' => $edge['code'],
                    'source' => $edge['id_srcConnection'],
                    'target' => $edge['id_dstConnection'],
                ]
            ];
        }

        return json_encode($cytoscapeData, JSON_PRETTY_PRINT);
    }

}