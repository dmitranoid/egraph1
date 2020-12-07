<?php
declare(strict_types=1);


namespace App\DTO\View\Admin\Import;


class DwresDbDto
{
    public string $code;
    public string $dbName;
    public string $connectionString;
    public string $userName;
    public string $password;
    public string $status;

    public function __construct(string $code, string $dbName, string $connectionString, string $userName, string $password, string $status)
    {
        $this->code = $code;
        $this->dbName = $dbName;
        $this->connectionString = $connectionString;
        $this->userName = $userName;
        $this->password = $password;
        $this->status = $status;
    }

}