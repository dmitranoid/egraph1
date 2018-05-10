<?php

namespace Domain\Repositories;


use Domain\Entities\User\User;

interface BaseRepositoryCRUDInterface
{
    function getById(int $id);
    function add($entity);
    function remove($entity);
    function update($entity);
}