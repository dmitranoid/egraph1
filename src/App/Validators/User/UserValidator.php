<?php

namespace App\Validators\User;

use App\Validators\BaseValidator;


class UserValidator extends BaseValidator
{
    protected $rules = [
        'name' => [
            'required',
            ['lengthBetween', 3, 50],
        ]
    ];

}
