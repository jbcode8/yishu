<?php

namespace Api\Controller;
use Think\Controller;

/**
 *	手机APP 基类接口
 */
Class BaseController extends Controller {

	//验证
	public function _initialize() {
		$verify = I('get.verify', '');
		/*$params = I('get.');
		
		unset($params['verify']);
		$sign = $this->getSign($params, C('API_VERIFY'));
		if(empty($verify) || $verify !== $sign){
		    exit('Request error!');
		}*/
		//p($param);
		//if(empty($verify) || $verify !== C('API_VERIFY')){
			//exit('Request error!');
		//}
		//if(empty($verify) || $verify !== C('API_VERIFY')){
			//exit('Request error!');
		//}

	}
	
	//验签算法
	private function getSign($params, $verify){
		$v = $this->assemble($params);
	    return strtoupper(md5(strtoupper(md5($v)).$verify));
	}
	
	//验签算法
	private function assemble($params){
		if(!is_array($params)){
			return '';
		}
		ksort($params, SORT_STRING);
		$sign = '';
		foreach($params as $key => $val){
			$sign .= $key . (is_array($val) ? assemble($val) : $val);
		}
		return $sign;
	}
	
	//获取图片
	public function getPic($record_id, $type='thumb'){
	    $img_where = array(
			'yr.recordid' => $record_id,
		    'ya.type' => $type
		);
		$img_info = M('record')->alias('yr')->join('yishu_attachment ya on yr.sourceid = ya._id')->where($img_where)->find();
		return $img_info['savepath'] . $img_info['savename'];
	}

	//用户可用余额
	public function getUserBalance($uid){
		//申请中的提现
		$apply_deposit = M("paimai_deposit")->where(array('uid' => $uid, 'status'=>0))->sum('money');
		//成功的提现
		$succ_deposit = M("paimai_deposit")->where(array('uid'=>$uid,'status'=>1))->sum('money');
		//总金额
		$all_amount = format_money($this->getUserAmountByUid($uid) - $succ_deposit);
		//商品到期返还的保证金
		$return_money = $this->getReturnMoneyByUid($uid);
		//冻结资金
		$frozen = abs($this->getFrozenmoneyByUid($uid));
		//违约扣除的保证金
		$cuted_money = abs($this->cuted_money($uid));
		//冻结金额 = 扣除保证金 - 到期没有付款金额 - 返还的保证金
		$frozen_amount = format_money(abs($frozen - $cuted_money - $return_money));
		//余额
		$balance = $all_amount - $frozen_amount;
		//可用金额 = 余额 - 申请中的提现
		$user_amount = format_money(($balance ? $balance : 0) - $apply_deposit);
		return $user_amount;
	}
	 
    //用户总金额
    public function getUserAmountByUid($uid){
    
        $useramount_where=array(
            'recharge_uid'=>$uid,
            'recharge_status'=>2,//状态2为充值成功的
            // 'recharge_trade_no'=>array('NEQ',''),
            // 'recharge_buyeremail'=>array('NEQ',''),
            'recharge_style'=>array('IN','1, 6'),//1为支付宝充值
        );
        //支付宝和银行充值的
        $AllMoney=M("paimai_recharge","yishu_","yishu")->where($useramount_where)->sum("recharge_money");

        //$cuted_money=cuted_money($uid);
        //p($cuted_money);
        //返回的为总的减商品到期没有付款的
        return $AllMoney+$this->cuted_money($uid);
    }
    
    //冻结资金
    public function getFrozenmoneyByUid($uid){
        $frozen_where=array(
            'recharge_uid'=>$uid,
            'recharge_returngid'=>array('NEQ','0'),
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>array('IN','4,5'),//4为拍商品时扣除的保证金,5为补扣的 7提现
        );
        return M("paimai_recharge","yishu_","yishu")->where($frozen_where)->sum("recharge_money");
    }
    
    //没按时付款永远扣除的资金
    public function cuted_money($uid){
        $frozen_where=array(
            'recharge_uid'=>$uid,
            'recharge_returngid'=>array('NEQ','0'),
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>3,//3为商品到期没有付款扣除的保证金
        );
        return M("paimai_recharge","yishu_","yishu")->where($frozen_where)->sum("recharge_money");
    }

    //提现记录
    public function tx_money($uid){
        $tx_where=array(
            'recharge_uid'=>$uid,
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>7,//3为商品到期没有付款扣除的保证金
        );
        return M("paimai_recharge","yishu_","yishu")->where($tx_where)->sum("recharge_money");
    }

    //返还用户的资金
    public function getReturnMoneyByUid($uid){
        $return_where=array(
            'recharge_uid'=>$uid,
            'recharge_returngid'=>array('NEQ','0'),
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>2,//2为拍商品没拍到返回的保证金
        );
        return M("paimai_recharge","yishu_","yishu")->where($return_where)->sum("recharge_money");
    }
}