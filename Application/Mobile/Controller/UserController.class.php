<?php

// +----------------------------------------------------------------------
// | wap端用户中心_控制器
// +----------------------------------------------------------------------
// | author => Usagi
// +----------------------------------------------------------------------
namespace Mobile\Controller;

use Mobile\Controller\MobileController;

class UserController extends MobileController
{
    public function _initialize()
    {
        parent::_initialize();
		$this->page_title = '个人中心';
		$this->page_num = 10;
    }
    /**
     * 拍卖wap用户中心
     */
    public function index()
    {
		//获取会员信息
		$mid = $this->auth['mid'];
		$member_info = M()->db(2, 'DB_BSM')->table('bsm_member')->where(array('mid' => $mid))->find();
		$this->assign('m_info', $member_info);

		//获取正在拍卖的商品记录条数
		$biding_where = array(
			'bidrecord_uid'=>$mid,
			'goods_successid'=>0,
			'goods_endtime'=>array('GT',time()),
		);
		$biding_count = M('paimai_goods')->alias('pg')->join('yishu_paimai_bidrecord pb on pb.bidrecord_goodsid = pg.goods_id')->where($biding_where)->count();
		$this->assign('biding_count', $biding_count);

		//获取拍到拍品的记录条数
		$bid_fin_count = M('paimai_order')->where(array('order_uid' => $mid, 'order_status' => array('NEQ', 2)))->count();
		$this->assign('bid_fin_count', $bid_fin_count);

		//获取已购拍品的记录条数
		$buyed_count = M('paimai_order')->alias('po')->join('yishu_paimai_order_info poi on po.order_orderinfoid = poi.orderinfo_id')->where(array('order_uid' => $mid, 'order_status' => 2))->count();
		$this->assign('buyed_count', $buyed_count);

		//获取参拍拍品的记录条数
		$joined_count = M('paimai_bidrecord')->alias('pb')->join('yishu_paimai_goods pg on pb.bidrecord_goodsid = pg.goods_id')->where(array('bidrecord_uid' => $mid, 'goods_endtime' => array('LT', time())))->count();
		$this->assign('joined_count', $joined_count);
		
		$this->display('Admin:user_index');
    }

	/**
	 * 用户中心 - 我的资产
	 */
	public function my_assets(){
	    $this->page_title = '我的资产';
		$mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}

		//获取账户信息
		$m_member = D('MobileMember');
		$finance = array();

		//申请中的提现金额
		$deposit_money = M('paimai_deposit')->where(array('uid' => $mid, 'status' => 0))->sum('money');

		//提现成功的金额
		$deposit_money_succ = M("paimai_deposit")->where(array('uid' => $mid,'status'=>1))->sum('money');

		//未拍到 返还用户的金额
		$return_money = $m_member->getReturnMoney($mid);

		//冻结的保证金
		$frozen_money = abs($m_member->getFrozenMoney($mid));

		//拍到未付款永久扣除的保证金
		$cuted_money = abs($m_member->cuted_money($mid));

		//冻结金额
		$frozen = abs($frozen_money - $cuted_money - $return_money);

		//总充值金额
		$user_amount = format_money($m_member->getUserAmount($mid) - $deposit_money_succ);
        $finance['user_amount'] = $user_amount;

		//可用余额
		$balance = $user_amount - $frozen;// - $deposit_money;
		$finance['balance'] = format_money(($balance ? $balance : 0) - $deposit_money);
		
		//冻结总金额
		$finance['frozen_amount'] = format_money(($frozen ? $frozen : 0) + $deposit_money);
        
		$this->assign('finance', $finance);
		$this->display('Admin:assets');
	}

	/**
	 * 用户中心 - 账单明细列表
	 */
	public function bill_list(){
	    $this->page_title = '账单明细';
		$mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}
		$p = I('p', 1, 'intval');
		$t = I('t', 0);

		if(IS_AJAX){
			switch($t){
			    case 0: $list = D('MobileMember')->getBillList($mid, $p, $this->page_num, 2);break;
				case 1: $list = D('MobileMember')->getBillList($mid, $p, $this->page_num, 1);break;
				default: ;
			}
		    $this->ajaxReturn($list);
		}
		//已支付
		$paid_list = D('MobileMember')->getBillList($mid, $p, $this->page_num, 2);
		$this->assign('paid_list', $paid_list);

		//未支付
		$unpaid_list = D('MobileMember')->getBillList($mid, $p, $this->page_num, 1);
		$this->assign('unpaid_list', $unpaid_list);

		$this->display('Admin:bill_list');
	}

	/**
	 * 用户中心 - 我的地址列表
	 */
	public function my_address(){
	    $this->page_title = '我的地址';
		$mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}
		//获取地址列表
		$addr_field = array('address_id, address_provincename, address_cityname, address_address, address_receiver, address_tel, address_mobile, address_isdefault');
		$address_list = M()->db(2,'DB_BSM')->table('bsm_address')->field($addr_field)->where(array('address_uid' => $mid))->order('address_id desc')->select();

		$this->assign('addr_list', $address_list);
		
		$this->display('Admin:address_list');
	}

	/**
	 * 用户中心 - 新增收货地址页面
	 */
	public function add_address(){
		$this->page_title = '新增地址';
		$mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}
	    $this->display('Admin:add_address');
	}

	/**
	 * 用户中心 - 修改收货地址页面
	 */
	public function modify_address(){
	    $this->page_title = '修改地址';
		$mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}
		$address_id = I('address_id');
		$this->address_info = M()->db(2, 'DB_BSM')->table('bsm_address')->where(array('address_id' => $address_id))->find();

		$this->display('Admin:modify_address');
	}

	/**
	 * 用户中心 - 收货地址添加/编辑方法
	 */
	public function edit_address(){
	    $mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}
		$addr_id = I('addr_id', 0, 'intval');
		$addr_count = M()->db(2, 'DB_BSM')->table('bsm_address')->where(array('address_uid' => $mid))->count();
		if(!$addr_id){
			if($addr_count >= 5){
			    $this->error('最多只能添加五个收货地址');
			}
		}
		$m_addr = M()->db(2, 'DB_BSM')->table('bsm_address');
		$m_addr->create();
		$m_addr->address_uid = $mid;
		$m_addr->address_createtime = time();
		if($addr_count == 0){
		    $m_addr->address_isdefault = 1;
		}
		if(!$addr_id){
		    if($m_addr->add()){
				$this->redirect(U('Mobile/User/my_address', '', ''));
			}else{
				$this->error('添加失败');
			}
		}else{
			if($m_addr->save()){
				$this->redirect(U('Mobile/User/my_address', '', ''));
			}else{
				$this->error('更新失败');
			}
		}
	}

	/**
	 * 用户中心 - 删除收货地址
	 */
	public function delete_address(){
	    if(!AJAX){
		    $this->error('此页面不存在');
		}
		$mid = $this->auth['mid'];
		$addr_id = I('addr_id', 0, 'intval');
		if(!$addr_id){
			$data = array('status' => 0, 'info' => '该地址不存在');
		    $this->ajaxReturn($data);
		}else{
			$addr_info = M()->db(2,'DB_BSM')->table('bsm_address')->where(array('address_id' => $addr_id))->find();
			
			if($addr_info['address_uid'] != $mid){
			    $data = array('status' => 0, 'info' => '地址请求有误');
				$this->ajaxReturn($data);
			}
			$result = M()->db(2,'DB_BSM')->table('bsm_address')->where(array('address_id' => $addr_id))->delete();
			if($result){
				$data = array('status' => 1, 'info' => '删除成功');
			}else{
				$data = array('status' => 0, 'info' => '删除失败');
			}
		    $this->ajaxReturn($data);
		}
	}

	/**
	 * 用户中心 - 我的收藏
	 */
	public function my_collection(){
	    $this->page_title = '我的收藏';
		$mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}
		$p = I('p', 1, 'intval');

		//获取收藏列表
		$collect_list = D('MobileGoods')->getCollections($mid, $p, $this->page_num);
		if(IS_AJAX){
		    $this->ajaxReturn($collect_list);
		}
		$this->assign('collect_list', $collect_list);
		$this->display('Admin:collection');
	}

	/**
	 * 用户中心 - 我的拍品
	 */
	public function my_product(){
	    $mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}
		$this->page_title = "我的拍品";
		$p = I('p', 1, 'intval');
		$t = I('t', 0);
		
		$model_goods = D('MobileGoods');

		if(IS_AJAX){
		    switch($t){
			    case '0' : $list = $model_goods->getBidingGoods($mid, $p, $this->page_num);break;
				case '1' : $list = $model_goods->getAuctionedGoods($mid, $p, $this->page_num);break;
				case '2' : $list = $model_goods->getBuyedGoods($mid, $p, $this->page_num);break;
				case '3' : $list = $model_goods->getJoinedGoods($mid, $p, $this->page_num);break;
				default:;
			}
			$this->ajaxReturn($list);
		}else{
			//进行的拍品
			$biding_list = $model_goods->getBidingGoods($mid, $p, $this->page_num);
			$this->assign('biding_list', $biding_list);
			
			//拍到的拍品
			$auctioned_list = $model_goods->getAuctionedGoods($mid, $p, $this->page_num);
			$this->assign('auctioned_list', $auctioned_list);
			
			//购买的拍品
			$buyed_list = $model_goods->getBuyedGoods($mid, $p, $this->page_num);
			$this->assign('buyed_list', $buyed_list);

			//参拍过的拍品
			$joined_list = $model_goods->getJoinedGoods($mid, $p, $this->page_num);
			$this->assign('joined_list', $joined_list);

			$this->display('Admin:my_product');
		}
	}

	/**
	 * 用户中心 - 拍下的商品列表页
	 */
	public function auctioned_goods(){
	    $mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}
		$p = I('p', 1, 'intval');
		$order_list = D('MobileGoods')->getAuctionedGoods($mid, $p, $this->page_num);
		if(IS_AJAX){
		    $this->ajaxReturn($order_list);
		}else{
			$this->assign('order_list', $order_list);
		    $this->display('Admin:auctioned_goods');
		}
	}

	/**
	 * 用户中心 - 参拍的商品列表页
	 */
	public function joined_goods(){
	    //...
	}

	/**
	 * 用户中心 - 提交订单页
	 */
	public function submit_order(){
		$this->page_title = '订单详情';
	    $mid = $this->auth['mid'];
		if(!$mid){
		    redirect('http://www.yishu.com/Mobile/Index/login');
		}
		$order_id = I('oid', 0, 'intval');
		if(!$order_id){
		    $this->error('此页面不存在');
		}

		//订单信息
		$order_info = D('MobileGoods')->getOrderInfo($order_id);
		$this->assign('order_info', $order_info);
		
		//收货地址
		$addr_list = D('MobileMember')->getAddrList($mid);
		$this->assign('addr_list', $addr_list);

		$this->display('Admin:submit_order');
	}
}
