<?php

namespace App\Validators;

class ValidatorFactory {

    public function getValidator($validatorName):ValidatorInterface {
        $classname = 'App\Validators\\'. $validatorName;
        return new $classname;
    }
}