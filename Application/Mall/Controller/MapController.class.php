<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 城市选择 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class MapController extends FpublicController {

    public function _initialize(){

        parent::_initialize();
    }

    // 首页
    public function index() {

        $seo['title'] = '中国古玩城_列表';

        $this->assign('seo', $seo);
        $this->display();
    }

}