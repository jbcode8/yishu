<?php

// +----------------------------------------------------------------------
// | 会员中心 买家收藏 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class UcollectController extends MpublicController {

    public function _initialize(){

        parent::_initialize();

        $this->Model = D('Collect');
        $this->where = array('uid' => $_SESSION['user_auth']['uid']);
        $this->aryAct = array('delete', 'list');
        $this->indexId = 'collect_id';
    }

    // 其他操作
    public function init(){

        // 操作方式
        $act = isset($_GET['act']) ? trim($_GET['act']) : 'list';
        in_array($act, $this->aryAct) OR $this->error('不合法URL');

        // 操作方式
        $act == 'delete' AND $this->_delete($this->where);
        $act == 'list' AND $this->_list($this->where);
    }

    // 列表
    public function _list($where){

        // 状态
        if(isset($_GET['type'])){
            $where['collect_type'] = intval($_GET['type']);
            $this->collType = intval($_GET['type']);
        }

        // 查询[排除回复数据]
        $aryList = $this->Model->where($where)->select();

        empty($aryList) OR $this->aryList = $aryList;
        $this->display('list');
    }

    // 删除信息(ajax)[私有:不可直接地址访问]
    private function _delete($where){

        // 判断ID
        isset($_GET['id']) AND $id = intval($_GET['id']);
        if(empty($id)) die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');
        $where[$this->indexId] = $id;

        // 先读取信息是否存在，还可以预读是否存在回复信息
        $data = $this->Model->where($where)->find();
        if($data[$this->indexId] != $id) die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'信息不存在或者已被删除')) .')');

        $bool = $this->Model->where($where)->delete();
        if($bool){
            echo(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>'信息已被删除')) .')');
        }
    }
} 