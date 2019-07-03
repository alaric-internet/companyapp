<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-28
 * Time: 09:15
 */

namespace Alaric;

interface FactoryInterface
{
    /**
     * @param array $config
     * @return object
     */
    public static function load($config);
}