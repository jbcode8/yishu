<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 前端城市选择 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.11.20
// +----------------------------------------------------------------------

namespace Mall\Controller;

class CityController extends FpublicController {

    // 首页
    public function index() {

        $seo['title'] = '中国古玩城_城市';

        $this->assign('seo', $seo);
        $this->display();
    }

}