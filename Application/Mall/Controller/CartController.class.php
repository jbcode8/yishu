<?php

// +----------------------------------------------------------------------
// | 前端 购物车 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class CartController extends FpublicController {

    public function _initialize(){

        parent::_initialize();
        $this->modelName = 'Cart';

        // 检测是否为登录用户
        isset($_SESSION['user_auth']) AND $loginUser = $_SESSION['user_auth'];
        if(isset($loginUser) && !empty($loginUser)){
            $this->loginUser = $loginUser;
        }
    }

    public function init() {

        isset($_GET['step']) AND $step = intval($_GET['step']);
        isset($step) OR $this->error('非法操作！');

        switch($step){
            case 1: $this->_step1(); break;
            case 2: $this->_step2(); break;
            case 3: $this->_step3(); break;
        }
    }

    /**
     * 登录后跳转的URL
     */
    private function _go2login(){
        header('location: '.'http://www.yishu.com/goto.php?url='.currentUri());
        exit;
    }

    /**
     * 解析SESSION保存的购物车信息为数组
     * @return array|bool
     */
    private function _parseCart2ary(){

        isset($_SESSION['sessCart']) AND $cartList = $_SESSION['sessCart'];
        if(isset($cartList) && is_array($cartList) && !empty($cartList)){

            // 依次是 产品ID | 店铺ID
            $aryGids = $arySids = array();
            foreach($cartList as $sid => $info){
                $arySids[] = $sid; # 店铺ID
                foreach($info['g'] as $id => $count){
                    $aryGids[] = $id; # 产品ID
                }
            }

            // 根据产品ID获取产品信息
            $goodsList = D('Goods')->getCartGoods($aryGids);

            // 根据店铺ID获取店铺信息
            $storeList = D('Store')->getCartStore($arySids);

            return array($cartList, $storeList, $goodsList);

        }else{
            $this->error('购物车暂时没有商品，请先选择产品！');
        }
    }

    private function _step1(){

        // 解析购物车的数据
        $cartInfo = $this->_parseCart2ary();
        if($cartInfo){
            list($aryCart, $storeList, $goodsList) = $cartInfo;
            $this->aryCart = $aryCart;
            $this->storeList = $storeList;
            $this->goodsList = $goodsList;
        }

        $this->display('step1');
    }

    private function _step2(){

		// 先判断是否登录
		if(empty($this->loginUser)){
			$this->error('请先登录后继续购物！');
		}

        // 根据SESSION保存的购物车的信息解析为数组列表
        $cartInfo = $this->_parseCart2ary();
        if($cartInfo){
            list($aryCart, $storeList, $goodsList) = $cartInfo;
            $this->aryCart = $aryCart;
            $this->storeList = $storeList;
            $this->goodsList = $goodsList;
        }

        // 获取收货地址
        $loginUserId = $this->loginUser['uid'];
        $aryShoppingAddress = D('ShoppingAddress')->where(array('uid'=>$loginUserId))->order('is_default DESC')->select();
        if(empty($aryShoppingAddress)){
            $this->error('请设置您的收货地址', U('__APP__/Mall/Uaddress/init'), 3);
        }else{
            $this->aryShoppingAddress = $aryShoppingAddress;
        }

        // 获取地区的数组
        $aryCity = regionOne(2);
        $aryRegion = array();
        foreach($aryCity as $rs){
            $aryRegion[$rs['id']] = $rs;
        }
        $this->aryRegion = $aryRegion;
        $this->jsonRegion = json_encode($aryRegion);

        $this->display('step2');
    }

    private function _step3(){

        isset($_GET['fee']) AND $fee = $_GET['fee'];
        isset($fee) OR exit('无效的订单或者订单已过期！');

        isset($_GET['nolt']) AND $nolt = $_GET['nolt'];
        isset($nolt) OR exit('无效的订单或者订单已过期！');
        $arySn = explode('|', $nolt);

        isset($_GET['ono']) AND $ono = $_GET['ono'];
        if(isset($ono)){
            $sessOno = session('out_trade_no');
            if($ono == $sessOno){

                $this->totalFee = $fee;
                $this->arySn = $arySn;

                $map = '';
                foreach($arySn as $rs){
                    $map .= 'OR `order_sn` = '."'".$rs."' ";
                }
                $map = trim($map,'OR');

                // 更新对应订单的状态
                D('Store')->where($map)->data(array('status'=>1))->save();

                // 注销验证用的订单号
                session('out_trade_no', null);

                $this->display('step3');
            }else{
                exit('无效的订单或者订单已过期！');
            }
        }else{
            $this->redirect('无效的订单或者订单已过期！');
        }
    }

    /**
     * 动态的删除购物车的列表
     */
    public function ajaxDelCart(){

        isset($_GET['sid']) AND $sid = intval($_GET['sid']);
        isset($sid) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效店铺')) .')');

        isset($_GET['gid']) AND $gid = intval($_GET['gid']);
        isset($gid) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效商品')) .')');

        /** 判断此商品是否存在 */
        $data = D('Goods')->field('goods_id')->where(array('goods_id'=>$gid,'store_id'=>$sid,'status'=>2))->find();
        empty($data) AND die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'此商品已经下架')) .')');

        /** [操作Session的购物车列表] */
        isset($_SESSION['sessCart']) AND $cartList = $_SESSION['sessCart'];
        if(is_array($cartList) && !empty($cartList) && isset($cartList[$sid]['g'][$gid])){
            unset($cartList[$sid]['g'][$gid]); /** 清楚当前商品 */
            if(empty($cartList[$sid]['g'])){
                unset($cartList[$sid]); /** 如果该店下没有商品，则清楚该店 */
            }
            session('sessCart', $cartList);
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1')) .')');
        }else{
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'您的操作发生一个意外')) .')');
        }
    }

    /**
     * 动态的修改购物车的产品数量
     */
    public function ajaxCartNum(){

        isset($_GET['sid']) AND $sid = intval($_GET['sid']);
        isset($sid) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效店铺')) .')');

        isset($_GET['gid']) AND $gid = intval($_GET['gid']);
        isset($gid) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效商品')) .')');

        isset($_GET['num']) AND $num = max(intval($_GET['num']), 1);
        isset($num) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效库存量')) .')');

        /** 判断此商品是否存在 */
        $data = D('Goods')->field('goods_num')->where(array('goods_id'=>$gid,'store_id'=>$sid,'status'=>2))->find();
        empty($data) AND die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'此商品已经下架')) .')');

        /** 计算库存数量是否合法 */
        $num > $data['goods_num'] AND $num = $data['goods_num'];

        /** 操作Cart的SESSION信息 */
        isset($_SESSION['sessCart']) AND $cartList = $_SESSION['sessCart'];
        if(is_array($cartList) && !empty($cartList) && isset($cartList[$sid]['g'][$gid])){
            $cartList[$sid]['g'][$gid] = $num;
            session('sessCart', $cartList);
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1')) .')');
        }else{
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'您的操作发生一个意外')) .')');
        }
    }
}