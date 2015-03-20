<?php

namespace Message\Controller;
use Admin\Controller\AdminController;

class CategoryController extends AdminController
{

    /**
     * 获取留言板
     */
    public function index()
    {
        $list = D('Category')->getMessageCache();
        $this->assign('list',$list);
        $this->display();
    }


}