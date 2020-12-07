<?php
declare(strict_types=1);


namespace App\Http\Controllers\Admin\Dashboard;


use App\Http\Controllers\Admin\AdminPanelGenericController;

class Index extends AdminPanelGenericController
{
    protected function action()
    {
        return $this->view->render($this->response, 'admin/dashboard/index.twig', []);
    }

}