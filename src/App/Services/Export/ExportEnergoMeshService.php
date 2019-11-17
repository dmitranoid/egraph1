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


    public function exportCytoscapeJson(): array
    {
        $substNodes = $this->fpdo
            ->from('energoObject obj')
            ->select('obj.name obj_name, obj.type obj_type')
            ->where('obj_type', 'ПС')
            ->fetchAll();

        $nodes = $this->fpdo
            ->from('energoConnection conn')
            ->join('energoObject obj on conn.code_energoObject = obj.code')
            ->select('conn.id conn_id, conn.name conn_name, conn.code conn_code, obj.name obj_name, obj.type obj_type')
            ->order('conn.voltage desc')
            ->fetchAll();
        $edges = $this->fpdo
            ->from('energoLink')
            ->fetchAll();

        $cytoscapeData = [];
        // отображаем высокие подстанции как узлы
        // ТП/РП отображаем только как точки подключения для экономии места на схеме
        foreach ($substNodes as $node) {
            $cytoscapeData[] = [
                'data' => [
                    'id' => $node['obj_name'],
                    'caption' => $node['obj_name'],
                    'type' => 'ПС',
                    'weight' => 110,
                ]
            ];
        }
        // точки подключения как узлы
        foreach ($nodes as $node) {
            $cytoscapeData[] = ['data' => [
                'id' => $node['conn_code'],
                'caption' => $node['conn_name'],
                'type' => $node['obj_type'] == 'ПС' ? 'Ф' : $node['obj_type'],
                'weight' => in_array($node['obj_type'], ['ТП', 'РП']) ? 0.6 : 110,
                //'parent' => $node['id_energoObject'],
            ]];
            // если это фидер, то строим связь с ПС
            // у точки подключения к ТП имя точки подключения = имя родительского обьекта
            if ($node['obj_name'] != $node['conn_code']) {
                $cytoscapeData[] = ['data' => [
                    'id' => $node['obj_name'] . '/' . $node['code'],
                    'source' => $node['obj_name'],
                    'target' => $node['conn_code'],
                    'weight' => 110,
                ]];
            }
        }
        // связи между точками подключения
        foreach ($edges as $edge) {
            $cytoscapeData[] = [
                'data' => [
                    'id' => $edge['code'],
                    'source' => $edge['code_srcConnection'],
                    'target' => $edge['code_dstConnection'],
                    'weight' => 10,
                ]
            ];
        }
        return $cytoscapeData;
    }

}