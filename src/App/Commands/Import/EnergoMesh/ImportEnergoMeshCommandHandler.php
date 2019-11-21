<?php


namespace App\Commands\Import\EnergoMesh;


use App\Commands\CommandHandlerInterface;
use App\Exceptions\ApplicationException;
use App\Services\Import\DwresImportService;
use Exception;
use Psr\Log\LoggerInterface;

class ImportEnergoMeshCommandHandler implements CommandHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ImportEnergoMeshCommand $command
     * @throws ApplicationException
     * @return void
     */
    public function handle(ImportEnergoMeshCommand $command):void
    {
        $this->logger->info('import started');
        try {
            $importService = new DwresImportService($command->srcPdo, $command->dstPdo, $this->logger);
            $importService->import();
        } catch (Exception $e) {
            $this->logger->error('import:' . $e->getMessage());
            throw new ApplicationException('import error', 0, $e);
        }
    }

}