<?php
declare(strict_types=1);


namespace App\Services\EnergoNetwork;


class Node
{

    private $code;
    private $voltage;
    private $region;
    private $parent;
    /**
     * @var array
     */
    private $info;

    /**
     * Node constructor.
     * @param string $region РЭС
     * @param string $code код
     * @param string $parent для подключений родитнльская ПС
     * @param string $voltage напряжение
     * @param array $info массив с дополнительной информацией
     */
    public function __construct($region, $code, $parent, $voltage, array $info = [])
    {
        $this->code = $code;
        $this->voltage = $voltage;
        $this->region = $region;
        $this->parent = $parent;
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
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

    /**
     * @return array
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

}