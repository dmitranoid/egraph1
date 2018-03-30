<?php 

namespace App\Dispatchers;

interface EventDispatcherInterface 
{
    public function dispatch(array $events);
}