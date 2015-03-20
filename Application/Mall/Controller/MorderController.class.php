<?php

// +----------------------------------------------------------------------
// | 古玩城 会员中心 店铺订单 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen # 2014.01.13
// +----------------------------------------------------------------------

namespace Mall\Controller;

class MorderController extends MpublicController {

    public function _initialize(){

        parent::_initialize();

        $this->Model = D('Order');
        $this->where = array('seller_id' => $_SESSION['user_auth']['uid']);
        $this->indexId = 'order_id';
        $this->aryAct = array('send', 'list');
        $this->store_id = $_SESSION['store_id'];
    }

    /**
     * 当前页的入口控制
     */
    public function init(){

        /** 获取操作方式 */
        $act = isset($_GET['act']) ? trim($_GET['act']) : 'list';
        in_array($act, $this->aryAct) OR $this->error('不合法URL');

        /** 执行私有的方法 */
        $act == 'list' AND $this->_list($this->where);
        $act == 'send' AND $this->_send($this->where);
    }

    /**
     * [私有]列表方法
     * @param $where
     */
    private function _list($where){

        /** 查询 */
        $aryList = $this->Model->where($where)->select();
        if(!empty($aryList)){

            $this->aryList = $aryList;

            foreach($aryList as $rs){
                $oid[] = $rs['order_id'];
            }

            /** 获取对应的商品 */
            $oGoods = M('MallOrderGoods')->where(array('order_id'=>array('in', $oid)))->select();
            if($oGoods){
                foreach($oGoods as $rw){
                    $aryGoods[$rw['order_id']][] = $rw;
                }
                $this->aryGoods = $aryGoods;
            }
        }

        // 配送方式
        $aryDelivery = D('Delivery')->aryDelivery($this->store_id);
        $tagDeliverySelect = D('Delivery')->tagSelect($aryDelivery);
        empty($tagDeliverySelect) OR $this->tagDeliverySelect = $tagDeliverySelect;

        $this->display('list');
    }

    private function _send($where){

        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) OR $this->error('此信息不存在!');
        $where['order_id'] = $id;

        // 先读取信息
        $info = $this->Model->where($where)->find();

        // 根据订单号 读取 发货地址
        $info['address'] = M('MallOrderOther')->where(array('order_id'=>$info['order_id']))->find();
        prt($info);
    }
} 