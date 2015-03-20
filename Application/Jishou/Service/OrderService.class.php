<?php
	namespace Jishou\Service;
	use Jishou\Service\AddressService;
	class OrderService{
		

		protected $goods_data=array();

		protected $address_data = array();

		protected $order_sn = '';

		protected $order_id = '';
			
		//保存的是orderFirst提交过来的数据;
		protected $param = array();
		
		/*
		*@method goods_data address_data 的数据准备
		*@param  订单页面传过来的数据信息
		*@return boolean  成功或失败
		*/
		public function prepare($param){
			
			$goods_id=$param['goods_id'];
			$address_id=$param['address_id'];
			$goods_field = 'goods_id,user_id,goods_sn,goods_name,goods_price';
			$goods_data =M('JishouGoods')
					->field($goods_field)
					->where(array('goods_id'=>$goods_id))->select();
			$address = new AddressService();
			$address_data=$address->getSingleAddress($address_id);
		
			if(empty($goods_data)||empty($address_data)){
				
				return false;
			}
			

			//获取商品的额图片信息，等会要加入到订单商品表里面
			$goods_img = M('JishouGoodsImg')
						->field('img_name,img_path')
						->where(array('goods_id'=>$goods_id,'img_type'=>'thumb'))
						->limit(1)->select();

			
			$this->param = $param;
			if(empty($goods_img)){
				$this->goods_data = $goods_data[0];
			}else{
				$this->goods_data = array_merge($goods_data[0],$goods_img[0]);
			}
			$this->address_data = $address_data;



			return true;

		}
		/*
		*@method 订单入库
		*@param  订单页面传过来的数据信息
		*@return boolean  成功或失败
		*/
		public function createOrder(){
			
			$user_id = $_COOKIE['mid'];
			$this->order_sn=$order_sn = mt_rand(10,99).date('ymdHis').mt_rand(10,99);
			
			$map_order = array(
				'order_sn'=>$order_sn,
				'seller_id' =>$this->goods_data['user_id'],
				'purchaser_id' =>$user_id,
				'goods_amount'=>$this->goods_data['goods_price'],
				'recipient' =>$this->address_data['address_receiver'],
				'province' =>$this->address_data['address_provincename'],
				'city' =>$this->address_data['address_cityname'],
				'detail_address'=>$this->address_data['address_address'],
				'post_code'  =>$this->address_data['address_zipcode'],
				'mobilephone' =>$this->address_data['address_mobile'],
				'leave_message' =>$this->param['remark'],
				'atime'  =>time(),
			);

			
			//保存order_id的值
			$this->order_id=$order_id = M('JishouOrderInfo')->data($map_order)->add();
		
			if(empty($order_id)){
				return false;
			}

			
			$map_order_goods = array(
				'order_id' =>$order_id,
				'goods_id' =>$this->goods_data['goods_id'],		
				'goods_sn' =>$this->goods_data['goods_sn'],
				'goods_name' =>$this->goods_data['goods_name'],
				'goods_price' =>$this->goods_data['goods_price'],
				'img_name'	=>$this->goods_data['img_name'],
				'img_path'   =>$this->goods_data['img_path'],
				'goods_number' =>1
			);

			$order_goods_id = M('JishouOrderGoods')
				->data($map_order_goods)->add();
			

			if(empty($order_goods_id)){

				M('JishouOrderInfo')->where(array('order_id'=>$order_id))
						->limit(1)->delete();
				
				return false;
			}

			

			return true;
		}

		/*
		*@method 获取orderSecond  所需的订单信息
		*@param  null
		*@return array  返回orderSecond所需的数据
		*/
		public function orderInfo(){
			$order_info=array(
				'order_id' =>$this->order_id,
				'order_sn'=>$this->order_sn,
				'remark' =>$this->param['remark'],
				'address' =>$this->address_data,
				'goods' =>$this->goods_data,
			);

			return $order_info;
		
		}
		
		
	
	}
?>
