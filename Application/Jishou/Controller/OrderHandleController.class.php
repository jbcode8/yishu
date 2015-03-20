<?php
	namespace Jishou\Controller;
	use Jishou\Controller\JishouController;
	use Jishou\Service\GoodsService;
	use Jishou\Service\AddressService;
	use Jishou\Service\OrderService;
	use Jishou\Addons\AlipaySubmitAddons;
	use Jishou\Addons\AlipayNotifyAddons;
	use Jishou\Service\MiscellaneousService;

	class OrderHandleController extends JishouController{

		public function _initialize(){
			parent::_initialize();
			if(empty($_COOKIE['mid'])){
				header('content-type:text/html;charset=utf-8');

				//没有登录则跳转到登录页面	
				redirect('http://i.yishu.com/member/passport/login?redUrl='.$this->general_info['web_index'].__SELF__,1,'请先登录...');
				
			}

			
		}
	
		//订单的第一步操作
		public function orderFirst($goods){

			//这步是查询用户是否有已经购买了的生成的订单，但还没有付款的
			$ms = new MiscellaneousService();
			$status = $ms->userHasOrder($goods,$this->mid);
			//不为false证明有已经生成的订单,不去生成订单 去订单链接页面
			if($status !==false){
				$this->assign('status',$status);
				$this ->display('Order/order_has');
				exit;
			}

			//实例化商品的服务数据层
			$goodsService = new GoodsService();
			$goods_info = $goodsService->orderGoods($goods);

			if(empty($goods_info)){
				$this->error('数据错误');
			}

			//商品信息加载
			$this->assign('goods_info',$goods_info[0]);

			$address = new AddressService;
			$allAddress = $address->getAllAddress();

			//获取region表的省市信息

		

			//地址信息加载
			$this->assign('all_address',$allAddress);
			
			$region_names = D('Region')
					->field('name,id')
					->where(array('pid'=>2))->select();
			$this->assign('region_names',$region_names);
			$this->display('Order/order_first');
		}
		
		public function orderFirstAgain($goods){
			//已经有了订单的情况下顾客再次生成订单

			//实例化商品的服务数据层
			$goodsService = new GoodsService();
			$goods_info = $goodsService->orderGoods($goods);

			if(empty($goods_info)){
				$this->error('数据错误');
			}

			//商品信息加载
			$this->assign('goods_info',$goods_info[0]);

			$address = new AddressService;
			$allAddress = $address->getAllAddress();

			//获取region表的省市信息

		

			//地址信息加载
			$this->assign('all_address',$allAddress);
			
			$region_names = D('Region')
					->field('name,id')
					->where(array('pid'=>2))->select();
			$this->assign('region_names',$region_names);
			$this->display('Order/order_first');
		}

		//订单的第二步操作
		public function orderSecond(){
			/*if(!IS_POST){
				$this->error('无法请求');
			}*/
			
			$order = I('post.');
			

			$maniOrder = new OrderService();
			
			//初始化数据
			if(!$maniOrder->prepare($order)){
				$this->error('数据错误');
			}
			
			//生成订单信息
			if(!$maniOrder->createOrder()){
				$this->error('生成订单失败');
			}
			//渲染订单的信息
			$this->assign('order_info',$maniOrder->orderInfo());

			
			$this->display('Order/order_second');
			

		
		}

		//订单的第三部操作
		public function orderThird($order_is){

			//获取到传递过来的order_id
			$order_id = explode('-',$order_is);
			$order_id = $order_id[1];
			
			if(!M('JishouOrderInfo')
				->where(array('order_id'=>$order_id))
				->limit(1)
				->select()
				)
			{
				$this->error('数据错误');
			}

			$this->assign('order_id',$order_id);
			$this->display('Order/order_third');
		
		}

		//生成支付接口
		public function orderPay(){
			$order_info = $_POST;
			$order_id = $order_info['order_id'];
			$pay_type=$order_info['pay_type'];

			
			$order = M('JishouOrderInfo')
				->where(array('order_id'=>$order_id))
				->limit(1)
				->select();
			$order=$order[0];

			$order_goods = M('JishouOrderGoods')
				->where(array('order_id'=>$order_id))
				->limit(1)
				->select();
			$order_goods =$order_goods[0];
			
			if(empty($order))
			{
				$this->error('数据错误');
			}

			//支付方式选择
			switch($pay_type){
				//支付宝支付
				case '1':
						load('@.alipay_core_func');
						load('@.alipay_md5_func');
						$alipay_config = C('ALI_CONFIG');
						$alipay_info =C('ALI_INFO');

						//支付类型
						$payment_type = "1";
						//必填，不能修改
						//服务器异步通知页面路径
						//$notify_url = U('OrderHandle/alipayNotifyUrl');
						$notify_url = U('Jishou/BankReturnApi/alipayNotifyUrl');
						//需http://格式的完整路径，不能加?id=123这类自定义参数        //页面跳转同步通知页面路径
						//$return_url = U('OrderHandle/alipayReturnUrl');
						$return_url = U('Jishou/BankReturnApi/alipayReturnUrl');
						//需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/        //卖家支付宝帐户
						$seller_email = $alipay_info['seller_email'];
						//必填        //商户订单号
						$out_trade_no = $order['order_sn'];
						//商户网站订单系统中唯一订单号，必填        //订单名称
						$subject = $order_goods['goods_name'];
						$body='中国艺术网-打造中国最大的寄售平台';
						//必填        //付款金额
						$total_fee = $order['goods_amount'];
						//必填        //订单描述        $body = $_POST['WIDbody'];
						//商品展示地址
						$show_url = U('/Jishou/Goods/goods-'.$order_goods['goods_id']);
						//需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html        //防钓鱼时间戳
						$anti_phishing_key = "";
						//若要使用请调用类文件submit中的query_timestamp函数        //客户端的IP地址
						$exter_invoke_ip = "";
						//非局域网的外网IP地址，如：221.0.0.1


			/************************************************************/

					//构造要请求的参数数组，无需改动
						$parameter = array(
								"service" => "create_direct_pay_by_user",
								"partner" => trim($alipay_config['partner']),
								"payment_type"	=> $payment_type,
								"notify_url"	=> $notify_url,
								"return_url"	=> $return_url,
								"seller_email"	=> $seller_email,
								"out_trade_no"	=> $out_trade_no,
								"subject"	=> $subject,
								"total_fee"	=> $total_fee,
								"body"	=> $body,
								"show_url"	=> $show_url,
								"anti_phishing_key"	=> $anti_phishing_key,
								"exter_invoke_ip"	=> $exter_invoke_ip,
								"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
						);

						//建立请求
						
						$alipaySubmit = new AlipaySubmitAddons($alipay_config);
						
						$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
						//echo htmlspecialchars($html_text);exit;
						$this->assign('html_text',$html_text);
						$this->display('Order/order_pay');	
						break;
					
				//网银在线支付
				case '2':
					$chinabank_config = C('chinabank_config');
					$this->v_mid = $chinabank_config['mid'];						    // 1001是网银在线的测试商户号，商户要替换为自己的商户号。
					$v_mid = $chinabank_config['mid'];						    // 1001是网银在线的测试商户号，商户要替换为自己的商户号。

					$this->v_url = U('Jishou/BankReturnApi/receive');	// 商户自定义返回接收支付结果的页面。对应Receive.php示例。
					$v_url = U('Jishou/BankReturnApi/receive');	// 商户自定义返回接收支付结果的页面。对应Receive.php示例。
	                                                    //参照"网银在线支付B2C系统商户接口文档v4.1.doc"中2.3.3.1
	
					$key   = $chinabank_config['key'];								    // 参照"网银在线支付B2C系统商户接口文档v4.1.doc"中2.4.1进行设置。

					$this->remark2 = '[url:='.U('Jishou/BankReturnApi/autoReceive').']'; //服务器异步通知的接收地址。对应AutoReceive.php示例。必须要有[url:=]格式。
					$remark2 = '[url:='.U('Jishou/BankReturnApi/autoReceive').']'; //服务器异步通知的接收地址。对应AutoReceive.php示例。必须要有[url:=]格式。

					//$v_oid = trim($_POST['v_oid']); 
				    //$v_oid = date('Ymd',time())."-".$v_mid."-".date('His',time());//订单号，建议构成格式 年月日-商户号-小时分钟秒
				    $this->v_oid = $order['order_sn'];
				    $v_oid = $order['order_sn'];
	 
					$this->v_amount = $order['goods_amount'];                   //支付金额                 
					$v_amount = $order['goods_amount'];                   //支付金额                 
				    $this->v_moneytype = "CNY";                                            //币种
				    $v_moneytype = "CNY";                                            //币种

					$text = $v_amount.$v_moneytype.$v_oid.$v_mid.$v_url.$key;        //md5加密拼凑串,注意顺序不能变
				    $this->v_md5info = strtoupper(md5($text));                             //md5函数加密并转化成大写字母

					//$remark1 = trim($_POST['remark1']);					 //备注字段1
					$this->remark1 = '欢迎选购';					 //备注字段1
					$this->display('Order/order_bank_pay');
					exit;
					break;
			
			}

			exit;

		}

		//支付宝返回数据接口
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
		

		public function setDefaultAddress(){
			if(!IS_AJAX){
				$this->error('数据错误');
			}


		}
	}

?>