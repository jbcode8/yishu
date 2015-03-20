<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 文章 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.25
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class AdminArticleController extends AdminController {

    public function _initialize(){
        parent::_initialize();
        $this->ModelName = 'Article';
    }

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');
        $startTime = I('start_time', '', 'trim');
        $endTime = I('end_time', '', 'trim');
        $type = I('stype', '', 'trim');

        // 搜索条件初始化
        $condition = array();

        // 组装搜索条件
        if(!empty($kw) || !empty($startTime) || !empty($endTime)){

            // 开始时间
            empty($startTime) OR $intStart = time2int($startTime, ' 00:00:00');
            $this->assign('start_time', $startTime);

            // 结束时间
            empty($endTime) OR $intEnd = time2int($endTime, ' 23:59:59');
            $this->assign('end_time', $endTime);

            // 关键字
            empty($type) AND $type = 'title';
            empty($kw) OR $condition[$type] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
            $this->assign('type', $type);

            // 时间同时具有
            if($startTime != '' && $endTime != ''){
                $condition['create_time'] = array(between, array($intStart, $intEnd));
            }else{
                // 开始时间和结束时间单个
                empty($startTime) OR $condition['create_time'] = array('gt', $intStart);
                empty($endTime) OR $condition['create_time'] = array('lt', $intEnd);
            }
        }

        $list = $this->lists($this->ModelName, $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    public function listorder(){
        parent::listorder($this->ModelName);
    }

    public function delete(){
        parent::delete($this->ModelName);
    }

    public function add(){
        parent::add($this->ModelName);
    }

    public function edit(){
        parent::edit($this->ModelName);
    }
    
}