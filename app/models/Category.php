<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-08
 * Time: 21:28
 */

namespace Alaric\Models;

use Alaric\Models;

class Category extends Models
{
    public $id;
    public $site_id;
    public $slug;
    public $type;
    public $name;
    public $description;
    public $create_time;
    public $update_time;
    public $delete_time;
    public $status;
    public $keywords;
    public $content;
}