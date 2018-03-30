<?php

namespace  Domain\Entities\EnergoObject;

use Domain\Entities\EventTrait,
    Domain\Entities\EnergoObject\EnergoObject,
    Domain\Enums\Voltage,
    Domain\Enums\TrasnmitionDirection,
    Domain\Enums\ActivityStatus,
    Domain\Exceptions\DomainException;
    
/**
 * Точка подключения к энергообъекту
 */
class EnergoConnection {
    use EventTrait;

    private $id;
    private $energoObject;
    private $name;
    private $nom;
    private $voltage;
    private $direction;
    private $activityStatus;

    public function __construct($id, EnergoObject $energoObject , string $name , string $nom, Voltage $voltage, TrasnmitionDirection $direction, ActivityStatus $activityStatus) {
        $this->id = $id;
        $this->energoObject = $energoObject;
        $this->name = $name;
        $this->nom = $nom; 
        $this->direction = $direction;
        $this->voltage = $voltage;
    }
}
