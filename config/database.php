<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:18
 */

return [
    'adapter'  => 'Mysql',
    'host'     => env('DB_HOST', '127.0.0.1'),
    'dbname'   => env('DB_DATABASE', 'companyapp'),
    'port'     => env('DB_PORT', 3306),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'prefix'  => env('DB_PREFIX', ''),
    'charset'  => env('DB_CHARSET', 'utf8mb4'),
    'options'  => [
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ]
];