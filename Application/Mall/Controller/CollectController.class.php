<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 收藏 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.30
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class CollectController extends AdminController {

    public function index(){

        $collect_type = I('type','','intval');
        $condition = array();
        ($collect_type === 0 || $collect_type === 1) AND $condition['collect_type'] = $collect_type;

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');
        $startTime = I('start_time', '', 'trim');
        $endTime = I('end_time', '', 'trim');

        // 组装搜索条件
        if(!empty($kw) || !empty($startTime) || !empty($endTime)){

            // 开始时间
            empty($startTime) OR $intStart = time2int($startTime, ' 00:00:00');
            $this->assign('start_time', $startTime);

            // 结束时间
            empty($endTime) OR $intEnd = time2int($endTime, ' 23:59:59');
            $this->assign('end_time', $endTime);

            // 关键字
            empty($kw) OR $condition['content'] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);

            // 时间同时具有
            if($startTime != '' && $endTime != ''){
                $condition['create_time'] = array(between, array($intStart, $intEnd));
            }else{
                // 开始时间和结束时间单个
                empty($startTime) OR $condition['create_time'] = array('gt', $intStart);
                empty($endTime) OR $condition['create_time'] = array('lt', $intEnd);
            }
        }

        $list = $this->lists('Collect', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除之前的判断
    public function _before_delete(){

        // 判断信息是否存在
        $collect_id = I('get.collect_id', '', 'intval');
        empty($collect_id) AND $this->error('信息不存在或者已经被删除!');
    }
}