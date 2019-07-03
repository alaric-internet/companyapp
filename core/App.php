<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:25
 */

namespace Alaric;


use Exception;

class App
{
    protected $di;

    /** @var Router */
    protected $router;

    private $loaders = [
        "cache",
        "database",
        "logger",
        "router"
    ];

    public function __construct() {
        $this->di = new Di();

        foreach ($this->loaders as $service) {
            $serviceName = ucfirst($service);
            $this->{'init' . $serviceName}();
        }

        $this->di->setDI($this->di);
    }

    public function run(){
        $controller = new Controller();

        $controller->handle();
    }

    /**
     * @throws Exception
     */
    protected function initCache(){
        $config = $this->loadConfig("cache");

        $this->di->set('cache', function () use ($config){
            return new Cache($config);
        });
    }

    /**
     * @param $di
     * @throws Exception
     */
    protected function initDatabase(){
        $config  = $this->loadConfig("database");

        $this->di->set('db', function () use ($config){
            return new Db($config);
        });
    }

    protected function initLogger(){
//        $logger = new Loader();
//        set_exception_handler([&$logger, 'handleException']);
//        set_error_handler([&$logger, 'handleError']);
//        register_shutdown_function([&$logger, 'handleShutdown']);
    }

    protected function initRouter(){

        /** @var Router $router */
        $router  = $this->loadConfig("routes");

        $router->setDefaultNamespace("Alaric\\Controllers\\Admin");

        if(isset($_GET['_uri'])) {
            $router->setUriSource($_GET['_uri']);
        }

        $router->removeExtraSlashes(true);

        $router->notFound(['controller' => 'error', 'action' => 'index']);

        $this->di->set('router', function () use($router){
            return $router;
        });
    }

    /**
     * @param string $name
     * @return array
     * @throws Exception
     */
    protected function loadConfig($name)
    {
        if(!$name){
            throw new Exception(
                '无法读取配置文件 ' . $name
            );
        }

        $path = config_path($name . ".php");
        if (!is_readable($path)) {
            throw new Exception(
                '无法读取配置文件 ' . $name
            );
        }

        $config = include $path;

        if(!is_array($config) && !is_object($config)){
            throw new Exception(
                '配置文件必须是数组或对象 ' . $name
            );
        }

        return $config;
    }
}