<?php
declare(strict_types=1);


namespace App\Http\Controllers\Admin\Energonetwork\Import;


use App\Http\Controllers\Admin\AdminPanelGenericController;

final class DoImport extends AdminPanelGenericController
{
    public function action()
    {
        $data = $this->request->getParsedBody();
        // validate input data

    }
}