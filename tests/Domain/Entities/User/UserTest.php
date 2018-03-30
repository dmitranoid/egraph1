<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 16.03.2018
 * Time: 12:05
 */

namespace Tests\Domain\Entities\User;

use PHPUnit\Framework\TestCase;
use Domain\Entities\User\User;


class UserTest extends TestCase
{

    public function testInstance()
    {
        $user = new User('test user');
        $this->assertInstanceOf(User::class, $user);
    }
}
