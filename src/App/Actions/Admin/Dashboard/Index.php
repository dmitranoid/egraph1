<?php
declare(strict_types=1);


namespace App\Actions\Admin\Dashboard;


use App\Actions\Admin\Energonetwork\Import\GenericImportAction;

class Index extends Action
{
    protected function action()
    {
        return $this->view->render($this->response, 'admin/dashboard/index.twig', []);
    }

}