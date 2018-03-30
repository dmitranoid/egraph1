<?php

namespace App\Services;


use Domain\Exceptions,
    Domain\Exceptions\EntityNotFoundException;

class UserService
{
    private $repository;

    private $dispatcher;

    public function __construct($repository, $dispatcher) {
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
    }

    public function create($name) {
        try {
            $userEntity = $this->repository->findByName($name);
        } catch (EntityNotFoundException $e) {
            //ok
        }
        if ($userEntity) {
            throw new EntityAlreadyExistsException('User with given name already exists' , ['name'=>$name]);            
        }
        $userEntity->activate();
        // start transaction
        $this->repository->add($userEntity);
        // end transaction

        $this->dispatcher->dispatch($userEntity->releaseEvents());

        return true;
        
    }

    public function rename($id, $newName) {
        try {
            $userEntity = $this->repository->findById($id); 
        } catch (EntityNotFoundException $e) {
            throw $e;
        };
        $userEntity->rename($newName);

        $repository->update($userEntity);

        $this->dispatcher->dispatch($userEntity->releaseEvents());

        return true;
        
    }

    public function activate($id) {
        try {
            $userEntity = $this->repository->findById($id);
        } catch (EntityNotFoundException $e) {
            return false;
        }
        $userEntity->activate();

        $repository->update($userEntity);

        $this->dispatcher->dispatch($userEntity->releaseEvents());
    }
    
    public function deactivate($id) {
        try {
            $userEntity = $this->repository->findById($id); 
        } catch (EntityNotFoundException $e) {
            return false;
        }
        $userEntity->deactivate();

        $repository->update($userEntity);

        $this->dispatcher->dispatch($userEntity->releaseEvents());
    }
}
