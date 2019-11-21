<?php
declare(strict_types=1);


namespace App\Services\EnergoNetwork;


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
    private function __construct(array $nodes, array $edges)
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
        $nodes = [];
        foreach ($energoConnections as $connection) {
            $nodes[Node($connection['code_energoObject']] = new Node($connection['code_energoObject'], $connection['voltage']);
        }
        foreach ($energoLinks as $link) {
            // TODO напряжение взять из подключений
            $edges[] = new Edge($link['code_srcConnection'], $link['code_dstConnection'], 0);
        }
        return new EnergoNetworkBuilder($nodes, $edges);
    }

    public function buildTreeForNode(string $energoObjectCode): array
    {

    }

}