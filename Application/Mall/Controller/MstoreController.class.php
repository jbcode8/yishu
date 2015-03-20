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
				
				$sid = $this->Model->add();
                if ($sid !== false) {

					// 加入静态文件表
					$staticData['views'] = 1;
					$staticData['collect'] = 1;
					$staticData['sales'] = 1;
					$staticData['comment'] = 1;
					$staticData['store_id'] = $sid;
					M('MallStoreCount')->data($staticData)->add();

                    $this->success('申请成功，请等待审核！');

                } else {
                    $this->error('申请失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }
        }else{

            // 经营类型 & 主要货源
            $this->htmlStoreType = $this->Model->tagRadioStoreType();
            $this->htmlStoreSource = $this->Model->tagRadioStoreSource();

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

            // 经营类型 & 主要货源
            $this->htmlStoreType = $this->Model->tagRadioStoreType($info['store_type']);
            $this->htmlStoreSource = $this->Model->tagRadioStoreSource($info['store_source']);

            $this->display('edit');
        }
    }
}