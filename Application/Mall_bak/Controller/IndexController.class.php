<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 前端控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.23
// +----------------------------------------------------------------------

namespace Mall\Controller;

class IndexController extends FpublicController {

    // 首页
    public function index() {

        $seo['title'] = '中国古玩城';
        $seo['keywords'] = '中国古玩城';
        $seo['desc'] = '中国古玩城';

        // 网站公告
        $this->aryNotice = D('Article')->field(array('article_id AS id','title'))->where(array('cate_id'=>12, 'store_id'=>0))->limit(6)->select();

        $this->assign('seo', $seo);
        $this->display();
    }

}