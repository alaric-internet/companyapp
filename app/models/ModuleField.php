<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-08
 * Time: 21:37
 */

namespace Alaric\Models;

use Alaric\Models;

class ModuleField extends Models
{
    public $id;
    public $site_id;
    public $status;
    public $module_id;
    public $field;
    public $field_name;
    public $field_type;
    public $field_tips;
    public $default_value;
    public $max_length;
    public $is_required;
    public $order_by;
    public $is_sys;
    public $create_time;
}