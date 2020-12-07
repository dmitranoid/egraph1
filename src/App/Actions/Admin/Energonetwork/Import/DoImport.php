<?php
declare(strict_types=1);

namespace App\Actions\Admin\Energonetwork\Import;


use App\Actions\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class DoImport extends GenericImportAction
{
    protected function action(): Response
    {
        // validate data
        $formData = validate($this->request->getParsedBody());
        
        if (empty($srcFirebirdFile = $this->request->getQueryParams()['fdb_server_path'] ?? null)) {
            return $this->response->withRedirect($this->router->urlFor('adm.network.import.index'));
        }


    }

}