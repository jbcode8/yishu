<?php
	/**
	* 银行返回的数据入口
	*/
	namespace Jishou\Controller;
	use Jishou\Controller\JishouController;
	use Jishou\Addons\AlipayNotifyAddons;

	class BankReturnApiController extends JishouController{

		//网银在线的异步通知接口
		public function autoReceive(){
			$chinabank_config = C('chinabank_config');
			//****************************************	//MD5密钥要跟订单提交页相同，如Send.asp里的 key = "test" ,修改""号内 test 为您的密钥
											//如果您还没有设置MD5密钥请登陆我们为您提供商户后台，地址：https://merchant3.chinabank.com.cn/
			$key=$chinabank_config['key'];							//登陆后在上面的导航栏里可能找到“B2C”，在二级导航栏里有“MD5密钥设置”
														//建议您设置一个16位以上的密钥或更高，密钥最多64位，但设置16位已经足够了
			//****************************************

			$v_oid     =trim($_POST['v_oid']);      
			$v_pmode   =trim($_POST['v_pmode']);      
			$v_pstatus =trim($_POST['v_pstatus']);      
			$v_pstring =trim($_POST['v_pstring']);      
			$v_amount  =trim($_POST['v_amount']);     
			$v_moneytype  =trim($_POST['v_moneytype']);     
			$remark1   =trim($_POST['remark1' ]);     
			$remark2   =trim($_POST['remark2' ]);     
			$v_md5str  =trim($_POST['v_md5str' ]);     
			/**
			 * 重新计算md5的值
			 */
			                           
			$md5string=strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key)); //拼凑加密串
			if ($v_md5str==$md5string)
			{
				
			   if($v_pstatus=="20")
				{
				   //支付成功
					//商户系统的逻辑处理（例如判断金额，判断支付状态(20成功,30失败),更新订单状态等等）......
					$order_status = M('JishouOrderInfo')
							->field('pay_status,order_sn,
									recipient,province,city,
									detail_address,mobilephone,post_code')
							->where(array('order_sn'=>$v_oid))
							->limit(1)->select();
					$pay_status = $order_status[0]['pay_status'];
					if($pay_status!=1){  //不是支付了的状态则变为支付了的状态
						M('JishouOrderInfo')->where(array('order_sn'=>$v_oid))
									->data(array('pay_status'=>1,'pay_time'=>time(),'is_on_sale'=>0))->save();
						$oid = M('JishouOrderInfo')->field('order_id') ->where(array('order_sn'=>$v_oid)) ->find();
						$gid = M('JishouOrderGoods')->field('goods_id')->where(array('order_id'=>$oid['order_id']))->find();
						M('JishouGoods')->where(array('goods_id'=>$gid['goods_id']))
									->data(array('is_on_sale'=>0))->save();
						$action_data = M('JishouOrderInfo')
									->field('order_id,order_status,pay_status,shipping_status')
									->where(array('order_sn'=>$v_oid))
									->limit(1)->select();
						$action_map= $action_data[0];
						$action_map['action_style']='付款成功';
						$action_map['log_time'] =time();

						M('JishouOrderAction')->add($action_map);
					}

					echo "ok";	
				}
			  echo "ok";
				
			}else{
				echo "error";
			}


		}

		//网银在线的浏览器返回的通知的入口
		public function receive(){

			$chinabank_config = C('chinabank_config');
			//****************************************	//MD5密钥要跟订单提交页相同，如Send.asp里的 key = "test" ,修改""号内 test 为您的密钥
														//如果您还没有设置MD5密钥请登陆我们为您提供商户后台，地址：https://merchant3.chinabank.com.cn/
			$key=$chinabank_config['key'];							//登陆后在上面的导航栏里可能找到“B2C”，在二级导航栏里有“MD5密钥设置”
														//建议您设置一个16位以上的密钥或更高，密钥最多64位，但设置16位已经足够了
			//****************************************
				
			$v_oid     =trim($_POST['v_oid']);       // 商户发送的v_oid定单编号   
			$v_pmode   =trim($_POST['v_pmode']);    // 支付方式（字符串）   
			$v_pstatus =trim($_POST['v_pstatus']);   //  支付状态 ：20（支付成功）；30（支付失败）
			$v_pstring =trim($_POST['v_pstring']);   // 支付结果信息 ： 支付完成（当v_pstatus=20时）；失败原因（当v_pstatus=30时,字符串）； 
			$v_amount  =trim($_POST['v_amount']);     // 订单实际支付金额
			$v_moneytype  =trim($_POST['v_moneytype']); //订单实际支付币种    
			$remark1   =trim($_POST['remark1' ]);      //备注字段1
			$remark2   =trim($_POST['remark2' ]);     //备注字段2
			$v_md5str  =trim($_POST['v_md5str' ]);   //拼凑后的MD5校验值  

			/**
			 * 重新计算md5的值
			 */
			                           
			$md5string=strtoupper(md5($v_oid.$v_pstatus.$v_amount.$v_moneytype.$key));

			/**
			 * 判断返回信息，如果支付成功，并且支付结果可信，则做进一步的处理
			 */


			if ($v_md5str==$md5string)
			{
				if($v_pstatus=="20")
				{
					//支付成功，可进行逻辑处理！
					//商户系统的逻辑处理（例如判断金额，判断支付状态，更新订单状态等等）......


					$order_status = M('JishouOrderInfo')
							->field('pay_status,order_sn,
									recipient,province,city,
									detail_address,mobilephone,post_code')
							->where(array('order_sn'=>$v_oid))
							->limit(1)->select();
					$pay_status = $order_status[0]['pay_status'];
					if($pay_status!=1){  //不是支付了的状态则变为支付了的状态
						M('JishouOrderInfo')->where(array('order_sn'=>$v_oid))
									->data(array('pay_status'=>1,'pay_time'=>time(),'is_on_sale'=>0))->save();
						$oid = M('JishouOrderInfo')->field('order_id') ->where(array('order_sn'=>$v_oid)) ->find();
						$gid = M('JishouOrderGoods')->field('goods_id')->where(array('order_id'=>$oid['order_id']))->find();
						M('JishouGoods')->where(array('goods_id'=>$gid['goods_id']))
									->data(array('is_on_sale'=>0))->save();
						$action_data = M('JishouOrderInfo')
									->field('order_id,order_status,pay_status,shipping_status')
									->where(array('order_sn'=>$v_oid))
									->limit(1)->select();
						$action_map= $action_data[0];
						$action_map['action_style']='付款成功';
						$action_map['log_time'] =time();

						M('JishouOrderAction')->add($action_map);
					}

					$status['flag'] =1;
					$status['data'] =$order_status[0];
					$this->assign('status',$status);


				}else{
					$status['flag']= 0;
					$status['data']['out_trade_no'] = $v_oid;
					$status['data']['trade_no'] = $v_pmode;
					$this->assign('status',$status);
				}

			}else{
					$status['flag']= 0;
					$status['data']['out_trade_no'] = $v_oid;
					$status['data']['trade_no'] = $v_pmode;
					$this->assign('status',$status);
			}
			$this->display('Order/order_success');

		}

		//支付宝字符完成返回的通知入口
		public function alipayReturnUrl(){

			load('@.alipay_core_func');
			load('@.alipay_md5_func');
			$alipay_config = C('ALI_CONFIG');
			$alipayNotify = new AlipayNotifyAddons($alipay_config);
			$verify_result = $alipayNotify->verifyReturn();
			
			
			if($verify_result) {//验证成功
			
				
				//请在这里加上商户的业务逻辑程序代码
				
				//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
				//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

				//商户订单号
				$out_trade_no = $_GET['out_trade_no'];

				//支付宝交易号
				$trade_no = $_GET['trade_no'];

				//交易状态
				$trade_status = $_GET['trade_status'];

				
				if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
					//判断该笔订单是否在商户网站中已经做过处理
						//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
						//如果有做过处理，不执行商户的业务程序
					$order_status = M('JishouOrderInfo')
							->field('pay_status,order_sn,
									recipient,province,city,
									detail_address,mobilephone,post_code')
							->where(array('order_sn'=>$out_trade_no))
							->limit(1)->select();
					$pay_status = $order_status[0]['pay_status'];
					if($pay_status!=1){  //不是支付了的状态则变为支付了的状态
						M('JishouOrderInfo')->where(array('order_sn'=>$out_trade_no))
									->data(array('pay_status'=>1,'pay_time'=>time(),'is_on_sale'=>0))->save();
						$oid = M('JishouOrderInfo')->field('order_id') ->where(array('order_sn'=>$v_oid)) ->find();
						$gid = M('JishouOrderGoods')->field('goods_id')->where(array('order_id'=>$oid['order_id']))->find();
						M('JishouGoods')->where(array('goods_id'=>$gid['goods_id']))
									->data(array('is_on_sale'=>0))->save();
						$action_data = M('JishouOrderInfo')
									->field('order_id,order_status,pay_status,shipping_status')
									->where(array('order_sn'=>$out_trade_no))
									->limit(1)->select();
						$action_map= $action_data[0];
						$action_map['action_style']='付款成功';
						$action_map['log_time'] =time();

						M('JishouOrderAction')->add($action_map);
					}

					$status['flag'] =1;
					$status['data'] =$order_status[0];
					$this->assign('status',$status);


				}
				else {
					$status['flag']= 0;
					$status['data'] = $_GET;
					$this->assign('status',$status);
				  //echo "trade_status=".$_GET['trade_status'];
				}
					
				//echo "验证成功<br />";

				//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
				
				/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			}
			else {

				$status['flag']= false;
				$status['data'] = $_GET;
				//验证失败
				//如要调试，请看alipay_notify.php页面的verifyReturn函数
				$this->assign('status',$status);
			}

			$this->display('Order/order_success');
		}
		
		//支付宝支付异步返回接口
		public function alipayNotifyUrl(){
			load('@.alipay_core_func');
			load('@.alipay_md5_func');
			$alipay_config = C('ALI_CONFIG');
			$alipayNotify = new AlipayNotifyAddons($alipay_config);
			$verify_result = $alipayNotify->verifyReturn();
			if($verify_result) {//验证成功
				
				//请在这里加上商户的业务逻辑程序代码
				
				//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
				//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
					$out_trade_no = $_POST['out_trade_no'];

					//支付宝交易号
					$trade_no = $_POST['trade_no'];

					//交易状态
					$trade_status = $_POST['trade_status'];


				if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
					//判断该笔订单是否在商户网站中已经做过处理
						//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
						//如果有做过处理，不执行商户的业务程序
						$order_status = M('JishouOrderInfo')
							->field('pay_status,order_sn,
									recipient,province,city,
									detail_address,mobilephone,post_code')
							->where(array('order_sn'=>$out_trade_no))
							->limit(1)->select();
						$pay_status = $order_status[0]['pay_status'];
						if($pay_status!=1){  //不是支付了的状态则变为支付了的状态
							M('JishouOrderInfo')->where(array('order_sn'=>$out_trade_no))
										->data(array('pay_status'=>1,'pay_time'=>time(),'is_on_sale'=>0))->save();
							$oid = M('JishouOrderInfo')->field('order_id') ->where(array('order_sn'=>$v_oid)) ->find();
							$gid = M('JishouOrderGoods')->field('goods_id')->where(array('order_id'=>$oid['order_id']))->find();
							M('JishouGoods')->where(array('goods_id'=>$gid['goods_id']))
									->data(array('is_on_sale'=>0))->save();
							$action_data = M('JishouOrderInfo')
										->field('order_id,order_status,pay_status,shipping_status')
										->where(array('order_sn'=>$out_trade_no))
										->limit(1)->select();
							$action_map= $action_data[0];
							$action_map['action_style']='付款成功';
							$action_map['log_time'] =time();

							M('JishouOrderAction')->add($action_map);
						}

						echo 'success';
					}else {
						echo 'fail';
					}

			}else {

				echo 'fail';
			}

		}





	}
