<?php

// +----------------------------------------------------------------------
// | 后台 保障信息列表 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class AdminEnsureController extends AdminController {

    public function _initialize(){
        parent::_initialize();
        $this->ModelName = 'Ensure';
    }

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');

        // 搜索条件初始化
        $condition = array();

        // 组装搜索条件
        if(!empty($kw)){

            // 关键字
            empty($kw) OR $condition['name'] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
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

    // 状态(0：锁定；1：正常)
    public function status(){

        // 获取信息ID
        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) OR $this->error('信息不存在或者已删除！');
        $where['id'] = $id;

        // 获取状态值
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) OR $this->error('参数有误！');
        $field['status'] = $status;

        if(D($this->ModelName)->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
            D($this->ModelName)->caches();
        }else{
            $this->error('状态更新失败！');
        }
    }
}