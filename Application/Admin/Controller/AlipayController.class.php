<?php

namespace Admin\Controller;

Class AlipayController extends AdminController {
	public function index(){
		//用户表查询条件
		$where = array(
			);
		//分页
		$p = I('p',1,'intval');
		$prePage = 15;//每页显示条数
		//用户表查询字段
		$field = 'mid,username,mobile,loginnum';

		//用户表查询
		$list = M('member', 'bsm_', DB_BSM)->field($field)->limit(15)->page($p . ',' . $prePage)->order('mid desc')->select();
		//总记录数
		$list_num = M('member', 'bsm_', DB_BSM)->count('mid');
		//是否充值，用户姓名
		foreach($list as $k=>$v){
			$tem = $this->getCountMoneys($v['mid']);
			$list[$k]['recharge_sum'] = $tem['money_sum'];//充值金额
			$list[$k]['recharge'] = $tem['money_num'];//充值次数
			$list[$k]['reviver'] = $this->getUserName($v['mid']);//收货人
			$list[$k]['canpai_num'] = $this->getCanpaiNum($v['mid']);//参拍次数
		}

		//分页实例化
		$Page = new \Think\Page($list_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出

		$this->list = $list;
		$this->display();
	}

	public function getCountMoneys($uid = '') {

		//返回数组
		$tem = array();//包含充值成功次数和总的充值金额
		//查询条件
		$where = array(
			'recharge_uid' => $uid,
			'recharge_style' => array('IN', '1,6'),//账户充值金额
			'recharge_status' => 2 	//充值成功
			);
		//查询字段
		//$field = 'recharge_money';
		//充值成功次数
		$tem['money_num'] = M('paimai_recharge')->where($where)->count('recharge_money');
		//充值金额
		$tem['money_sum'] = M('paimai_recharge')->where($where)->sum('recharge_money');
		return $tem;
	}

	/**
	 *	获得用户姓名
	 *
	 */
	public function getUserName($uid = ''){
		//查询条件
		$where = array(
			'orderinfo_uid' => $uid
			);
		 $temp = M('paimai_order_info')->field('orderinfo_reciver')->where($where)->find();
		 return $temp['orderinfo_reciver'];
	}

	/**
	 *	所拍物品
	 *
	 */

	public function paipin() {
		//接收id
		$uid = I('uid');
		//查询条件
		$where = array(
			'orderinfo_uid' => $uid,
			);
		//查询字段
		$field = 'orderinfo_id, orderinfo_sn, orderinfo_status, orderinfo_receivetime, orderinfo_sendtime, orderinfo_provincename, orderinfo_cityname, orderinfo_address, orderinfo_status';


		$list = M('paimai_order_info')->field($field)->where($where)->select();
		foreach($list as $k=>$v) {
			$list[$k]['goodsname'] = $this->getGoodsName($v['orderinfo_id']);
		}
		$this->list = $list;
		$this->display();
	}

	/**
	 *	获得订单的商品的名称
	 *
	 */ 
	public function getGoodsName($orderinfo_id = '') {
		
		$temp = M('paimai_order')->field('order_goodsname')->where(array('order_orderinfoid' => $orderinfo_id))->find();

		return $temp['order_goodsname'];


	}

	/**
	 *	获得参拍次数
	 *
	 */

	public function getCanpaiNum($uid = '') {
		$where = array(
			'recharge_uid' => $uid,
			'recharge_style' => array('IN', array('4', '5'))
			);
		return M('paimai_recharge')->where($where)->count();
		
	}
}