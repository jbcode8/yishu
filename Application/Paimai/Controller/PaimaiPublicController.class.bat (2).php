<?php
// +----------------------------------------------------------------------
// | HomeController.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Paimai\Controller;
use Home\Controller\HomeController;


class PaimaiPublicController extends HomeController{
	 public function _initialize(){
		 parent::_initialize();
		 //拍卖前台 cookie赋值给session
		$_SESSION['mid'] = !empty($_COOKIE['mid'])?$_COOKIE['mid']:null;
		$_SESSION['username'] = !empty($_COOKIE['username'])?$_COOKIE['username']:null;
		$_SESSION['groupid'] = !empty($_COOKIE['groupid'])?$_COOKIE['groupid']:null;
        /*C(api('Admin/Config/lists',array('group'=>1)));
		$special_where=array(
			"special_isshow"=>1,
			"special_isdelete"=>0,
		);
		$Special=M("PaimaiSpecial")->where($special_where)->order("special_id desc")->limit(5)->select();
		$this->assign("Special",$Special);
		$this->assign("action",__ACTION__);
		$this->assign("cur_timestamp",time());*/
		$auth = getLoginStatus();
		if($auth){
			//¸öÈËÐÅÏ¢
			$global_message_count = M()->db(2,'DB_BSM')->table('bsm.bsm_message')->where(array('status'=>4,'to_uid'=>$auth['mid']))->count();
			$this->global_message_count = $global_message_count;
			$user_amount = M()->db(2,'DB_BSM')->table('bsm.bsm_member')->where(array('mid'=>$auth['mid']))->getField('amount');

			$uid = $auth['mid'];
			$moneys = M("paimai_deposit")->where(array('uid'=>$uid,'status'=>0))->sum('money');
			$tx_moneys_success = M("paimai_deposit")->where(array('uid'=>$uid,'status'=>1))->sum('money');
		#总金额
			$allamount=format_money($this->getUserAmountByUid($uid)-$tx_moneys_success);
			$this->AllAmount=($allamount?$allamount:0);
		#冻结金额
			$returnmoney = $this->getReturnMoneyByUid($uid);//12.12(ch)商品到期返还的保证金
			//$frozen=abs($this->getFrozenmoneyByUid($uid));
			//冻结金额=扣除保证金($frozen)-到期没有付款金额（$this->cuted_money($uid)）-返还的保证金
			$frozen = abs($frozen+$this->cuted_money($uid)-$returnmoney);
			$this->FrozenAmount=format_money(($frozen?$frozen:0)+$moneys);
		#可用
			$cuted_money=abs($this->cuted_money($uid));
			//p($cuted_money);
			//$allamount = 总-因为没有付款扣除的那笔钱
			//可用=总-冻结-因为没有付款扣除的那笔钱
			//$canuser=abs(abs($allamount-$frozen)-$cuted_money)-abs(D('Member')->tx_money($uid));
			$canuser = $allamount-$frozen;
			$this->user_amount = format_money(($canuser?$canuser:0)-$moneys);
			$user_avatar = "http://sso.yishu.com/avatar.php?uid=".$auth['mid']."&size=middle";
			$this->user_avatar = $user_avatar;
			//ä¯ÀÀ¼ÇÂ¼
			$cookie = cookie('paimai_browse');
			$user_browse = array();
			foreach($cookie as $val){
				$GoodsObj = M('PaimaiGoods');
				$Goods = $GoodsObj->field('goods_id,goods_name,goods_sn,goods_starttime,goods_endtime,goods_nowprice,recordid')->where(array('goods_isshow'=>1,'goods_id'=>$val))->find();
				$Goods["pics"] = D('Content/Document')->getPic($Goods['recordid'], 'image');
				$user_browse[] = $Goods;
			}
			$this->user_browse = $user_browse;
			$this->user_browse_length = count($user_browse);
			/*
			$my_goods = M('PaimaiBidrecord')->join('yishu_paimai_goods on yishu_paimai_goods.goods_id = yishu_paimai_bidrecord.bidrecord_goodsid')->field('yishu_paimai_goods.goods_id,yishu_paimai_goods.goods_name,yishu_paimai_goods.goods_sn,yishu_paimai_goods.goods_starttime,yishu_paimai_goods.goods_endtime,yishu_paimai_goods.goods_nowprice,yishu_paimai_goods.recordid,yishu_paimai_bidrecord.bidrecord_price')->where('yishu_paimai_bidrecord.bidrecord_uid='.$auth['mid'].' and yishu_paimai_goods.goods_endtime > '.time())->order('yishu_paimai_bidrecord.bidrecord_id desc')->group('yishu_paimai_goods.goods_id')->select();
			*/
			//ÎÒµÄÅÄÆ·£¬Î´½áÊøµÄ
			$my_goods = M()->query("select * from (SELECT yishu_paimai_goods.goods_id,yishu_paimai_goods.goods_name,yishu_paimai_goods.goods_sn,yishu_paimai_goods.goods_starttime,yishu_paimai_goods.goods_endtime,yishu_paimai_goods.goods_nowprice,yishu_paimai_goods.recordid,yishu_paimai_bidrecord.bidrecord_price FROM yishuv2.`yishu_paimai_bidrecord` INNER JOIN yishuv2.yishu_paimai_goods on yishu_paimai_goods.goods_id = yishu_paimai_bidrecord.bidrecord_goodsid WHERE ( yishu_paimai_bidrecord.bidrecord_uid=".$auth['mid']." and yishu_paimai_goods.goods_endtime > ".time()." ) ORDER BY yishu_paimai_bidrecord.bidrecord_id desc) t1 GROUP BY t1.goods_id");
			$sum_total = 0;
			foreach($my_goods as &$val){
				$val["pics"] = D('Content/Document')->getPic($val['recordid'], 'image');
				$sum_total += $val['bidrecord_price'];
			}
			$this->my_goods = $my_goods;
			$this->my_goods_count = count($my_goods);
			$this->sum_total = $sum_total;
			//print_r($my_goods);

			//ÎÒÊÕ²ØµÄÅÄÆ·
			$my_collect = M('PaimaiCollect')->join('yishuv2.yishu_paimai_goods on yishu_paimai_goods.goods_id = yishu_paimai_collect.collect_goodsid')->field('yishu_paimai_goods.goods_id,yishu_paimai_goods.goods_name,yishu_paimai_goods.goods_sn,yishu_paimai_goods.goods_starttime,yishu_paimai_goods.goods_endtime,yishu_paimai_goods.goods_nowprice,yishu_paimai_goods.recordid')->where(array('yishu_paimai_collect.collect_uid'=>$auth['mid'],'yishu_paimai_goods.goods_isshow'=>1,'yishu_paimai_goods.goods_isdelete'=>0))->order('yishu_paimai_collect.collect_id desc')->limit(8)->select();
			foreach($my_collect as &$val){
				$val["pics"] = D('Content/Document')->getPic($val['recordid'], 'image');
			}
			
			if(empty($my_collect)) //Ã»ÓÐÎÒµÄÊÕ²ØÈ¡ÍÆ¼ö
			{
				//ÍÆ¼öÅÄÆ·
				//ÓÒ²à8¸öÌîÂú
				//ÕýÔÚ½øÐÐ
				//½ñÌì0³½Ê±¼ä
				$today_starttime=strtotime(date("Ymd", time()));
				//½ñÌìÍíÉÏ12µã
				$today_endtime=strtotime(date("Ymd", time()+86400));
				$ing_field=array(
					"goods_id,goods_name,goods_sn,goods_starttime,goods_endtime,goods_nowprice,recordid",
				);
				$ing_where=array(
					'goods_isshow'=>1,
					'goods_isdelete'=>0,
					//'goods_starttime'=>array("GT",$today_starttime),
					//'goods_starttime'=>array("LT",time()),
					'goods_starttime'=>array("BETWEEN",array($today_starttime,time())),
				);
				$total = 8;
				$IngCount=M("PaimaiGoods")->where($ing_where)->getField('count(goods_id) as count');
				if($IngCount>=$total){
					$remain_ing = $total;
					$remain_future = 0;
				}else{
					$remain_ing = $IngCount;
					$remain_future = $total - $IngCount;
				}
				$IngGoods=M("PaimaiGoods")->field($ing_field)->where($ing_where)->limit('0,'.$remain_ing)->order("goods_starttime asc,goods_id asc")->select();
				 foreach ($IngGoods as $k => &$v) {
					$v["pics"] = D('Content/Document')->getPic($v['recordid'], 'image');
				 }
				//$this->assign("IngGoods",$IngGoods);
				
				//¼´½«¿ªÅÄ
				$start_field=array(
					"goods_id,goods_name,goods_sn,goods_starttime,goods_endtime,goods_nowprice,recordid",
				);
				$start_where=array(
					'goods_isshow'=>1,
					'goods_isdelete'=>0,
					'goods_starttime'=>array("GT",time()),
					//'goods_starttime'=>array("LT",$today_endtime),
					//'goods_starttime'=>array("BETWEEN",array(time(),$today_endtime)),
				);
				$StartGoods=M("PaimaiGoods")->field($start_field)->where($start_where)->limit('0,'.$remain_future)->order("goods_starttime asc,goods_id asc")->select();
				 foreach ($StartGoods as $k => $v) {
					$v['pics'] = D('Content/Document')->getPic($v['recordid'], 'image');
				}
				//$this->assign("StartGoods",$StartGoods);

				//$my_collect = array_merge($IngGoods,$StartGoods);
				foreach($IngGoods as $val){
					$my_collect[] = $val;
				}
				foreach($StartGoods as $val){
					$my_collect[] = $val;
				}
				$this->collect_status = 0;
			}else{
				$this->collect_status = 1;
			}
			//print_r($my_collect);
			$this->my_collect = $my_collect;

		}

    }

	//Çå¿Õcookie ÅÄÂôä¯ÀÀ¼ÇÂ¼
	public function clearCookie(){
		cookie('paimai_browse',null);
		echo 1;
	}
	 /*
        用户总金额
    */
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
    /*
        得到冻结
    */
    public function getFrozenmoneyByUid($uid){
        $frozen_where=array(
            'recharge_uid'=>$uid,
            'recharge_returngid'=>array('NEQ','0'),
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>array('IN','4,5'),//4为拍商品时扣除的保证金,5为补扣的 7提现
        );
        return M("paimai_recharge","yishu_","yishu")->where($frozen_where)->sum("recharge_money");
    }
    /*
        这个是没有按时付款永远扣除的资金
    */
    public function cuted_money($uid){
        $frozen_where=array(
            'recharge_uid'=>$uid,
            'recharge_returngid'=>array('NEQ','0'),
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>3,//3为商品到期没有付款扣除的保证金
        );
        return M("paimai_recharge","yishu_","yishu")->where($frozen_where)->sum("recharge_money");
    }

	/*
        提现
    */
    public function tx_money($uid){
        $tx_where=array(
            'recharge_uid'=>$uid,
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>7,//3为商品到期没有付款扣除的保证金
        );
        return M("paimai_recharge","yishu_","yishu")->where($tx_where)->sum("recharge_money");
    }
    /*
        这个是返还用户的资金
    */
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