<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-3-18
 * Time: 下午12:28
 */

namespace Mall\Controller;
use Home\Controller\HomeController;

class TestController extends HomeController {

    public function index(){
        $this->display();
    }
} 