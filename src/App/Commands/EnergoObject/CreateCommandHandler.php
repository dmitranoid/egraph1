<?php


namespace App\Commands\EnergoObject;

use App\Commands\GenericCommandHandler;
use \PDO;
use Psr\Log\LoggerInterface;

class CreateCommandHandler extends GenericCommandHandler
{
    private $db;
    private $logger;

    public function __construct(PDO $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function execute()
    {
        $this->logger->info('Create Energo Object command ');
    }
}