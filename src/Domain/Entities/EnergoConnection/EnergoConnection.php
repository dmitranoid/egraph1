<?php

namespace  Domain\Entities\EnergoObject;

use Domain\Entities\EventTrait,
    Domain\Enums\Voltage,
    Domain\Enums\TrasnmissionDirection,
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

    public function __construct($id, EnergoObject $energoObject , string $name , string $nom, Voltage $voltage, TrasnmissionDirection $direction, ActivityStatus $activityStatus) {
        $this->id = $id;
        $this->energoObject = $energoObject;
        $this->name = $name;
        $this->nom = $nom; 
        $this->direction = $direction;
        $this->voltage = $voltage;
    }
}
