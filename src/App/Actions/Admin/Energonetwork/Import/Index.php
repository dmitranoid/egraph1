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
        // TODO забирать из конфига
        $dbList = array(
            new DwresDbDto('barg', 'Барановичи город', "d:\\work\\egraph-test-data\\fdb\\bars.FDB", 'sysdba', 'masterkey', 'not defined', null),
            new DwresDbDto('bars', 'Барановичи село', "d:\\work\\egraph-test-data\\fdb\\bars.FDB", 'sysdba', 'masterkey', 'not defined', null),
            new DwresDbDto('iva', 'Ивацевичи', "d:\\work\\egraph-test-data\\fdb\\iva.FDB", 'sysdba', 'masterkey', 'not defined', null),
            new DwresDbDto('lah', 'Ляховичи', "d:\\work\\egraph-test-data\\fdb\\lah.FDB", 'sysdba', 'masterkey', 'not defined', null),
            new DwresDbDto('gan', 'Ганцевичи', "d:\\work\\egraph-test-data\\fdb\\gan.FDB", 'sysdba', 'masterkey', 'not defined', null),
            new DwresDbDto('ber', 'Береза', "d:\\work\\egraph-test-data\\fdb\\ber.FDB", 'sysdba', 'masterkey', 'not defined', null),
        );
        return $this->view->render($this->response, 'admin/network/import/index.twig', compact('dbList'));
    }

}