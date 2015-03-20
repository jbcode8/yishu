<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 品牌筛选 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class BrandListController extends FpublicController {

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