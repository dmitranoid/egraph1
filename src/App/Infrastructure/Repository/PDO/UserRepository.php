<?php

namespace App\Infrastructure\Repository\PDO;


use Domain\Repositories\UserRepositoryInterface,
    Domain\Entities\User\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected $fields = [
        'id',
        'name',
        'status',
    ];

    public function findById(int $id) {
        $sth = $this->db->prepare('select * from users where id=:id');
        $result = $sth->execute([':id'=>$id]);
        if (!$result || ($sth->rowCount() == 0)){
            return false;
        }
        return $this->hydrator->hydrate(User::class, $sth->fetchOne(PDO::FETCH_ASSOC)); 
    }


    public function findByName($username) {
        $sth = $this->db->prepare('select * from users where name=:name');
        $result = $sth->execute([':name' => $username]);
        if (!$result || ($sth->rowCount() == 0)){
            return false;
        }
        return $this->hydrator->hydrate(User::class, $sth->fetchOne(PDO::FETCH_ASSOC)); 
    }

    public function add(User $user) {
        $insertFields = array_diff_key($this->fields, ['id']);
        $data = $this->fieldsToParams($this->hydrator->extract($user, $insertFields));
        $sth = $this->db->prepare('insert into users(name, status) values(:name, :status)');
        $result = $sth->execute($data);
        if (!$result){
            $errorInfo = $sth->errorInfo();
            throw new \Exception($errorInfo[2]);
        }
        return true; 
    }

    public function update(User $user) {
        $data = $this->hydrator->extract($user, $this->fields);
        $sth = $this->db->prepare('update users set name=:name, status = :status where id=:id');
        $result = $sth->execute($data);
        if (!$result){
            throw new \Exception($sth->errorInfo()[2]);
        }
        return true; 
    }
    
    public function remove(User $user) {
        $data = $this->hydrator->extract($user, ['id']);
        $sth = $this->db->prepare('delete from users where id=:id');
        $result = $sth->execute($data);
        if (!$result){
            throw new \Exception($sth->errorInfo[2]);
        }
        return true; 
    }
}