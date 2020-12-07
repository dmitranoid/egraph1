<?php
declare(strict_types=1);

namespace App\Actions\Admin\Energonetwork\Import;


use App\Actions\DomainRecordNotFoundException;
use App\DTO\View\Admin\Import\DwresDbDto;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class Index extends GenericImportAction
{
    protected function action(): Response
    {
        $dbList = array(
            new DwresDbDto('bar-sel', 'Барановичи село', "d:\\work\\egraph-test-data\\fdb\\Барановичский сельский РЭС.FDB", 'sysdba', 'masterkey', 'not defined'),
            new DwresDbDto('gan', 'Ганцевичи', "d:\\work\\egraph-test-data\\fdb\\gan.FDB", 'sysdba', 'masterkey', 'not defined'),
        );
        $data = compact('dbList');
        return $this->view->render($this->response, 'admin/network/import/index.twig', $data);
    }

}