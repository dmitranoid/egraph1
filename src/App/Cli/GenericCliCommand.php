<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 10.05.2018
 * Time: 11:54
 */

namespace App\Cli;


use App\Infrastructure\View\ViewInterface;
use Psr\Log\LoggerInterface;

class GenericCliCommand
{
    public $logger;
    public $view;

    public function __construct(ViewInterface $view, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->view = $view;
    }
}