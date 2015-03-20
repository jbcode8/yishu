<?php
// +----------------------------------------------------------------------
// | 栏目列表
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Controller;


class CategoryController extends AdminController{

    /**
     *栏目分类列表
     */
    public function index(){
        $this->assign('tree',D('CategoryMall')->getTree());
        $this->display();
    }
}