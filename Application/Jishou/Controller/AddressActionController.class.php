<?php

	/*
	*购物流程里面的收货地址操作的入口
	*/
	namespace Jishou\Controller;
	use Jishou\Controller\JishouController;
	class AddressActionController extends JishouController{
		
		public function dela_url(){

			if(!IS_AJAX){
				$this->error('接口无法调用');
			}
			$address_id = $_POST['address_id'];
			$user_id = $_COOKIE['mid'];
			$address = D('Address');
			$flag =$address->where(array('address_uid'=>$user_id,'address_id'=>$address_id))
					->limit(1)->delete();
			exit(json_encode($flag));
		}
	
	}