<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 23.03.2018
 * Time: 16:57
 */

namespace App\Commands;


abstract class GenericCommand implements CommandInterface
{
    abstract public function execute();
}