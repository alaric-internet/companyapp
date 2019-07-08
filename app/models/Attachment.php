<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-08
 * Time: 21:25
 */

namespace Alaric\Models;

use Alaric\Models;

class Attachment extends Models
{
    public $id;
    public $site_id;
    public $user_id;
    public $status;
    public $create_time;
    public $delete_time;
    public $file_type;
    public $file_size;
    public $width;
    public $height;
    public $md5;
    public $file_location;
    public $title;
}