<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 02.04.2018
 * Time: 11:03
 */

namespace App\Api;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ApiResourceGenericController implements ApiResourceControllerInterface
{
    /**
     * @var LoggerInterface
     */
    var $logger;

    /**
     * @var \PDO
     */
    var $db;

    /**
     * @var array список разрешенных для класса методов
     */
    protected $allowedActions = [
        'index',
        'show',
        'post',
    ];

    public function __construct(LoggerInterface $logger, \PDO $db)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args) {
        $method = strtolower($args['method'] ?? '');
        if(!in_array($method, $this->allowedActions)){
            $errorMessage = [
                'status' => 'error',
                'message' => sprintf('%s->%s not found', $args['class'], $method),
            ];
            return $response->withStatus(404)->withJson($errorMessage);
        }
        return (call_user_func([$this, $method], $request, $response, $args));
    }

    public function index(RequestInterface $request, ResponseInterface $response, array $args)
    {
        // TODO: Implement index() method.
    }

    public function show(RequestInterface $request, ResponseInterface $response, array $args)
    {
        // TODO: Implement get() method.
    }

    public function store(RequestInterface $request, ResponseInterface $response, array $args)
    {
        // TODO: Implement post() method.
    }

}