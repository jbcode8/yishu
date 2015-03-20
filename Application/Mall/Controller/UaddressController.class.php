<?php

// +----------------------------------------------------------------------
// | 会员中心 卖家地址 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class UaddressController extends MpublicController {

    public function _initialize(){

        parent::_initialize();

        $this->Model = D('ShoppingAddress');
        $this->where = array('uid' => $_SESSION['user_auth']['uid']);
        $this->aryAct = array('show', 'delete', 'list', 'add', 'edit', 'set_default', 'edit_data');
        $this->indexId = 'address_id';
    }

    // 其他操作
    public function init(){

        // 操作方式
        $act = isset($_GET['act']) ? trim($_GET['act']) : 'list';
        in_array($act, $this->aryAct) OR $this->error('不合法URL');

        // 操作方式
        $act == 'show' AND $this->_show();
        $act == 'delete' AND $this->_delete($this->where);
        $act == 'list' AND $this->_list($this->where);
        $act == 'add' AND $this->_add($this->where);
        $act == 'edit' AND $this->_edit($this->where);
        $act == 'set_default' AND $this->_set_default($this->where);
        $act == 'edit_data' AND $this->_edit_data($this->where);
    }

    // 前端购物车的修改地址
    private function _edit_data(){

        // 收货地址ID
        isset($_GET['address_id']) AND $address_id = intval($_GET['address_id']);
        isset($address_id) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');
        $where['address_id'] = $address_id;

        // 收货人
        isset($_GET['consignee']) AND $consignee = trim($_GET['consignee']);
        isset($consignee) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请填写收货人')) .')');
        $data['consignee'] = $consignee;

        // 城市
        isset($_GET['region_id']) AND $region_id = intval($_GET['region_id']);
        isset($region_id) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请选择城市')) .')');
        $data['region_id'] = $region_id;

        // 收货地址
        isset($_GET['address']) AND $address = trim($_GET['address']);
        isset($address) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请填写收货地址')) .')');
        $data['address'] = $address;

        // 联系手机
        isset($_GET['mobile']) AND $mobile = trim($_GET['mobile']);
        isset($mobile) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请填写联系手机')) .')');
        $data['mobile'] = $mobile;

        // 修改操作
        if($this->Model->where($where)->data($data)->save() !== false){
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1')) .')');
        }else{
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'修改时发生了一个意外错误！')) .')');
        }
    }

    // 前端的购物车设置默认地址
    private function _set_default($where){

        // 检测是否传值
        isset($_GET['id']) AND $id = intval($_GET['id']);
        if(!isset($id)) die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');

        // 检测数据库是否存在
        $int = $this->Model->where($where)->find();
        if($int <= 0) die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'此信息不存在')) .')');

        // 先清空其他的收获地址的默认地址
        $this->Model->where(array('uid'=>$where['uid']))->data(array('is_default'=>0))->save();

        // 然后将传值过来的地址设置为默认
        $bool = $this->Model->where(array('uid'=>$where['uid'],'address_id'=>$id))->setInc('is_default');
        $status = $bool ? 1 : 0;
        die(trim($_GET['backfunc']).'('.json_encode(array('status' => $status)).')');
    }

    // 列表
    private function _list($where){

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

    // 添加信息[私有:不可直接地址访问]
    private function _add($where){

        if(IS_POST){

            $_POST['uid'] = $where['uid'];

            // 入库前检测是否有收获地址
            $intAddress = $this->Model->where(array('uid'=>$where['uid']))->find();
            if($intAddress > 0){
                if($_POST['is_default'] == 1){
                    // 清除其他存在地址的默认状态
                    $this->Model->where(array('uid'=>$where['uid']))->data(array('is_default'=>0))->save();
                }
            }else{
                $_POST['is_default'] = 1;
            }

            // 数据比对，构建入库数组
            if($this->Model->create() !== false){

                if ($this->Model->add()) {
                    $this->success('操作成功');
                } else {
                    $this->error('操作失败');
                }

            }else{
                $this->error($this->Model->getError());
            }

        }else{

            $this->display('add');
        }
    }

    // 编辑信息[私有:不可直接地址访问]
    private function _edit($where){

        if(IS_POST){

            $where[$this->indexId] = $_POST[$this->indexId];


            // 数据比对，构建入库数组
            if($this->Model->create() !== false){

                if ($this->Model->where($where)->save() !== false) {
                    $this->success('操作成功');
                } else {
                    $this->error('操作失败');
                }

            }else{
                $this->error($this->Model->getError());
            }

        }else{

            isset($_GET['id']) AND $id = intval($_GET['id']);
            isset($id) OR $this->error('无效信息');
            $where[$this->indexId] = $id;

            $data = $this->Model->where($where)->find();
            empty($data) AND $this->error('信息不存在或者已经删除！');

            $this->data = $data;
            $this->display('edit');
        }
    }
} 