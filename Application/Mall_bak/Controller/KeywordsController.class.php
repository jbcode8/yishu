<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 后台 搜索 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.18
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class KeywordsController extends AdminController {

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');

        // 搜索条件初始化
        $condition = array();

        // 组装搜索条件
        if(!empty($kw)){

            // 关键字
            empty($kw) OR $condition['words'] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
        }

        $list = $this->lists('Keywords', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除之前的判断
    public function _before_delete(){

        // 判断支付方式是否存在
        isset($_GET['key_id']) AND $key_id = intval($_GET['key_id']);
        isset($key_id) OR $this->error('信息不存在或者已经被删除!');
    }

    // 状态(0：正常；1：推荐；2：锁定)
    public function editstatus(){

        // 获取信息ID
        isset($_GET['key_id']) AND $key_id = intval($_GET['key_id']);
        isset($key_id) OR $this->error('信息不存在或者已删除！');

        // 获取状态值
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) OR $this->error('参数有误！');

        $where['key_id'] = $key_id;
        $field['status'] = $status;

        if(D('Keywords')->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }
}