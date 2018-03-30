<?php

namespace Domain\Entities\User\Events;

class UserDeactivatedEvent 
{
    public $id;
    public $date;

    public function __construct($id, $date) {
        $this->id = $id;
        $this->date = $date;
    }
}