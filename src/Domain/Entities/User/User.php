<?php

namespace Domain\Entities\User;

use Domain\Entities\EventTrait,
    Domain\Entities\User\Events\UserActivatedEvent,
    Domain\Entities\User\Events\UserDeactivatedEvent,
    Domain\Exceptions\DomainException;

class User 
{
    use EventTrait;

    const STATUS_ACTIVE = 'active';
    const STATUS_DISABLED = 'disabled';

    private $id;
    private $name;
    private $status;


    public function __construct($name) {
        $this->name = $name;
    }

    public function activate() {
        if(self::STATUS_ACTIVE == $this->status){
            throw new DomainException('User alredy active');
        }
        $this->status = self::STATUS_ACTIVE;
        $this->recordEvent(new UserActivatedEvent($this->id, new \DateTime()));
    }

    public function deactivate()  {
        if(self::STATUS_DISABLED == $this->status){
            throw new DomainException('User alredy disabled');
        }
        $this->status = self::STATUS_DISABLED; 
        $this->recordEvent(new UserDeactivatedEvent($this->id, new \DateTime()));        
    }

    public function changeName($name) {
        if(empty($name)) {
            throw new \InvalidArgumentException('username must be not empty');
        } 
        $this->name = $name;    
    }
}