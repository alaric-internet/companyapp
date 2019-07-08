<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-02
 * Time: 21:42
 */

namespace Alaric\Models;

use Alaric\Models;

class Article extends Models
{
    public $id;
    public $site_id;
    public $user_id;
    public $status;
    public $author;
    public $source;
    public $views;
    public $title;
    public $seo_title;
    public $logo;
    public $slug;
    public $description;
    public $keywords;
    public $flag;
    public $create_time;
    public $publish_time;
    public $update_time;
    public $delete_time;
}