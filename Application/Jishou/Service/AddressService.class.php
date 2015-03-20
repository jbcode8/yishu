<?php
	/*
	* AddressService.class.php 收货地址操作类
	*/
	namespace Jishou\Service;

	class AddressService{



		/*
		*@method 获取用户的所有的收货地址
		*@param  null
		*@return array  返回所有的收货地址数组
		*/
		public function getAllAddress(){

			$user_id = $_COOKIE['mid'];
			$field = 'address_id,address_uid,address_provincename,
				address_cityname,address_receiver,address_address,
				address_mobile,address_zipcode,address_isdefault';
			$address = D('Address')
					->field($field)
					->where(array('address_uid'=>$user_id))->order('address_isdefault desc')->select();
			return $address;
		}
		
		/*
		*@method 获取用户的指定的收货地址
		*@param  null
		*@return array  返回单个的收货地址信息
		*/
		public function getSingleAddress($address_id){
			$field = 'address_id,address_uid,address_provincename,
				address_cityname,address_receiver,address_address,
				address_mobile,address_zipcode,address_isdefault';
			$address = D('Address')
					->field($field)
					->where(array('address_id'=>$address_id))
					->limit(1)->select();
			return $address[0];
		}
	
	}

?>