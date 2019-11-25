<?php

namespace Tests\App\Services\EnergoNetwork;

use App\Services\EnergoNetwork\Edge;
use App\Services\EnergoNetwork\Node;
use Envms\FluentPDO\Query;
use InvalidArgumentException;
use PDO;
use Tests\App\Includes;
use App\Services\EnergoNetwork\EnergoNetworkBuilder;
use PHPUnit\Framework\TestCase;

class EnergoNetworkBuilderTest extends TestCase
{
    /** @var PDO */
    private $pdo;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testCreateFromDbData()
    {
        $this->pdo = Includes\initSqliteDb();
        $fpdo = new Query($this->pdo);

        $testedRegion = '111113';

        // TODO заменить на ReadModel
        $objects = $fpdo
            ->from('energoObject obj')
            ->leftJoin('geoCoords coord on coord.code_energoObject = obj.code')
            ->select('obj.*, coord.*', true)
            ->where('obj.code_region', $testedRegion)
            ->fetchAll();

        $connections = $fpdo
            ->from('energoConnection conn')
            ->innerJoin('energoObject obj on obj.code = conn.code_energoObject')
            ->select('conn.*', true)
            ->where('obj.code_region', $testedRegion)
            ->fetchAll();

        $links = $fpdo
            ->from('energoLink link')
            ->leftJoin('energoConnection src on src.code = link.code_srcConnection')
            ->select('link.*, src.voltage', true)
            ->where('link.code_region', $testedRegion)
            ->fetchAll();

        $energoNetworkObject = EnergoNetworkBuilder::createFromDbData($objects, $connections, $links);
        $this->assertInstanceOf(EnergoNetworkBuilder::class, $energoNetworkObject);
    }

    /** @doesNotPerformAssertions */
    public function testCreateNetworkWithCustomValidData()
    {
        $energoNetworkObject = new EnergoNetworkBuilder();
        $this->initEnergoNetworkWithValidData($energoNetworkObject);
    }

    private function initEnergoNetworkWithValidData(EnergoNetworkBuilder $energoNetwork): EnergoNetworkBuilder
    {
        // n1
        $energoNetwork->addNode(new Node('0', 'n1', null, '330'));
        $energoNetwork->addNode(new Node('0', 'n1-10', 'n1', '220'));
        $energoNetwork->addNode(new Node('0', 'n1-20', 'n1', '220'));
        $energoNetwork->addNode(new Node('0', 'n1-30', 'n1', '110'));
        $energoNetwork->addEdge(new Edge('0', 'n1', 'n1-10', null, '220'));
        $energoNetwork->addEdge(new Edge('0', 'n1', 'n1-20', null, '220'));
        $energoNetwork->addEdge(new Edge('0', 'n1', 'n1-30', null, '110'));
        // n10 (220)
        $energoNetwork->addNode(new Node('0', 'n10', null, '220'));
        $energoNetwork->addNode(new Node('0', 'n10-1', 'n10', '220'));
        $energoNetwork->addNode(new Node('0', 'n10-2', 'n10', '220'));
        // connect n1 and n10
        $energoNetwork->addEdge(new Edge('0', 'n1-10', 'n10-1', '', '220'));
        // n20 (220)
        $energoNetwork->addNode(new Node('0', 'n20', null, '220'));
        $energoNetwork->addNode(new Node('0', 'n20-1', 'n20', '220'));
        $energoNetwork->addNode(new Node('0', 'n20-2', 'n20', '220'));
        // connect n1 and n20
        $energoNetwork->addEdge(new Edge('0', 'n1-20', 'n20-2', '', '220'));
        // n30 (110)
        $energoNetwork->addNode(new Node('0', 'n30', null, '110'));
        $energoNetwork->addNode(new Node('0', 'n30-1', 'n30', '110'));
        $energoNetwork->addEdge(new Edge('0', 'n30', 'n30-1', null, '110'));
//        $energoNetwork->addNode(new Node('0', 'n30-2', 'n30', '110'));
        // connect n1 and n30
        $energoNetwork->addEdge(new Edge('0', 'n1-30', 'n30-1', null, '110'));


        return $energoNetwork;
    }

    public function testCreateNetworkWithCustomInvalidData()
    {
        $energoNetworkObject = new EnergoNetworkBuilder();
        $this->expectException(InvalidArgumentException::class);
        $energoNetworkObject->addEdge(new Edge('0', 'not-existing-node', 'n1-1', null, '0'));
    }

    /** @doesNotPerformAssertions */
    public function testBuildTreeForNode()
    {
        $energoNetworkObject = new EnergoNetworkBuilder();
        $this->initEnergoNetworkWithValidData($energoNetworkObject);
        $tree = $energoNetworkObject->getTreeForNode('n1');
        //var_dump($tree);
    }

    /** @doesNotPerformAssertions */
    public function testBuildTreeForNodeDbData()
    {
        $this->pdo = Includes\initSqliteDb();
        $fpdo = new Query($this->pdo);

        $testedRegion = '111113';

        // TODO заменить на ReadModel
        $objects = $fpdo
            ->from('energoObject obj')
            ->leftJoin('geoCoords coord on coord.code_energoObject = obj.code')
            ->select('obj.*, coord.*', true)
            ->where('obj.code_region', $testedRegion)
            ->fetchAll();

        $connections = $fpdo
            ->from('energoConnection conn')
            ->innerJoin('energoObject obj on obj.code = conn.code_energoObject')
            ->select('conn.*', true)
            ->where('obj.code_region', $testedRegion)
            ->fetchAll();

        $links = $fpdo
            ->from('energoLink link')
            ->leftJoin('energoConnection src on src.code = link.code_srcConnection')
            ->select('link.*, src.voltage', true)
            ->where('link.code_region', $testedRegion)
            ->fetchAll();

        $energoNetworkObject = EnergoNetworkBuilder::createFromDbData($objects, $connections, $links);

        $tree = $energoNetworkObject->getTreeForNode('111115');
        var_dump($tree);
    }

    public function testGetMeshForRegion()
    {
        $this->pdo = Includes\initSqliteDb();
        $fpdo = new Query($this->pdo);

        $testedRegion = '111113';

        // TODO заменить на ReadModel
        $objects = $fpdo
            ->from('energoObject obj')
            ->leftJoin('geoCoords coord on coord.code_energoObject = obj.code')
            ->select('obj.*, coord.*', true)
            ->where('obj.code_region', $testedRegion)
            ->fetchAll();

        $connections = $fpdo
            ->from('energoConnection conn')
            ->innerJoin('energoObject obj on obj.code = conn.code_energoObject')
            ->select('conn.*', true)
            ->where('obj.code_region', $testedRegion)
            ->fetchAll();

        $links = $fpdo
            ->from('energoLink link')
            ->leftJoin('energoConnection src on src.code = link.code_srcConnection')
            ->select('link.*, src.voltage', true)
            ->where('link.code_region', $testedRegion)
            ->fetchAll();

        $energoNetworkObject = EnergoNetworkBuilder::createFromDbData($objects, $connections, $links);
        $result = $energoNetworkObject->getMeshForRegion('111113', 'l');
        $this->assertIsArray($result);

    }


}
