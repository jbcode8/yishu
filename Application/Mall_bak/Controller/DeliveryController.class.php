<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 配送方式 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.12.25
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class DeliveryController extends AdminController {

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');

        // 搜索条件初始化
        $condition = array();

        // 组装搜索条件
        if(!empty($kw)){

            // 关键字
            empty($kw) OR $condition['delivery_name'] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
        }

        $list = $this->lists('Delivery', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除之前的判断
    public function _before_delete(){
        
        // 判断支付方式是否存在
        $delivery_id = I('get.delivery_id', '', 'intval');
        empty($delivery_id) AND $this->error('信息不存在或者已被删除!');
    }

    // 状态(0：锁定；1：待核；2：审核)
    public function editstatus(){

        // 获取信息ID
        isset($_GET['delivery_id']) AND $delivery_id = intval($_GET['delivery_id']);
        isset($delivery_id) OR $this->error('信息不存在或者已被删除！');

        // 获取状态值
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) OR $this->error('参数有误！');

        $where['delivery_id'] = $delivery_id;
        $field['status'] = $status;

        if(D('Delivery')->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }
}