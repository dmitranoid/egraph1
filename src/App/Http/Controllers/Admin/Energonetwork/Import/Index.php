<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Energonetwork\Import;


use App\DTO\View\Admin\Import\DwresDB;
use App\Http\Controllers\Admin\AdminPanelGenericController;

final class Index extends AdminPanelGenericController
{
    public function action()
    {
        $dbList = array(
            new DwresDB('bar-sel', 'Барановичи село', "d:\\work\\egraph-test-data\\fdb\\Барановичский сельский РЭС.FDB", 'sysdba', 'masterkey', 'not defined'),
            new DwresDB('gan', 'Ганцевичи', "d:\\work\\egraph-test-data\\fdb\\gan.FDB", 'sysdba', 'masterkey', 'not defined'),
        );
        $data = compact('dbList');
        return $this->view->render($this->response, 'admin/network/import/index.twig', $data);
    }
}