<?php

namespace Domain\Entities\EnergoObject;

use PHPUnit\Framework\TestCase;

use Domain\Enums\Voltage,
    Domain\Enums\ActivityStatus,
    Domain\Enums\EnergoObjectType;

class EnergoObjectTest extends TestCase
{
    public function testInstance()
    {
        $voltage = new Voltage(Voltage::V04);
        $activityStatus = new ActivityStatus(ActivityStatus::ENABLED);
        $type = new EnergoObjectType(EnergoObjectType::TP);
        $obj = new EnergoObject('TestEnergoObject', $type, $voltage, $activityStatus);
        $this->assertInstanceOf(EnergoObject::class, $obj);
    }
}
