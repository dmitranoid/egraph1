<?php

namespace App\ServiceProviders;

use Psr\Container\ContainerInterface;
use App\Infrastructure\View\TwigView;

class TwigServiceProvider implements ServiceProviderInterface
{
    public static function register(ContainerInterface $container)
    {
        $config = $container->get('settings')['view'];
        $loader = new \Twig\Loader\FilesystemLoader($config['template_path']);
        $twig = new \Twig\Environment($loader, [
            'cache' => $config['twig']['cache'],
        ]);
        //$twig->addExtension(new Slim\Views\TwigExtension($app->getRouter(), ''));

        return new TwigView($twig);
    }
}
