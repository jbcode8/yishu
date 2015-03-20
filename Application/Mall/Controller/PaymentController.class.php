<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 支付方式 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.25
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class PaymentController extends AdminController {

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');

        // 搜索条件初始化
        $condition = array();

        // 组装搜索条件
        if(!empty($kw)){

            // 关键字
            empty($kw) OR $condition['pay_name'] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
        }

        $list = $this->lists('Payment', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除之前的判断
    public function _before_delete(){
        
        // 判断支付方式是否存在
        $pay_id = I('get.pay_id', '', 'intval');
        empty($pay_id) AND $this->error('支付方式不存在或者已经被删除!');
    }

    // 状态(0：锁定；1：待核；2：审核)
    public function editstatus(){

        // 获取信息ID
        isset($_GET['pay_id']) AND $pay_id = intval($_GET['pay_id']);
        isset($pay_id) OR $this->error('信息不存在或者已删除！');

        // 获取状态值
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) OR $this->error('参数有误！');

        $where['pay_id'] = $pay_id;
        $field['status'] = $status;

        if(D('Payment')->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }
}