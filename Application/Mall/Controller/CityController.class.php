<?php

// +----------------------------------------------------------------------
// | 前端 城市选择 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// | modify: 663642331@qq.com (增加14行) Kaiwei Sun 2014/07/15
// +----------------------------------------------------------------------

namespace Mall\Controller;
class CityController extends FpublicController {

    public function index() {
        session('city_name', I('get.city'));
    }
}