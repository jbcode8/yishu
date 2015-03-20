<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 发货地址 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.31
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class ShoppingAddressController extends AdminController {

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');
        $type = I('stype', '', 'trim');

        // 搜索条件初始化
        $condition = array();

        // 组装搜索条件
        if(!empty($kw)){

            // 默认搜索字段
            empty($type) AND $type = 'address';

            // 关键字
            empty($kw) OR $condition[$type] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
            $this->assign('type', $type);
        }

        $list = $this->lists('ShoppingAddress', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除之前的判断
    public function _before_delete(){
        
        // 判断品牌ID是否存在
        $address_id = I('get.address_id', '', 'intval');
        empty($address_id) AND $this->error('品牌不存在!');
        
        // 检测此地址下是否关联购物车
    }
}