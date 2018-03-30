<?php

namespace App\Validators;

interface ValidatorInterface
{
    function validate($data, $rules =[]);
    function getErrors();
}