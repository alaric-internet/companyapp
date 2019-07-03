<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-28
 * Time: 09:11
 */

namespace Alaric\Db;

use Alaric\Factory as BaseFactory;

class Factory extends BaseFactory
{
    /**
     * @param array $config
     * @return object
     * @throws \Exception
     */
    public static function load($config)
    {
        return self::loadClass("Alaric\\Db", $config);
    }
}