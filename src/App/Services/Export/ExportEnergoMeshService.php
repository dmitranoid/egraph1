<?php
declare(strict_types=1);

namespace App\Services\Export;


use App\Exceptions\ApplicationException;
use App\Helpers\GeoDataHelper;
use App\Services\EnergoNetwork\Edge;
use App\Services\EnergoNetwork\EnergoNetworkBuilder;
use App\Services\EnergoNetwork\Node;
use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Query;
use PDO;
use Webmozart\Assert\Assert;

final class ExportEnergoMeshService
{
    const NETWORK_LEVEL_HIGH = 'h';
    const NETWORK_LEVEL_LOW = 'l';

    const HIGH_VOLTAGE = [35, 110, 220, 330, 750];

    /**
     * @var Query
     */
    private $fpdo;

    public function __construct(PDO $pdo)
    {
        $this->fpdo = new Query($pdo);
    }

    /**
     * @param string|array $region код(ы) РЭСа, обслуживающиго сети
     * @param string $level уровень сети (основная(high)/распределительная(low))
     * @return array
     */
    public function exportCytoscapeJson($region = null, $level = null): array
    {
        return $this->getMeshFromService($region, $level);
    }

    /**
     * возвращает массив узлов и связей для указанного РЭС
     *
     * @param null $region
     * @param null $level
     * @return array
     * @throws ApplicationException
     */
    public function getMeshFromService($region = null, $level = 'l'): array
    {
        Assert::notEmpty($region);
        Assert::true(in_array($level, [self::NETWORK_LEVEL_HIGH, self::NETWORK_LEVEL_LOW]));
        // TODO заменить на ReadModel
        try {
            $objects = $this->fpdo
                ->from('energoObject obj')
                ->leftJoin('geoCoords coord on coord.code_energoObject = obj.code')
                ->select('obj.*, coord.*', true)
                ->where('obj.code_region', $region)
                ->fetchAll();

            $connections = $this->fpdo
                ->from('energoConnection conn')
                ->innerJoin('energoObject obj on obj.code = conn.code_energoObject')
                ->select('conn.*', true)
                ->where('obj.code_region', $region)
                ->fetchAll();

            $links = $this->fpdo
                ->from('energoLink link')
                ->leftJoin('energoConnection src on src.code = link.code_srcConnection')
                ->leftJoin('energoConnection dst on dst.code = link.code_dstConnection')
                ->select('link.*, src.voltage src_voltage, dst.voltage dst_voltage', true)
                ->where('link.code_region', $region)
                ->fetchAll();
        } catch (\Envms\FluentPDO\Exception $e) {
            throw new ApplicationException('ошибка работы с БД', 0, $e);
        }

        $energoNetworkObject = EnergoNetworkBuilder::createFromDbData($objects, $connections, $links);

        $mesh = $energoNetworkObject->getMeshForRegion($region, self::NETWORK_LEVEL_HIGH);

/* Барановичи ЭС
        $geoHelper = new GeoDataHelper(
            [
                'lefttop' => ['latitude' => 53.350, 'longitude' => 24.650],
                'rightbottom' => ['latitude' => 52.300, 'longitude' => 27.000],
            ],
*/

/* Городской РЭС        53.183762, 25.848543    53.083555, 26.100882
        $geoHelper = new GeoDataHelper(
            [
                'lefttop' => ['latitude' => 53.183762, 'longitude' => 25.848543],
                'rightbottom' => ['latitude' => 53.083555, 'longitude' => 26.100882],
            ],
*/

            $geoHelper = new GeoDataHelper(
                [
                    'lefttop' => ['latitude' => 53.183762, 'longitude' => 25.848543],
                    'rightbottom' => ['latitude' => 53.083555, 'longitude' => 26.100882],
                ],
            [
                'width' => 1920,
                'height' => 1080
            ]);

        $cytoscapeData = [];
        foreach ($mesh['nodes'] as $node) {
            /** @var Node $node */
            $cytoscapeData[] =
                [
                    'data' => [
                        'id' => $node->getCode(),
                        'caption' => $node->getInfo()['name'],
                        'type' => $node->getInfo()['type'],
                        'weight' => is_numeric($node->getVoltage()) ? (float)$node->getVoltage() : 0,
                    ],
                    'position' => $geoHelper->coords2pixels(
                        $node->getInfo()['latitude'] ?? 0,
                        $node->getInfo()['longitude'] ?? 0
                    )
                ];
        }
        foreach ($mesh['edges'] as $edge) {
            /** @var Edge $edge */
            $cytoscapeData[] = [
                'data' => [
                    'id' => $edge->getCode(),
                    'source' => $edge->getSrcNodeCode(),
                    'target' => $edge->getDstNodeCode(),
                    'weight' => is_numeric($edge->getVoltage()) ? (float)$edge->getVoltage() : 0,
                ]
            ];
        }

        return $cytoscapeData;
    }


    public function getMeshFromDb($region = null, $level = null): array
    {
        // для распредсети все равно показываем и основную
        if (empty($level)) {
            $level = self::NETWORK_LEVEL_LOW;
        }
        Assert::true($level == self::NETWORK_LEVEL_LOW || $level == self::NETWORK_LEVEL_HIGH, 'неправильно задан уровень сети');

        $topLevelObjectTypes = ['ПС', 'РУ'];

        $substNodes = $this->fpdo
            ->from('energoObject obj')
            ->leftJoin('geoCoords geo on geo.code_energoObject = obj.code')
            ->select('obj.name obj_name, obj.type obj_type, obj.voltage obj_voltage, latitude, longitude')
            ->where('obj_type', $topLevelObjectTypes);
        if (!empty($region)) {
            $substNodes->where('obj.code_region', $region);
        }
        if ($level == self::NETWORK_LEVEL_HIGH) {
            $substNodes->where('obj.voltage', self::HIGH_VOLTAGE);
        }
        $substNodes = $substNodes
            ->fetchAll();

        //
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
                'src_voltage' => self::HIGH_VOLTAGE,
                'dst_voltage' => self::HIGH_VOLTAGE,
            ]);
        }
        $edges = $edges->fetchAll();

        $geoHelper = new GeoDataHelper(
            [
                'lefttop' => ['latitude' => 53.350, 'longitude' => 24.650],
                'rightbottom' => ['latitude' => 52.300, 'longitude' => 27.000],
            ],
            [
                'width' => 1920,
                'height' => 1080
            ]);
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
                ],
                'position' => $geoHelper->coords2pixels($node['latitude'], $node['longitude']),
            ];
        }
        // точки подключения как узлы
        foreach ($nodes as $node) {
            $cytoscapeData[] = ['data' => [
                'id' => $node['conn_code'],
                'caption' => $node['conn_name'],
                'type' => $node['obj_type'] == 'ПС' ? 'Ф' : $node['obj_type'],
                'weight' => in_array($node['obj_type'], ['ТП', 'РП']) ? 0.6 : 110,
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