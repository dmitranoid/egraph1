<?php
declare(strict_types=1);


namespace App\Services\EnergoNetwork;


use InvalidArgumentException;
use Webmozart\Assert\Assert;

class EnergoNetworkBuilder
{
    /**
     * @var Node[]|array
     */
    private $nodes;
    /**
     * @var Edge[]|array
     */
    private $edges;

    /**
     * EnergoNetworkBuilder constructor.
     * @param Node[] $nodes
     * @param Edge[] $edges
     */
    public function __construct(array $nodes = [], array $edges = [])
    {
        $this->nodes = $nodes;
        $this->edges = $edges;
    }

    /**
     * @param array $energoObjects
     * @param array $energoConnections
     * @param array $energoLinks
     * @return self
     */
    public static function createFromDbData(
        array $energoObjects,
        array $energoConnections,
        array $energoLinks
    )
    {
        $energoNetwork = new EnergoNetworkBuilder();

        foreach ($energoObjects as $object) {
            $energoNetwork->addNode(new Node($object['code_region'], $object['code'], null, $object['voltage']));
            $codeObject = $object['code'];
            // подключения для объекта
            $connectionsByObject = array_filter($energoConnections, function ($item) use ($codeObject) {
                return ($item['code_energoObject'] == $codeObject);
            });
            foreach ($connectionsByObject as $conn) {
                // точки подключения
                $energoNetwork->addNode(new Node($object['code_region'], $conn['code'], $object['code'], $conn['voltage']));
                // связи точки с объектом (не хранятся в БД как связи)
                $energoNetwork->addEdge(new Edge($object['code_region'], $conn['code_energoObject'], $conn['code'], $object['voltage']));
            }
        }
        // связи между энергообъектами (их точками подключения)
        foreach ($energoLinks as $link) {
            // TODO напряжение взято из подключений
            $energoNetwork->addEdge(new Edge($link['code_region'], $link['code_srcConnection'], $link['code_dstConnection'], 0));
        }
        return $energoNetwork;
    }

    /**
     * @return Node[]|array
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @return Edge[]|array
     */
    public function getEdges()
    {
        return $this->edges;
    }

    /**
     * Возвращает дерево из узлов, опускаясь по напряжению
     *
     * @param string $code код узла
     * @param int $voltage напряжение, ниже ниже которого должны быть узлы
     * @param array $visitedNodes уже посещенные зулы
     * @return array
     */
    private function treeNodeBuilder(string $code, $voltage = 750, $visitedNodes = []): array
    {
        Assert::true(is_numeric($voltage) && 1000 > ($voltage), 'неправильное значение напряжения: ' . $voltage);
        if (empty($node = $this->getNode($code))) {
            return [];
        };
        $retArray = ['node' => $node, 'childrens' => []];
        array_push($visitedNodes, $node->getCode());
        foreach ($this->getEdgesAttachedToNode($node->getCode()) as $edge) {
            // если напряжение выше, не идем по связи
            // если узел на второй стороне в списке посещенных, не идем по связи
            $isNodeVisited = array_search($this->getOppositeNodeCode($edge, $node->getCode()), $visitedNodes);
            if ($voltage < $edge->getVoltage() || (false !== $isNodeVisited)) {
                continue;
            }
            $childNode = $this->getNode($this->getOppositeNodeCode($edge, $node->getCode()));

            $retArray['childrens'][] = $this->treeNodeBuilder($childNode->getCode(), $childNode->getVoltage(), $visitedNodes);
        }
        return $retArray;
    }

    /**
     * Возвращает дерево из узлов, опускаясь по напряжению
     * @param string $code код узла
     * @param int $voltage напряжение, ниже ниже которого должны быть узлы
     * @return array
     */
    public function buildTreeForNode(string $code, $voltage = 750): array
    {
        return $this->treeNodeBuilder($code, $voltage);
    }

    public function addNode(Node $node)
    {
        $this->nodes[$node->getCode()] = $node;
    }

    public function addEdge(Edge $edge)
    {
        if (!$this->isNodeExists($edge->getSrcNodeCode())) {
            throw new InvalidArgumentException(sprintf('узел %s отсутстует при создании связи %s',
                $edge->getSrcNodeCode(),
                $edge->getSrcNodeCode() . '/' . $edge->getDstNodeCode()));
        }
        if (!$this->isNodeExists($edge->getDstNodeCode())) {
            throw new InvalidArgumentException(sprintf('узел %s отсутстует при создании связи %s',
                $edge->getDstNodeCode(),
                $edge->getSrcNodeCode() . '/' . $edge->getDstNodeCode()));
        }
        $this->edges[] = $edge;
    }

    private function isNodeExists($code): bool
    {
        foreach ($this->nodes as $node) {
            if ($code == $node->getCode()) {
                return true;
            }
        }
        return false;
    }

    private function isEdgeConnectingExists($srcCode, $dstCode)
    {
        foreach ($this->edges as $edge) {
            if (($srcCode == $edge->getSrcNodeCode() && $dstCode == $edge->getDstNodeCode()) ||
                ($dstCode == $edge->getSrcNodeCode() && $srcCode == $edge->getDstNodeCode())) {
                return true;
            }
        }
        return false;
    }

    private function getNode($code): ?Node
    {
        foreach ($this->nodes as $node) {
            if ($code == $node->getCode()) {
                return $node;
            }
        }
        return null;
    }

    /**
     * @param $code
     * @return Edge[]
     */
    private function getEdgesAttachedToNode($code): array
    {
        $edges = [];
        foreach ($this->edges as $edge) {
            if ($code == $edge->getSrcNodeCode() || $code == $edge->getDstNodeCode()) {
                $edges[] = $edge;
            }
        }
        return $edges;
    }

    private function getOppositeNodeCode(Edge $edge, $nodeCode): string
    {
        if ($nodeCode == $edge->getSrcNodeCode()) {
            return $edge->getDstNodeCode();
        }
        if ($nodeCode == $edge->getDstNodeCode()) {
            return $edge->getSrcNodeCode();
        }
        throw new InvalidArgumentException(sprintf('связь не подключена к узлу %s', $nodeCode));
    }
}
