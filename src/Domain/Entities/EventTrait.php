<?php

namespace Domain\Entities;


trait EventTrait
{
    private $events = [];
    protected function recordEvent($event)
    {
        $this->events[] = $event;
    }
    public function releaseEvents()
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}