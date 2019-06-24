<?php

namespace App\ServiceProviders;

use Psr\Container\ContainerInterface;

interface ServiceProviderInterface
{
    public static function register(ContainerInterface $container);
}
