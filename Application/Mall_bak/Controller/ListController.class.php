<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 前端列表控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.23
// +----------------------------------------------------------------------

namespace Mall\Controller;

class ListController extends FpublicController {

    // 首页
    public function index() {

        $seo['title'] = '中国古玩城_列表';

        $this->assign('seo', $seo);
        $this->display();
    }

}