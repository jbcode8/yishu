<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 订单 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.11.02
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class OrderController extends AdminController {

    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');
        $startTime = I('start_time', '', 'trim');
        $endTime = I('end_time', '', 'trim');
        $type = I('stype', '', 'trim');

        // 组装搜索条件
        if(!empty($kw) || !empty($startTime) || !empty($endTime) || !empty($type)){

            // 开始时间
            empty($startTime) OR $intStart = time2int($startTime, ' 00:00:00');
            $this->assign('start_time', $startTime);

            // 结束时间
            empty($endTime) OR $intEnd = time2int($endTime, ' 23:59:59');
            $this->assign('end_time', $endTime);

            // 关键字
            empty($type) AND $type = 'order_sn';
            empty($kw) OR $condition[$type] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
            $this->assign('type', $type);

            // 时间[同时]具有
            if($startTime != '' && $endTime != ''){
                $condition['add_time'] = array(between, array($intStart, $intEnd));
            }else{
                // 开始时间和结束时间[单个]
                empty($startTime) OR $condition['create_time'] = array('gt', $intStart);
                empty($endTime) OR $condition['create_time'] = array('lt', $intEnd);
            }
        }

        $list = $this->lists('Order', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    public function detail(){

        $order_id = I('get.order_id','','intval');

        // 此处涉及产品信息，目前无产品，后续完善
        $this->vo = D('Order')->find($order_id);
        $this->display();
    }

} 