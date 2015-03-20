<?php

// +----------------------------------------------------------------------
// | 古玩城 会员中心 店铺订单 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen # 2014.01.13
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Mall\Controller\MpublicController;

class MorderController extends MpublicController {

    public function _initialize(){

        parent::_initialize();

        $this->Model = D('Order');
    }

    public function index(){

        //
        $where['seller_id'] = $_SESSION['user_auth']['uid'];

        //
        $list = $this->Model->where($where)->select();

        empty($list) OR $this->list = $list;
        $this->display();
    }
} 