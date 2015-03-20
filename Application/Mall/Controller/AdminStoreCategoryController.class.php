<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 产品 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.11.02
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;


class AdminStoreCategoryController extends AdminController {

    public function _initialize(){

        parent::_initialize();
        $this->modelName = 'StoreCategory';
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
            empty($kw) OR $condition['cate_name'] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
        }

        $list = $this->lists($this->modelName, $condition, 'id DESC');
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除的操作
    public function delete(){
        parent::delete($this->modelName);
    }

    public function listorder(){
        parent::listorder($this->modelName);
    }

    /// 状态(0：锁定；1：待核；2：审核)
    public function status(){

        // 获取信息ID
        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) OR $this->error('信息不存在或者已删除！');
        $where['id'] = $id;

        // 获取状态值
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) OR $this->error('参数有误！');
        $field['status'] = $status;

        if(D($this->modelName)->where($where)->setField($field) !== false){
            D($this->modelName)->caches();
            $this->success('状态更新成功！');

        }else{
            $this->error('状态更新失败！');
        }
    }
} 