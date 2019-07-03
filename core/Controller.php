<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:25
 */

namespace Alaric;

class Controller
{
    /** @var Di */
    protected $di;

    final function __construct() {
        //注入di
        $this->getDI();
        /** @var Router $router */
        $router = $this->di->get('router');

        $router->handle();

        $matchedRoute = $router->getMatchedRoute();
        $match = $matchedRoute->getMatch();

        if($match !== null) {
            call_user_func_array($match, $router->getParams());
        }

        $matchRoute = [
            'module'     => $router->getModuleName(),
            'namespace'  => $router->getNamespaceName(),
            'controller' => $router->getControllerName(),
            'action'     => $router->getActionName(),
            'params'     => $router->getParams(),
        ];

        $controllerName = $matchRoute['namespace'] . "\\" . ucfirst($matchRoute['controller']) . "Controller";
        $controller = new $controllerName();

        $actionName = $matchRoute['action'] . "Action";
        //注入get
        if(is_array($matchRoute['params'])){
            $_GET = array_merge($_GET, $matchRoute['params']);
        }

        if(method_exists($controller, $actionName)){
            call_user_func_array([$controller, $actionName], $matchRoute['params']);
        }
    }

    public function handle(){

    }

    protected function getDI(){
        if(!$this->di){
            $this->di = DI::getDefault();
        }

        return $this->di;
    }

    protected function setDI($di){
        $this->di = $di;
    }
}