<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-30
 * Time: 16:54
 */

use Alaric\Loader;

define('APP_START_TIME', microtime(true));

define('APP_START_MEMORY', memory_get_usage());

define("BASE_PATH", realpath(dirname(__FILE__)) . "/");

if (is_file(BASE_PATH . '.env')) {
    $env = parse_ini_file(BASE_PATH . '.env', true);

    foreach ($env as $key => $val) {
        $name = strtoupper($key);

        if (is_array($val)) {
            foreach ($val as $k => $v) {
                $item = $name . '_' . strtoupper($k);
                putenv("$item=$v");
            }
        } else {
            putenv("$name=$val");
            $_ENV[$name]=$val;
        }
    }
}

error_reporting(E_ALL & ~E_NOTICE);

require BASE_PATH . "core/Helper.php";
require BASE_PATH . "core/Loader.php";

$loader = new Loader;
$loader->registerNamespaces(
    [
        'Alaric'                   => BASE_PATH . "core/",
        'Alaric\Models'            => app_path("models"),
        'Alaric\Controllers'       => app_path("controllers"),
        'Alaric\Providers'         => app_path("providers"),
        'Alaric\Libraries'         => app_path("libraries"),
    ]
);

$loader->register();
