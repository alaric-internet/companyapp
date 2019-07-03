<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:18
 */

use Alaric\Router;

$router = new Router(false);

$router->add(
    '/',
    [
        'controller' => 'index',
        'action'     => 'index'
    ]
)->match(function(){
    echo "match";
});

$router->add(
    "/(check|other)/{id:[0-9a-zA-Z\\-]+}/{name:[0-9a-zA-Z\\-]+}",
    [
        'controller' => 'index',
        'action'     => 1,
    ]
);

return $router;