<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:23
 */

namespace Alaric\Controllers;

use Alaric\Di;

class Base
{
    protected $di;

    final function __construct() {
        $this->di = Di::getDefault();

        if(method_exists($this, 'initialize')){
            $this->initialize();
        }

        echo "你好, index";
    }


}