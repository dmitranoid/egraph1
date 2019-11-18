<?php

namespace App\ServiceProviders;

use Psr\Container\ContainerInterface;
use App\Infrastructure\View\TwigView;
use Slim\Views\Twig;

class TwigServiceProvider implements ServiceProviderInterface
{
    public static function register(ContainerInterface $container)
    {
        $settings = $container->get('settings')['view'];
        $twigSettings = $settings['twig'];
        return new TwigView(new Twig($settings['template_path'], $twigSettings));
    }
}
