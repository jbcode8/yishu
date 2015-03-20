<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 店铺 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.11.02
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class AdminStoreController extends AdminController {

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
            empty($type) AND $type = 'store_name';
            empty($kw) OR $condition[$type] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
            $this->assign('type', $type);

            // 时间同时具有
            if($startTime != '' && $endTime != ''){
                $condition['add_time'] = array(between, array($intStart, $intEnd));
            }else{
                // 开始时间和结束时间单个
                empty($startTime) OR $condition['create_time'] = array('gt', $intStart);
                empty($endTime) OR $condition['create_time'] = array('lt', $intEnd);
            }
        }

        $list = $this->lists('Store', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 显示详细信息
    public function detail(){

        // 判断是否传递ID
        isset($_GET['store_id']) AND $store_id = intval($_GET['store_id']);
        isset($store_id) OR $this->error('信息不存在或者已经被删除!');
        $where['store_id'] = $store_id;

        $this->info = D('Store')->where($where)->find();
        $this->display();
    }

    // 更改信息的状态[说明(1:正常; 2:锁定; 0:待审)]
    public function editstatus(){

        // 判断是否传递ID
        isset($_GET['store_id']) AND $store_id = intval($_GET['store_id']);
        isset($store_id) OR $this->error('信息不存在或者已经被删除!');
        $where['store_id'] = $store_id;

        // 判断是否传递状态
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) OR $this->error('参数错误!');

        // 修改状态
        $data['status'] = $status;

        // 修改审核时间
        $data['allow_time'] = time();

        // 更改状态和时间
        if(D('Store')->where($where)->save($data)){
            $this->success('状态修改成功!');
        }else{
            $this->error('状态修改失败!');
        }
    }

	// 更改信息的等级[说明(1:高级; 0:普通)]
    public function editgrade(){

        // 判断是否传递ID
        isset($_GET['store_id']) AND $store_id = intval($_GET['store_id']);
        isset($store_id) OR $this->error('信息不存在或者已经被删除!');
        $where['store_id'] = $store_id;

        // 判断是否传递等级
        isset($_GET['store_grade']) AND $store_grade = intval($_GET['store_grade']);
        isset($store_grade) OR $this->error('参数错误!');

        // 修改等级
        $data['store_grade'] = $store_grade;

        // 修改审核时间
        $data['allow_time'] = time();

        // 更改等级和时间
        if(D('Store')->where($where)->save($data)){
            $this->success('店铺等级修改成功!');
        }else{
            $this->error('店铺等级修改失败!');
        }
    }

}