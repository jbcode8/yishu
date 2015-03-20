<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 前端 文章 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.23
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Home\Controller\HomeController;

class FarticleController extends HomeController {

    public function _initialize(){

        parent::_initialize();
        $this->Model = D('Article');
    }

    // 首页
    public function index() {

        isset($_GET['cate']) AND $cate_id = intval($_GET['cate']);
        isset($cate_id) && $cate_id > 0 OR $this->error('信息不存在或者已被删除！');
        $where['cate_id'] = $cate_id;

        $this->list = $this->Model->where($where)->order('listorder DESC')->select();

        // 获取类别名
        $this->cate_id = $cate_id;

        $this->display();
    }

    // 详细页面
    public function show() {

        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) && $id > 0 OR $this->error('信息不存在或者已被删除！');
        $where['article_id'] = $id;

        $info = $this->Model->where($where)->find();
        empty($info) AND $this->error('信息不存在或者已被删除！');
        $this->info = $info;

        // 增加浏览数
        $this->Model->where($where)->setInc('hits');

        // SEO信息
        $arySeo['title'] = $info['title'];
        $this->seo = $arySeo;

        $this->display();
    }

}