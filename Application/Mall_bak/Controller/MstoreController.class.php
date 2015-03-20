<?php

// +----------------------------------------------------------------------
// | 古玩城 会员中心 店铺资料 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen # 2014.01.09
// +----------------------------------------------------------------------

namespace Mall\Controller;

class MstoreController extends MpublicController {

    public function _initialize(){

        parent::_initialize();
        $this->Model = D('Store');
        $this->where = array('store_id' => $_SESSION['store_id']);
    }

    // 初始化入口
    public function index(){

        $where = $this->where;
        if($where['store_id']){
            $this->_edit($where);
        }else{
            $this->_add(array('uid'=>$_SESSION['user_auth']['uid']));
        }
    }


    // 店铺申请
    public function _add($where){

        if(IS_POST){

            $_POST['uid'] = $where['uid'];
            $data = $this->Model->create();

            if($data){

                if ($this->Model->add() !== false) {
                    $this->success('申请成功，请等待审核！');
                } else {
                    $this->error('申请失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }
        }else{
            $this->display('add');
        }
    }

    // 店铺修改
    private function _edit($where){

        if(IS_POST){

            $data = $this->Model->create();

            if($data){

                if ($this->Model->where($where)->save() !== false) {
                    $this->success('编辑成功！');
                } else {
                    $this->error('编辑失败！');
                }

            }else{

                $this->error($this->Model->getError());
            }

        }else{

            $info = $this->Model->where($where)->find();
            empty($info) AND $this->error('信息不存在！');

            $this->info = $info;
            $this->display('edit');
        }
    }
}