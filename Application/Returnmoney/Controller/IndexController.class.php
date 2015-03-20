<?php

// +----------------------------------------------------------------------
// | 退还保证金控制器
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Returnmoney\Controller;
//use Home\Controller\HomeController;
class IndexController
{
	/*
     * 此方法为后台index页面中的ajax来描瞄最近两天商品的结束时间小于当前时间的商品,如果商品已经过期则把最高出价者的uid写入goods_successid中并把goods_status的状态改为2 ,2为已经被人拍下但是还没有支付
     */
	
    public function checkouttimegoods()
    {	
		//v(D("PaimaiGoods")->CreateRechargeSn());
		//echo "ee";exit;
        $goods_obj = M("PaimaiGoods");
        #商品状态为0,扫描商品过期时间,如果有的商品已经过了过期时间来查看拍卖记录里面的最后一位竞拍者,给这个竞拍者形成一个商品订单yishu_paimai_order表生成一个记录,并把商品的状态改为goods_status=2,把商品竞拍成功者的goods_successid写入商品中,商品拍下时间也写到本商品记录中,并退还其它竞拍者的保证金
        //这段代码可以对sql进行优化   ." and goods_endtime>".strtotime("-2 day")
	#查询状态为0且过期的商品
		$goodsstatus0_field=array(
			'goods_id',
			'goods_endtime',
		);
		$goodsstatus0_where=array(
			'goods_status'=>0,
			//'goods_endtime'=>array('LT',time()),
			//10天之内的商品
			'goods_endtime'=>array("BETWEEN",array(time()-86480*10,time())),
		);
        $goodsstatus0_Arr = $goods_obj->field($goodsstatus0_field)->where($goodsstatus0_where)->select();
		
        $bidrecord_obj = M("PaimaiBidrecord");
		if(!empty($goodsstatus0_Arr)){//不空则为过期的商品
			
				foreach ($goodsstatus0_Arr as $k => $v) {
		#如果商品过期查询最后一位竟拍者

					//查找出bidrecord表中一个商品的最后一个竞拍者
					$bidrecordlastman_field=array(
						'bidrecord_uid',
						'bidrecord_time',
						'bidrecord_id',
					);
					$bidrecordlastman_where=array(
						'bidrecord_goodsid'=>$v['goods_id'],
					);
					$BidrecordLastman = $bidrecord_obj->field($bidrecordlastman_field)->where($bidrecordlastman_where)->order('bidrecord_id desc')->find();

					$bidrecord_uid = $BidrecordLastman['bidrecord_uid']; //最后一位拍者的id
					$bidrecord_time = $BidrecordLastman['bidrecord_time']; //最后一位拍者拍下的时间
					$bidrecord_id = $BidrecordLastman['bidrecord_id']; //最后一位拍者拍下拍买记录id
				
					if ($bidrecord_uid) {
		#修改goods表把最后一位竟拍者id和拍买时间写入

						//把uid写入yishu_paimai_goods中的goods_successid中,并把goods_status的状态更改为2
						$bidrecordlast_goods_data=array(
							'goods_successid'=>$bidrecord_uid,//最后一位拍者的id
							'goods_status'=>2,//更改商品状态为已经拍下,但还没有付款,为2
							'goods_bidcreatetime'=>$bidrecord_time,//商品被最后一位拍者拍下时间
							'goods_bidid'=>$bidrecord_id,//最后一位拍下的拍买记录id
						);
						$bidrecordlast_goods_where=array(
							'goods_id'=>$v['goods_id'],	
						);
						//入库更改
						if(!$goods_obj->where($bidrecordlast_goods_where)->data($bidrecordlast_goods_data)->save()){
//这里应该有个提示,如果有mongo可以把错误信息写入mongo中
							echo "商品状态更改失败:提示代码:110";
							exit;
						}
						
		#给用户形成一张订单
						//查找对应商品信息
						$order_goods_arr= $goods_obj->where(array('goods_id' => $v['goods_id']))->find(); //查找这件商品的信息
						
						$order_data=array(
							'order_goodsname'=>$order_goods_arr['goods_name'],//商品名
							'order_goodssn'=>$order_goods_arr['goods_sn'],//商品单号
							'order_goodsstartprice'=>$order_goods_arr['goods_startprice'],//商品起始价
							'order_goodsnowprice'=>$order_goods_arr['goods_nowprice'],//商品现在价格
							'order_goodsrecordid'=>$order_goods_arr['recordid'],//商品recordid
							'order_bidnum'=>$order_goods_arr['goods_bidtimes'],//商品拍卖次数
							'order_goodsbidtime'=>$bidrecord_time,//拍下时间
							'order_uid'=>$bidrecord_uid,
							'order_goodsid'=>$v['goods_id'],
							'order_createtime'=>time(),
						);
						//这里应该把商品属性查找出来也入库,但现在还没有做
						
						if(!M("PaimaiOrder")->data($order_data)->add()){
//这里应该也有一个提示,最好写入nosql中
							echo "给拍下用户自动形成订单表失败:提示代码:120";
							exit;
						 } else {
						 	#形成订单后发送短信和邮件
						 	//短信
						 	$uid = $bidrecord_uid;
						 	$mobile = get_mobile($uid);
				            //print_r($mobile);die;
				            //参数数组
				            $arr = array();
				            //支付宝支付时间
				            $arr['goods_endtime'] = date('m月d日 H时i分', time()+3*86400);
				            //支付宝支付金额
				            $arr['goods_name'] = $order_goods_arr['goods_name'];
				            Vendor('Mobile.Mobile');
				            $SMS = new \Mobile;
				            $code = $SMS->sendmobilecode($mobile, $arr);

				            //邮件
				            $user_email = get_email($uid);
             				$aa = alipay_send_email('feiniutest@163.com', 'abcd123', $user_email,'http://i.yishu.com/auction/center_order/rechargev2', $arr);
						 }
						

		#返回其它用户保证金
						//返回其它用户保证金
						$othercash_field=array(
							'bidrecord_uid',
							'bidrecord_goodsneedmoney',
						);
						$othercash_where=array(
							'bidrecord_goodsid'=>$v['goods_id'],
							'bidrecord_uid'=>array('NEQ',$bidrecord_uid),
						);
						//下面这个注释是每次保证金不变的情况下执行的sql
						//$OtherCash = $bidrecord_obj->field($othercash_field)->where($othercash_where)->group('bidrecord_uid')->select();

						//根据商品id查找每个用户的最高保证金(拍本件商品的每个用户最后一次拍下的保证金)
						$OtherCash_sql="select * from (select bidrecord_uid,bidrecord_goodsneedmoney,bidrecord_goodsid from yishu_paimai_bidrecord where bidrecord_uid<>$bidrecord_uid and bidrecord_uid<>0 and bidrecord_goodsid=".$v['goods_id']." order by bidrecord_id desc) as temp group by bidrecord_uid";
						$OtherCash=M()->query($OtherCash_sql);
						
						//下面一段代码很危险要后期进行很好优化,退还保证金的时候应该有一张流水号表来记录万一出错的
					   
						foreach ($OtherCash as $p => $q) {
							$tempmoney = $q['bidrecord_goodsneedmoney'];
							$return_needmoney_data=array(
									'recharge_sn'=>D("PaimaiGoods")->CreateRechargeSn(),//形成唯一订单号
									'recharge_uid'=>$q['bidrecord_uid'],//用户id
									'recharge_money'=>$q['bidrecord_goodsneedmoney'],//金额
									'recharge_createtime'=>time(),//时间
									'recharge_style'=>2,//2为返还的保证金
									//'recharge_status'=>2,//状态为成功
									'recharge_ip'=>get_client_ip(),//ip
									'recharge_returngid'=>$v['goods_id'],//商品id
								);
							
							
							if($return_recharge_id=M("PaimaiRecharge")->data($return_needmoney_data)->add()){
									
								$return_recharge_sql = "update bsm.bsm_member set amount=amount+$tempmoney,frozen=frozen-$tempmoney where mid=".$q['bidrecord_uid'];
								//M('member','bsm_','DB_BSM')->execute($sql)
								if(!M()->db(5, 'DB_BSM')->execute($return_recharge_sql)){
									echo "退还保证金写入用户账户失败";
									exit;
								}else{
									//如果都执行成功则修改状态为2,2为本次成功
									M("PaimaiRecharge")->where("recharge_id=$return_recharge_id")->setField("recharge_status","2");
								}

							}else{
								echo "退还保证金订单写入失败";
								exit;
							}
							

						}
					}else{//这里是
						

						//更改商品状态为1
						$change_goodsstatus1_data=array(
							'goods_status'=>1,//如果为1则为最后拍下者为机器人或没有人拍,为流拍
						);
						$change_goodsstatus1_where=array(
							'goods_id'=>$v['goods_id'],	
						);
						//入库更改
						if(!M('PaimaiGoods')->where($change_goodsstatus1_where)->data($change_goodsstatus1_data)->save()){
//这里应该有个提示,如果有mongo可以把错误信息写入mongo中
							echo "商品状态更改失败:提示代码:110";
							exit;
						}

						$bidrecordtman_field=array(
							'bidrecord_uid',
							'bidrecord_time',
							'bidrecord_id',
							'bidrecord_goodsneedmoney',
							'bidrecord_ip',
							'bidrecord_goodsid',
						);
						$bidrecordtman_where=array(
							'bidrecord_goodsid'=>$v['goods_id'],
							'bidrecord_uid'=>array('NEQ',0),
						);
						$Bidrecordpassrobot_arr = $bidrecord_obj->field($bidrecordtman_field)->order('bidrecord_id desc')->group("bidrecord_uid")->where($bidrecordtman_where)->select();
						//如果不空则退还保证金
						if(!empty($Bidrecordpassrobot_arr)){
							foreach ($Bidrecordpassrobot_arr as $m => $n) {
								
								$return_needmoney_data=array(
										'recharge_sn'=>D("PaimaiGoods")->CreateRechargeSn(),//形成唯一订单号
										'recharge_uid'=>$n['bidrecord_uid'],//用户id
										'recharge_money'=>$n['bidrecord_goodsneedmoney'],//金额
										'recharge_createtime'=>time(),//时间
										'recharge_style'=>2,//2为返还的保证金
										'recharge_status'=>2,//状态为成功
										'recharge_ip'=>get_client_ip(),//ip
										'recharge_returngid'=>$n['bidrecord_goodsid'],//商品id
									);
								
								if(!M("PaimaiRecharge")->data($return_needmoney_data)->add()){
									Vendor('Log.Mylog');
	        						$MyLOG = new \Mylog;
	        						$str="最后拍买者为机器人,返还的保证金出错:用户id:".$n['bidrecord_uid']."金额:".$n['bidrecord_goodsneedmoney']."商品ID:".$v['goods_id']."竞拍时间:".$n['bidrecord_time']."竞拍记录id:".$n['bidrecord_id'];
	        						$MyLOG->write($str);
								}
								

							}
						}

					}

				}
		}
     
    #商品状态为2即已经拍下,来扫描保证金到期时间.商品表中记录了商品拍下者的id,goods_successid,和拍下时间goods_bidcreatetime,用这个时间+我们定义的过期时间来和当前时间进行对比,如果小于了当前时间,则扣除保证金,
		$overtime=3600 * 72;//72小时
		$goodsstatus2_field=array(
			'goods_id',
			'goods_successid',
			'goods_bidcreatetime',
			'goods_needmoney',
		);
		$goodsstatus2_where=array(
			'goods_status'=>2,
		);
		
        $Goodsstatus2_Arr = $goods_obj->field($goodsstatus2_field)->where($goodsstatus2_where)->select();
        
		
		if(!empty($Goodsstatus2_Arr)){
			foreach ($Goodsstatus2_Arr as $k => $v) {
		#扣除每一笔保证金形成一张订单
				
			   if (time() > $v['goods_bidcreatetime'] + $overtime) {

				   //把商品状态改为4即到期没有付款被扣除了保证金的商品,
				   $goodsstatus4_data=array(
						'goods_status'=>4,   
				   );
				   $goodsstatus4_where=array(
						'goods_id'=>$v['goods_id'], 
				   );
				   //更改商品状态为4,即本商品到期没有付款
				   $goods_obj->where($goodsstatus4_where)->setField($goodsstatus4_data);
				   
				   $chargeorder_data=array(
						'order_status'=>3,   
				   );
				   $chargeorder_where=array(
						'order_uid'=>$v['goods_successid'],
						'order_goodsid'=>$v['goods_id'],
				   );
				   //更改自动生成的订单为锁定状态3
				   M("PaimaiOrder")->where($chargeorder_where)->setField($chargeorder_data);
				   
					$downmoney = $v['goods_needmoney']; //要扣除的金额
					$successid = $v['goods_successid']; //扣除的人
					
					//扣除保证金,每一笔形成一个订单
					$end_cash_data=array(
						'recharge_sn'=>D("PaimaiGoods")->CreateRechargeSn(),
						'recharge_uid'=>$successid,
						'recharge_money'=>-$downmoney,
						'recharge_createtime'=>time(),
						'recharge_style'=>3,//3为商品到期但未付款扣除的保证金
						//'recharge_status'=>2,//2为此交易成功
						'recharge_ip'=>get_client_ip(),
						'recharge_returngid'=>$v['goods_id'],
					);

					if($end_cach_id=M("PaimaiRecharge")->data($end_cash_data)->add()){

						$end_cash_sql="update bsm.bsm_member set frozen=frozen-$downmoney where mid=$successid";
						if(!M()->db(5, 'DB_BSM')->execute($end_cash_sql)){
							echo "商品到期没有付款扣除用户账户冻结保证金失败";
							exit;
						}else{
							//如果都执行成功则修改状态为2,2为本次成功
							M("PaimaiRecharge")->where("recharge_id=$end_cach_id")->setField("recharge_status","2");
						}
					}else{
						echo "商品到期没有付款扣除保证金形成扣除订单失败:提示代码:911";
						exit;
					}

					
			
				}
			}
		}
		
		echo date("Y-m-d H:i:s")."后台扫描成功\r\n";  
    }

}
