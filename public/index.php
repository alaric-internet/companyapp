<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:09
 */

use Alaric\App;

require dirname(__DIR__) . "/autoloader.php";

$bootstrap = new App();
$bootstrap->run();