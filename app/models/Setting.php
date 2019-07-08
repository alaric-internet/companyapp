<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-08
 * Time: 21:24
 */

namespace Alaric\Models;

use Alaric\Models;

class Setting extends Models
{
    public $id;
    public $site_id;
    public $name;
    public $value;
    public $tips;
}