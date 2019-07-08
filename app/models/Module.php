<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-08
 * Time: 21:36
 */

namespace Alaric\Models;

use Alaric\Models;

class Module extends Models
{
    public $id;
    public $site_id;
    public $type;
    public $status;
    public $name;
    public $is_sys;
    public $table_name;
    public $create_time;
}
