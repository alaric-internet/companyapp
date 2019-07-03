<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-02
 * Time: 19:24
 */

namespace Alaric;

use Exception;

class Di
{
    protected $_services;

    protected $_sharedInstances;

    protected static $_default;

    public function __construct() {
        if(!is_object(self::$_default)){
            self::$_default = $this;
        }

        return self::$_default;
    }

    public function set($name, $definition){
        $this->_services[$name] = $definition;
    }

    public function remove($name){
        unset($this->_services[$name], $this->_sharedInstances[$name]);
    }

    public function get($name, $parameters = null){
        $service = $this->_services[$name];

        if($service){
            if(is_array($parameters) && count($parameters)) {
                $instance = call_user_func_array($service, $parameters);
            }else{
                $instance = call_user_func($service);
            }
        }else{
            if(!class_exists($name)){
                throw new Exception("service not found");
            }

            if(is_array($parameters) && count($parameters)) {
                $instance = call_user_func_array($name, $parameters);
            }else{
                $instance = call_user_func($name);
            }
        }

        return $instance;
    }

    public function getShared($name, $parameters = null){
        $instance = $this->_sharedInstances[$name];
        if(!$instance){
            $instance = $this->get($name, $parameters);

            $this->_sharedInstances[$name] = $instance;
        }

        return $instance;
    }

    public function has($name){
        return isset($this->_services[$name]);
    }

    public static function setDefault($di)
	{
		self::$_default = $di;
	}

    public static function getDefault()
	{
        return self::$_default;
    }

    public function __call($method, $arguments = null)
    {
        if(strpos($method, 'get') === 0){
            $posibleService = lcfirst(substr($method, 3));
            if(isset($this->_services[$posibleService])){
                $instance = $this->get($posibleService, $arguments);
            }

            return $instance;
        }

        if(strpos($method, 'set') === 0){
            $this->set(lcfirst(substr($method, 3)), $arguments[0]);

            return null;
        }

        throw new Exception("Call to undefined method or service '" . $method . "'");
    }
}