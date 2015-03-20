<?php

// +----------------------------------------------------------------------
// | 古玩城 会员中心 店铺咨询 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen # 2014.01.09
// +----------------------------------------------------------------------

namespace Mall\Controller;

class MquestionController extends MpublicController {

    public function _initialize(){

        parent::_initialize();

        $this->Model = D('Question');
        $this->aryAct = array('reply', 'show', 'delete', 'list');
        $this->where = array('store_id' => $_SESSION['store_id']);
        $this->indexId = 'question_id';
    }

    // 初始化入口
    public function init(){

        // 操作方式
        $act = isset($_GET['act']) ? trim($_GET['act']) : 'list';
        in_array($act, $this->aryAct) OR $this->error('不合法URL');

        // 操作函数
        $act == 'list' AND $this->_list($this->where);
        $act == 'reply' AND $this->_reply($this->where);
        $act == 'show' AND $this->_show();
        $act == 'delete' AND $this->_delete($this->where);
    }

    // 列表
    private function _list($where){

        // 状态
        if(isset($_GET['reply'])){
            $where['is_reply'] = intval($_GET['reply']);
            $this->isReply = intval($_GET['reply']);
        }

        // 查询[排除回复数据]
        $where['q_id'] = 0;
        $aryList = $this->Model->where($where)->select();

        empty($aryList) OR $this->aryList = $aryList;
        $this->display('list');
    }

    // 回复
    private function _reply($where){

        if(IS_POST){

            $_POST['store_id'] = $where['store_id'];
            $c_id = intval($_POST['c_id']);
            empty($c_id) OR $where[$this->indexId] = $c_id;

            // 数据比对，构建入库数组
            if($this->Model->create() !== false){

                if ($this->Model->add() !== false) {
                    $this->Model->where($where)->setInc('is_reply');
                    $this->success('操作成功');
                } else {
                    $this->error('操作失败');
                }

            }else{
                $this->error($this->Model->getError());
            }

        }else{

            isset($_GET['id']) AND $id = intval($_GET['id']);
            if(empty($id)) die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');

            $where[$this->indexId] = $id;
            $data = $this->Model->where($where)->find();

            empty($data) OR $this->data = $data;
            $this->display('reply');
        }

    }

    // 查看信息(ajax)[私有:不可直接地址访问]
    private function _show(){

        isset($_GET['id']) AND $id = intval($_GET['id']);
        if(empty($id)) die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');
        $where['q_id'] = $id;

        $data = $this->Model->field('create_time,content')->where($where)->find();
        if($data){
            $data['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>$data)) .')');
        }
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
            if($data['is_reply'] != 0){
                $this->Model->where(array('q_id'=>$data[$this->indexId]))->delete();
            }
            echo(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>'信息已被删除')) .')');
        }
    }

}