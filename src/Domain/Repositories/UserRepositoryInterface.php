<?php

namespace Domain\Repositories;

use Domain\Entities\User\User;

interface UserRepositoryInterface
{
    public function findById(int $id);

    public function findByName($username);

    public function add(User $user);

    public function remove(User $user);

    public function update(User $user);
}
