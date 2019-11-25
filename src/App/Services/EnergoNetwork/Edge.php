<?php
declare(strict_types=1);


namespace App\Services\EnergoNetwork;


class Edge
{
    private $region;
    private $srcNodeCode;
    private $dstNodeCode;
    private $voltage;
    private $direction;
    /**
     * @var array
     */
    private $info;
    private $code;

    /**
     * Edge constructor.
     * @param string $region
     * @param string $srcNodeCode
     * @param string $dstNodeCode
     * @param string|null $code если null, генерируется автоматически
     * @param string $voltage
     * @param string $direction
     * @param array $info
     */
    public function __construct($region, $srcNodeCode, $dstNodeCode, $code, $voltage, $direction = '', array $info = [])
    {
        $this->srcNodeCode = $srcNodeCode;
        $this->dstNodeCode = $dstNodeCode;
        $this->voltage = $voltage;
        $this->direction = $direction;
        $this->region = $region;
        $this->info = $info;
        $this->code = $code;
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
    public function getCode()
    {
        // TODO
        if ($this->code === null) {
            return $this->srcNodeCode . '/' . $this->dstNodeCode;
        } else {
            return $this->code;
        }
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
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