<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-28
 * Time: 09:17
 */

namespace Alaric;

abstract class Factory implements FactoryInterface
{
    private static $loadedClass = [];

    /**
     * @param string $namespace
     * @param array $config
     * @return object
     * @throws \Exception
     */
    protected static function loadClass($namespace, $config){
        if(!is_array($config)){
            throw new \Exception("config must be array");
        }

        $adapter = $config["adapter"];
        if(!$adapter){
            throw new \Exception("you must provide 'adapter' option in factory config parameter.");
        }

        $className = $namespace . "\\" . $adapter;

        if (!isset(self::$loadedClass[$className])) {
            self::$loadedClass[$className] = new $className($config);
        }

        return self::$loadedClass[$className];
    }
}