<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 27.03.2018
 * Time: 11:53
 */

namespace App\Http\Controllers\Test;

use App\Infrastructure\View\ViewInterface;
use Domain\Entities\EnergoObject\EnergoObject;
use Domain\Enums\ActivityStatus;
use Domain\Enums\EnergoObjectType;
use Domain\Enums\Voltage;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class TestController
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ViewInterface
     */
    private $view;

    /**
     * TestController constructor.
     * @param ViewInterface $view
     * @param LoggerInterface $logger
     */
    public function __construct(ViewInterface $view, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->view = $view;
//        $this->logger->info(__CLASS__.':'.__METHOD__);
    }

    /*
        public function __construct(ContainerInterface $c)
        {
            $this->logger = $c->get('logger');
            $this->view = $c->get('view');
            $this->logger->info(__CLASS__.':'.__METHOD__);
        }
    */
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return ($this->view->render($response, 'front\test\index.twig', ['content'=>__CLASS__. ' - ' . __METHOD__]));
    }

    public function testAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return ($this->view->render($response, 'front\test\index.twig', ['content'=>__CLASS__. ' - ' . __METHOD__]));
    }

    public function testDomainAction(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        //$energoObjectRepository = new

        $energoObject = new EnergoObject(
            'Барановичи',
            new EnergoObjectType(EnergoObjectType::PS),
            new Voltage(Voltage::V330),
            new ActivityStatus(ActivityStatus::ENABLED)
        );

        $response->getBody()->write(var_export($energoObject));
        return $response;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return $this->view->render($response, '/admin/blank.twig', ['title' => 'title', 'content' => 'test']);
    }
}
