<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-29
 * Time: 17:22
 */

namespace Alaric\Cache;

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
        return self::loadClass("Alaric\\Cache", $config);
    }
}