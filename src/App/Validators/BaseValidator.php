<?php

namespace App\Validators;


class BaseValidator implements ValidatorInterface
{
    protected $errors = [];

    protected $rules = [];

    public function validate($data, $rules = []) {
        if (!empty($rules)) {
            $this->rules = $rules;
        }
        foreach ($this->rules as $field => $rule) {
            /*
            if (false === ($result = $rule->validate($data[$field]))) {
                $this->errors[$field] = $result;
            }
            */
        }
        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

}
