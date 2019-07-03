<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-01
 * Time: 11:07
 */

namespace Alaric\Controllers\Admin;

use Alaric\Controllers\Base;
use Alaric\Models\Article;

class IndexController extends Base
{
    public function initialize(){
        echo "initialize";
    }

    public function indexAction(){

        echo "123123123";
    }

    public function checkAction($id = null, $name = null){

//        $article = new Article();
//        $article->title ="顺手插入内容";
//        $article->add_time =time();
//        $article->content ="这是一些内容这是一些内容这是一些内容这是一些内容这是一些内容";
//        $result = $article->saveAs();
//
//        $result = $article->deleteAs();
//
//        var_dump($result);

        //$cache = $this->di->getCache('');
    }
}