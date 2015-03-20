<?php

namespace Mobile\Model;

use Think\Model;

/**
 * wap拍卖用户中心模型 by Usagi 2015-1-21 
 */
class MobileMemberModel extends Model{

	//用户收货地址列表 返回的第一条数据为默认地址
	public function getAddrList($uid){
	    $addr_field = array(
		    'address_id',
			'address_provincename',
			'address_cityname',
			'address_address',
			'address_receiver',
			'address_tel',
			'address_mobile',
			'address_isdefault'
		);
		return M()->db(2, 'DB_BSM')->table('bsm_address')->where(array('address_uid' => $uid))->order('address_isdefault desc, address_id desc')->select();
	}

	/**
	 * 用户账单信息
	 * @param  int $uid      用户id
	 * @param  int $p        页码
	 * @param  int $page_num 每页记录数
	 * @param  int $status   0 => 未成功 1 => 未支付 2 => 已支付
	 * @return array
	 */
	public function getBillList($uid = 0, $p = 1, $page_num = 10, $status = -1){
		if(!$uid){
		    return false;
		}
		$bill_field = array(
		    'recharge_createtime',
			'recharge_style',
			'recharge_paytime',
			'recharge_money'
		);
		if($status == -1){
		    $bill_where = array(
			    'recharge_uid' => $uid,
				'recharge_money' => array('NEQ', 0),
			);
		}else{
		    $bill_where = array(
				'recharge_uid' => $uid,
				'recharge_money' => array('NEQ', 0),
				'recharge_status' => $status
			);
		}
		$bill_list = M('paimai_recharge')->field($bill_field)->where($bill_where)->order('recharge_id desc')->limit(($p-1)*$page_num . ',' . $page_num)->select();
	    
		foreach($bill_list as &$v){
		    $v['time'] = $v['recharge_paytime'] ? date('Y-m-d H:i:s', $v['recharge_paytime']) : date('Y-m-d H:i:s', $v['recharge_createtime']);
			switch($v['recharge_style']){
			    case 1: $v['desc'] = '支付宝充值';break;
				case 2: $v['desc'] = '退还的保证金';break;
				case 3: $v['desc'] = '商品到期没有付款扣除的保证金';break;
				case 4: $v['desc'] = '拍商品时候扣除的保证金';break;
				case 5: $v['desc'] = '超过限额增加的保证金';break;
				case 6: $v['desc'] = '网银在线充值';break;
				case 7: $v['desc'] = '在线提现';break;
				default: $v['desc'] = '充值失败';
			}
		}
		return $bill_list;
	}

    //用户总金额
	public function getUserAmount($uid){
	    $amount_where = array(
			'recharge_uid' => $uid,
			'recharge_status' => 2,
			'recharge_style' => array('IN', '1,6')
		);
		$user_amount = M('paimai_recharge')->where($amount_where)->sum('recharge_money');

		//返回总金额 - 商品到期没付款的保证金
		return $user_amount + $this->cuted_money($uid);
	}

	//没按时付款 永远扣除的保证金
	public function cuted_money($uid){
	    $frozen_where = array(
		    'recharge_uid' => $uid,
			'recharge_returngid' => array('NEQ', '0'),
			'recharge_status' => 2,
			'recharge_style' => 3
		);
		return M('paimai_recharge')->where($frozen_where)->sum('recharge_money');
	}

	//获取冻结的保证金
	public function getFrozenMoney($uid){
	    $frozen_where = array(
		    'recharge_uid' => $uid,
			'recharge_returngid' => array('NEQ', '0'),
			'recharge_status' => 2,
			'recharge_style' => array('IN', '4,5')
		);
		return M('paimai_recharge')->where($frozen_where)->sum('recharge_money');
	}

	//获取未拍到商品返还用户的金额
	public function getReturnMoney($uid){
	    $return_where = array(
		    'recharge_uid' => $uid,
			'recharge_returnid' => array('NEQ', '0'),
			'recharge_status' => 2,
			'recharge_style' => 2
		);
		return M('paimai_recharge')->where($return_where)->sum('recharge_money');
	}
}