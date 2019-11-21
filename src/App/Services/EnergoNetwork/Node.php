<?php
declare(strict_types=1);


namespace App\Services\EnergoNetwork;


class Node
{
    private $code;
    private $voltage;

    public function __construct($code, $voltage)
    {
        $this->code = $code;
        $this->voltage = $voltage;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getVoltage()
    {
        return $this->voltage;
    }

}