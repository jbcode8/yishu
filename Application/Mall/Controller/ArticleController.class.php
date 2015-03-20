<?php

// +----------------------------------------------------------------------
// | 前端 文章 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class ArticleController extends FpublicController {

    public function index() {

        // 获取类别
        isset($_GET['cate']) AND $cateId = intval($_GET['cate']);
        $where['cate_id'] = $cateId;

        $this->cateName = getArticleCateName($cateId);

        // 获取信息
        $aryList = D('Article')->where($where)->select();
        empty($aryList) OR $this->aryList = $aryList;

        $this->display();
    }

    public function show(){

        // 获取信息ID
        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) OR $this->error('信息不存在或者已经被删除！');
        $where['article_id'] = $id;

        $info = D('Article')->where($where)->find();
        empty($info) OR $this->info = $info;

        $this->display();
    }
}