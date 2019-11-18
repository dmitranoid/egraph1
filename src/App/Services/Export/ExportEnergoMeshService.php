<?php
declare(strict_types=1);

namespace App\Services\Export;


use Envms\FluentPDO\Query;
use PDO;
use Webmozart\Assert\Assert;

final class ExportEnergoMeshService
{
    const NETWORK_LEVEL_HIGH = 'h';
    const NETWORK_LEVEL_LOW = 'l';

    const HIGH_VOLTAGE = [110, 220, 330, 750];

    /**
     * @var Query
     */
    private $fpdo;

    public function __construct(PDO $pdo)
    {
        $this->fpdo = new Query($pdo);
    }

    /**
     * @param null $region код региона обслуживающиго сети
     * @param null $level уровень сети (основная(high)/распределительная(low))
     * @return array
     */
    public function exportCytoscapeJson($region = null, $level = null): array
    {
        return $this->getMesh($region, $level);
    }

    public function getMesh($region = null, $level = null): array
    {
        // для распредсети все равно показываем и основную
        if (empty($level)) {
            $level = self::NETWORK_LEVEL_LOW;
        }
        Assert::true($level == self::NETWORK_LEVEL_LOW || $level == self::NETWORK_LEVEL_HIGH, 'неправильно задан уровень сети');

        $substNodes = $this->fpdo
            ->from('energoObject obj')
            ->select('obj.name obj_name, obj.type obj_type, obj.voltage obj_voltage')
            ->where('obj_type', 'ПС');
        if (!empty($region)) {
            $substNodes->where('obj.code_region', $region);
        }
        if ($level == self::NETWORK_LEVEL_HIGH) {
            $substNodes->where('obj.voltage', self::HIGH_VOLTAGE);
        }
        $substNodes = $substNodes
            ->fetchAll();

        $nodes = $this->fpdo
            ->from('energoConnection conn')
            ->join('energoObject obj on conn.code_energoObject = obj.code')
            ->select('conn.id conn_id, conn.name conn_name, conn.code conn_code, obj.name obj_name, obj.type obj_type')
            ->order('conn.voltage desc');
        if (!empty($region)) {
            $nodes->where('obj.code_region', $region);
        };
        // TODO неясно, нужно ли скрывать отходящие фидеры 10КВ
        if ($level == self::NETWORK_LEVEL_HIGH) {
            $nodes->where('obj.voltage', self::HIGH_VOLTAGE);
            //$nodes->where('conn.voltage', self::HIGH_VOLTAGE);
        }
        $nodes = $nodes->fetchAll();

        $edges = $this->fpdo
            ->from('energoLink')
            ->innerJoin('energoConnection srcConn on srcConn.code = code_srcConnection ')
            ->innerJoin('energoConnection dstConn on dstConn.code = code_dstConnection ')
            ->select('energoLink.*, srcConn.voltage src_voltage, dstConn.voltage dst_voltage');
        if (!empty($region)) {
            $edges->where('code_region', $region);
        };
        if ($level == self::NETWORK_LEVEL_HIGH) {
            // TODO возможно здесь тоже надо дойти до обьекта по напряжению
            $edges->where([
                'src_voltage'=> self::HIGH_VOLTAGE,
                'dst_voltage'=> self::HIGH_VOLTAGE,
            ]);
        }
        $edges = $edges->fetchAll();

        $cytoscapeData = [];
        // отображаем высокие подстанции как пс + фидеры (точки подключения)
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