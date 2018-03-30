<?php

// debug
if(!function_exists('dd')) {
    function dd(...$args) {
        foreach ($args as $key=>$arg) {
            var_dump($arg);
        }
    }
}

if(!function_exists('ddie')) {
    function ddie(...$args) {
        dd($args);
        die;
    }
}

// app helpers

if(!function_exists('container')) {
    function container() {
        return $app;
    }
}

if(!function_exists('container')) {
    function container() {
        return $app->getContainer();
    }
}


if(!function_exists('info')) {
    function info($message) {
        app()->getContainer()->logger->info($message);
    }
}




