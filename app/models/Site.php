<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-08
 * Time: 21:37
 */

namespace Alaric\Models;

use Alaric\Models;

class Site extends Models
{
    public $id;
    public $create_time;
    public $user_id;
    public $status;
    public $title;
    public $domain;
    public $seo_title;
    public $keywords;
    public $description;
}
