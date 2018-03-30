<?php

namespace Tests\Validators;

use PHPUnit\Framework\TestCase;

class ValidatorFactoryTest extends TestCase
{
    public function testValidatorsFactory() {
        $validatorFactory = new \App\Validators\ValidatorFactory();
        $userValidator = $validatorFactory->getValidator('User\UserValidator');
        $this->assertInstanceOf(\App\Validators\User\UserValidator::class, $userValidator);

    }
}
