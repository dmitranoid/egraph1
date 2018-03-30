<?php

namespace App\Validators\User;

use App\Validators\BaseValidator;


class UserValidator extends BaseValidator
{
    protected $rules = [
        'name' => [
            'not empty', 
            ['min_length'=> 3],
            ['max_length'=> 50],
        ]
    ];

}
