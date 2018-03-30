<?php

namespace Tests\Validators\User;

use App\Validators\User\UserValidator;
use PHPUnit\Framework\TestCase;

class UserValidatorTest extends TestCase
{
    public function testUserValidator_Ok_Data() {

        $data = [
           'name'=>'Ivanov', 
        ];
        $userValidator = new UserValidator();

        $result = $userValidator->validate($data);

        $this->assertTrue($result);

    }

    public function testUserValidator_Wrong_Data() {
        //не сделан валидатор
/*
        $data = [
           'name'=>'', 
        ];
        $userValidator = new \App\Validators\User\UserValidator();
        $result = $userValidator->validate($data);

        $this->assertEquals(['name'=>''], $result);
*/
    }    
}
