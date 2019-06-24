<?php

namespace App\ServiceProviders;

use Monolog\Logger;
use Monolog;
use Psr\Container\ContainerInterface;

class MonologServiceProvider implements ServiceProviderInterface
{
    public static function register(ContainerInterface $container)
    {
        $settings = $container->get('settings');
        $logger = new Logger($settings['logger']['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
        return $logger;
    }
}
