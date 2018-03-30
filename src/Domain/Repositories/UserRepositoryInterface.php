<?php

namespace Domain\Repositories;


use Domain\Entities\User\User;

interface UserRepositoryInterface
{
    function findById(int $id);
    function findByName($username);    
    function add(User $user);
    function remove(User $user);
    function update(User $user);    
}