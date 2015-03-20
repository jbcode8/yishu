<?php
	
	/**
	*寄售的地址操作入口
	*/
	namespace Jishou\Controller;
	use Jishou\Controller\JishouController;
	class JishouAddressController extends JishouController{
		
	
		/*
			订单流程收货地址的入口1
		*/
		public function address1(){
			if(!IS_AJAX){
				$this->error('无法调用接口');
			}
			
			$returnData = array();

			$returnData['flag'] =true;
			$user_id = $_COOKIE['mid'];
			$provinceid = $_POST['address_provinceid'];
			$cityid = $_POST['address_cityid'];
			$address_provincename = D('Region')->field('name')->where(array('id'=>$provinceid))->limit(1)->select();
			$address_provincename = $address_provincename[0]['name'];
			
			$address_cityname = D('Region')->field('name')->where(array('id'=>$cityid))->limit(1)->select();
			$address_cityname = $address_cityname[0]['name'];
			
			$address_map = array(
				'address_uid' => $user_id,
				'address_provinceid'=>$provinceid,
				'address_provincename' => $address_provincename,
				'address_cityid' =>$cityid,
				'address_cityname' =>$address_cityname,
				'address_address'  =>$_POST['address_address'],
				'address_mobile' =>$_POST['address_mobile'],
				'address_receiver' =>$_POST['address_receiver'],
				'address_zipcode'  =>$_POST['address_zipcode'],
				'address_createtime' =>time(),
				'address_isdefault' => 1,
			);
			$returnData['data'] = $address_map;
			$returnData['data']['del_url']= U('AddressAction/dela_url');
			$address = D('Address');
			if($address->create($address_map)){
				$address_id = $address->add();
				$address->where(array('address_uid'=>$user_id,'address_id'=>array('neq',$address_id)))
					->save(array('address_isdefault'=>0));
				$returnData['data']['address_id']=$address_id;
				exit(json_encode($returnData));
			}

			$returnData['flag'] = false;
			

			exit(json_encode($returnData));

			
		}

		//获取全部的city的值
		public function getCity(){
			$provinceid = $_POST['provinceid'];
			$citys = D('Region')->field('id,name')->where(array('pid'=>$provinceid))->select();
			if(empty($citys)){
				exit(json_encode(array('flag'=>false,'data'=>'')));
			}

			exit(json_encode(array('flag'=>true,'data'=>$citys)));
		}
	
	}