<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-08
 * Time: 21:27
 */

namespace Alaric\Models;

use Alaric\Models;

class Admin extends Models
{
    public $id;
    public $site_id;
    public $create_time;
    public $delete_time;
    public $user_name;
    public $password;
    public $status;
}