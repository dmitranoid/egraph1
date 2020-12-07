<?php
return [
    // View settings
    'view' => [
        'template_path' => ROOT_DIR . '/templates',
        'twig' => [
            'debug' => (getenv('ENV') === 'DEBUG'),
            'cache' => false, //(getenv('ENV') === 'DEBUG') ? false : ROOT_DIR . '/storage/cache/twig',
            'auto_reload' => true,
        ],
    ],

    // monolog settings
    'logger' => [
        'name' => 'app',
        'path' => ROOT_DIR . '/storage/logs/app.log',
    ],
    // db settings
    'database' => [
        'driver'=> getenv('DB_DRIVER'),
        'dbname'=> ROOT_DIR . getenv('DB_NAME'),
        'user'=> getenv('DB_USER'),
        'password'=> getenv('DB_PASSWORD'),
        'encoding'=> getenv('DB_ENCODING'),
    ],
];
