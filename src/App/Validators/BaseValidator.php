<?php

namespace App\Validators;



use Valitron\Validator;

class BaseValidator implements ValidatorInterface
{
    /** @var Validator */
    protected  $validator;

    protected $errors = [];

    protected $rules = [];

    public function __construct()
    {
        $this->validator = new Validator();
        $this->validator->mapFieldsRules($this->rules);
    }

    public function validate($data, $rules = []) {
        $this->errors = [];
        $v2 = $this->validator->withData($data);
        if (!empty($rules)) {
            $v2->mapFieldsRules($rules);
        }
        $isValid= $v2->validate();
        $this->errors = $v2->errors();
        return $isValid;
    }

    public function getErrors() {
        return $this->errors;
    }

}
