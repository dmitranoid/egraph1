<?php 

namespace App\Dispatchers;


class LoggerEventDispatcher implements EventDispatcherInterface
{
    protected $logger;

    public function __construct($logger) {
        $this->logger = $logger;
    }

    public function dispatch(array $events) {
        foreach ($events as $event) {
            $this->logger->info(get_class($event));
        }
    }
}