<?php
declare(strict_types=1);


namespace App\Services\EnergoNetwork;


class Edge
{

    private $srcNodeCode;
    private $dstNodeCode;
    private $voltage;
    /**
     * @var string
     */
    private $direction;

    public function __construct($srcNodeCode, $dstNodeCode, $voltage, $direction = '')
    {
        $this->srcNodeCode = $srcNodeCode;
        $this->dstNodeCode = $dstNodeCode;
        $this->voltage = $voltage;
        $this->direction = $direction;
    }

    /**
     * @return mixed
     */
    public function getSrcNodeCode()
    {
        return $this->srcNodeCode;
    }

    /**
     * @return mixed
     */
    public function getDstNodeCode()
    {
        return $this->dstNodeCode;
    }

    /**
     * @return mixed
     */
    public function getVoltage()
    {
        return $this->voltage;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }
}