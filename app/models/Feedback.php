<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-08
 * Time: 21:33
 */

namespace Alaric\Models;

use Alaric\Models;

class Feedback extends Models
{
    public $id;
    public $site_id;
    public $username;
    public $contact;
    public $title;
    public $content;
    public $status;
    public $ip;
    public $type;
    public $create_time;
    public $update_time;
}