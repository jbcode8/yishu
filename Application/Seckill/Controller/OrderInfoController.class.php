<?php
	namespace Seckill\Controller;
	use Think\Controller;
	class OrderInfoController extends Controller {

		public function createOrder(){
			//非ajax调用则返回错误
			if(!IS_AJAX){header('content-type:text/html;charset=utf-8');exit('文件不存在,sorry');}
			$mid = I('cookie.mid');
			$skgoods_id = I('post.skgoods_id',0,'intval');
			$retdata = array();
			if(empty($mid)||empty($skgoods_id)||$skgoods_id<0){
				$retdata = array('code'=>0,'messg'=>'参数错误');
				exit(json_encode($retdata));
			}

			//查询该用户是否已抢到过

			$this_order = M('SeckillOrderInfo')->where(array('orderinfo_goodsid'=>$skgoods_id,'orderinfo_uid'=>$mid))
									->find();
			if(!empty($this_order)){
				$retdata['code'] = 2;	
				$retdata['messg'] = '你已经抢过了，不能再抢了';	
				$retdata['other'] = $this_order;
				$retdata['url'] =U('Auction/CenterOrder/seckillbuy@i.yishu.com');	
				exit(json_encode($retdata));	
			}

			//查询是否库存足量
			$this_order = M('SeckillGoods')
					->where(array('skgoods_id'=>$skgoods_id))
					->getField('skgoods_inventory');
			if($this_order<=0){
				$retdata['code'] = 0;	
				$retdata['messg'] = '商品已抢完';	
				exit(json_encode($retdata));	
			}


			//既没抢过，库存也足,则生成订单

			$goods_info = M('SeckillGoods')->find($skgoods_id);
			//生成订单号
			$orderinfo_sn = date('ymdHis').mt_rand(100,999);
			$order_info_data = array(
				'orderinfo_sn' => $orderinfo_sn,
				'orderinfo_goodsid' => $goods_info['skgoods_id'],
				'orderinfo_uid' => $mid,
				'orderinfo_createtime' =>time(),
				'orderinfo_amount' =>$goods_info['skgoods_killprice'],
				);
			$order_data = array(
				//'order_orderinfoid' => 1,
				'order_goodsname' =>$goods_info['skgoods_name'],
				'order_goodsid' =>$goods_info['skgoods_id'],
				'order_goodssn' =>$goods_info['skgoods_sn'],
				'order_goodskillprice' =>$goods_info['skgoods_killprice'],
				'order_goodsrecordid' =>$goods_info['recordid'],
				'order_marketprice' =>$goods_info['skgoods_marketprice'],
				'order_uid' =>$mid,
				'order_createtime' =>time(),
				'order_number'=> 1,
				);
			//订单入库
			$order_orderinfoid = M('SeckillOrderInfo')->add($order_info_data);
			if(!$order_orderinfoid){
				$retdata['code'] = 0;	
				$retdata['messg'] = '订单生成失败';	
				exit(json_encode($retdata));
			}

			//订单的商品入库
			$order_data['order_orderinfoid'] =$order_orderinfoid;
			$flag = M('SeckillOrder')->add($order_data);
			if(!$flag){
				M('SeckillOrderInfo')->where(array('orderinfo_id'=>$order_orderinfoid))
							->limit(1)->delete();
				$retdata['code'] = 0;	
				$retdata['messg'] = '订单商品生成失败';
			}

			//库存减一
			M('SeckillGoods')->where(array('skgoods_id'=>$skgoods_id))->setDec('skgoods_inventory');


			//成功生成订单
			$retdata['code'] = 1;	
			$retdata['messg'] = '订单已生成';	
			$retdata['url'] =U('Auction/CenterOrder/seckillbuy@i.yishu.com');
			exit(json_encode($retdata));	

		}

	}