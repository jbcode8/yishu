<?php

/**
 *	手机APP 用户接口
 *
 */

 namespace Api\Controller;

 Class UserController extends BaseController {
	
	//填写方法 get传一个参数：verify=1
    //个人资料    
    public function PersonalData(){

      $mid = I('get.mid');

      if(!$mid){
			$return = array('result' => false, 'data' => '缺少用户id', 'code' => 2);
			$this->ajaxReturn($return);
		 }  
      $field = 'realname, nickname, sex, mobile';
      $where = array('mid' => $mid);
      $user_data = M("member","bsm_","BSM")->field($field)->where($where)->select();
	  if(!$user_data){
			$return = array('result' => false, 'data' => '', 'code' => 2);
			$this->ajaxReturn($return);
	   }

		$return = array('result' => true, 'data' => $user_data, 'code' => 1);
		$this->ajaxReturn($return);

	}

    //修改昵称,真实姓名，性别
    public function EditPersonalData(){

        $mid = I('get.mid');
		$nickname = I('get.nickname');
		$realname = I('get.realname');
		$sex = I('get.sex');

		if(!$mid){
			$return = array('result' => false, 'data' => '缺少用户id', 'code' => 2);
			$this->ajaxReturn($return);
	    }
		$data = array();
		$where = array('mid' => $mid);
		$data['nickname'] = $nickname ? $nickname : M("member","bsm_","BSM")->where($where)->getField('nickname');
        $data['realname'] = $realname ? $realname : M("member","bsm_","BSM")->where($where)->getField('realname');
        $data['sex'] = $sex ? $sex : M("member","bsm_","BSM")->where($where)->getField('sex');
        $User = M("member","bsm_","BSM")->where($where)->save($data);

	    if($User){
			$return = array('result' => true, 'data' => '修改成功', 'code' => 1);
			$this->ajaxReturn($return);
	    }else{
			$return = array('result' => false, 'data' => '修改失败', 'code' => 2);
			$this->ajaxReturn($return);
		}
	}

    //查询收货地址
      public function MyAddress(){

       $mid = I('get.mid');
		if(!$mid){
			$return = array('result' => false, 'data' => '缺少用户id', 'code' => 2);
			$this->ajaxReturn($return);
	    }

      $addr_field = array('address_id, address_provinceid, address_provincename, address_cityname, address_cityid, address_address, address_receiver, address_tel, address_mobile, address_isdefault, address_zipcode');
      $where = array('address_uid' => $mid);
      $address_list = M("address","bsm_","BSM")->field($addr_field)->where($where)->order('address_id desc')->select();
  
      if($address_list){
			$return = array('result' => true, 'data' => $address_list, 'code' => 1);
			$this->ajaxReturn($return);
	    }else{
			$return = array('result' => false, 'data' => '', 'code' => 2);
			$this->ajaxReturn($return);
		}

	  }

     //修改收货地址
     public function UpdateAddress(){

        $mid = I('get.mid');
        $address_id = I('get.address_id');
        $address_provinceid = I('get.address_provinceid');  //城市ID
        $address_cityid = I('get.address_cityid');          //地区ID
		$address_address = I('get.address_address');
		$address_receiver = I('get.address_receiver'); 
		$address_mobile = I('get.address_mobile');
		$address_zipcode = I('get.address_zipcode');

		if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }
		if(!$address_id){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }

         $data = array();
		 $where = array('address_id' => $address_id);
		 $address_province_where = array('id' => $address_provinceid);
         $address_city_where = array('id' => $address_cityid);
		 $data['address_provinceid'] = $address_provinceid ? $address_provinceid : M("address","bsm_","BSM")->where($where)->getField('address_provinceid');
		 $data['address_cityid'] = $address_cityid ? $address_cityid : M("address","bsm_","BSM")->where($where)->getField('address_cityid');
         $data['address_provincename']= $address_provincename ? $address_provincename : M("Region")->where($address_province_where)->getField('name');
         $data['address_cityname']= $address_cityname ? $address_cityname : M("Region")->where($address_city_where)->getField('name');
         $data['address_address'] = $address_address ? $address_address : M("address","bsm_","BSM")->where($where)->getField('address_address');
         $data['address_receiver'] = $address_receiver ? $address_receiver : M("address","bsm_","BSM")->where($where)->getField('address_receiver');
         $data['address_mobile'] = $address_mobile ? $address_mobile : M("address","bsm_","BSM")->where($where)->getField('address_mobile');
		 $data['address_zipcode'] = $address_zipcode ? $address_zipcode : M("address","bsm_","BSM")->where($where)->getField('address_zipcode');     
         $address = M("address","bsm_","BSM")->where($where)->save($data);

        if($address){
			$return = array('result' => true, 'data' => '修改成功', 'code' => 1);
			$this->ajaxReturn($return);
	    }else{
			$return = array('result' => false, 'data' => '修改失败', 'code' => 2);
			$this->ajaxReturn($return);
		}

	 }

     //添加收货地址
	 public function Addaddress(){

        $mid = I('get.mid');
        $address_provinceid = I('get.address_provinceid');  //城市ID
        $address_cityid = I('get.address_cityid');          //地区ID
		$address_address = I('get.address_address');        //详细地址
		$address_receiver = I('get.address_receiver');      //收货人
		$address_mobile = I('get.address_mobile');         //电话
		$address_zipcode = I('get.address_zipcode');       //邮编

		if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }        
        
         $data = array();
		 $address_province_where = array('id' => $address_provinceid);
         $address_city_where = array('id' => $address_cityid);
		 $data['address_uid'] = $mid;
         $data['address_provinceid'] = $address_provinceid;
		 $data['address_cityid'] = $address_cityid;
         $data['address_provincename']=  M("Region")->where($address_province_where)->getField('name');
         $data['address_cityname']= M("Region")->where($address_city_where)->getField('name');
		 $data['address_address'] = $address_address;
		 $data['address_receiver'] = $address_receiver;
		 $data['address_mobile'] = $address_mobile;
		 $data['address_zipcode'] = $address_zipcode;
         $data['address_createtime'] = time();
         $address = M("address","bsm_","BSM")->add($data);

        if($address){
			$return = array('result' => true, 'data' => '添加成功', 'code' => 1);
			$this->ajaxReturn($return);
	    }else{
			$return = array('result' => false, 'data' => '添加失败', 'code' => 2);
			$this->ajaxReturn($return);
		}

	 }
    
     //删除收货地址
	 public function DelAddress(){

        $mid = I('get.mid');
        $address_id = I('get.address_id');
		if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }
		if(!$address_id){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }
        $del = M("address","bsm_","BSM")->where(array('address_id' => $address_id))->delete();
        if($del){
			$return = array('result' => true, 'data' => '删除成功', 'code' => 1);
			$this->ajaxReturn($return);
	    }else{
			$return = array('result' => false, 'data' => '删除失败', 'code' => 2);
			$this->ajaxReturn($return);
		}

	 }

	 //设置默认收货地址
      public function DefaultAddress(){

        $mid = I('get.mid');
        $address_id = I('get.address_id');
        if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }	
		if(!$address_id){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }
        $where = array('address_uid' => $mid);
        $address_list = M("address","bsm_","BSM")->where($where)->setField('address_isdefault', 0);    
		$address_id_where = array('address_id' => $address_id);
        $update = M("address","bsm_","BSM")->where($address_id_where)->setField('address_isdefault', 1);
        if($update){
			$return = array('result' => true, 'data' => '设置成功', 'code' => 1);
			$this->ajaxReturn($return);
	    }else{
			$return = array('result' => false, 'data' => '设置失败', 'code' => 2);
			$this->ajaxReturn($return);
		}

	  }

      //财务管理
      public function UserFinance(){

        $mid = I('get.mid');
        if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }        
        $data['amountmoney'] = $this->getUserAmountByUid($mid);   //总金额
		$data['balance'] = $this->getUserBalance($mid);       //可用余额
		$data['frozenmoney'] = $this->getFrozenmoneyByUid($mid);  //冻结余额
		$return = array('result' => true, 'data' => $data, 'code' => 1);	
        $this->ajaxReturn($return);

	  }


      //账单列表  付款状态  未付款=1   已付款=2
      public function UserBillslist( $p = 1, $count = 10 ){

        $mid = I('get.mid');
        if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }
		
	    $field='recharge_money, recharge_createtime, recharge_style, recharge_sn, recharge_status, recharge_sn, recharge_trade_no, recharge_paytime';
		$unpaid_where = array(
				'recharge_uid' => $mid,
				'recharge_money' => array('NEQ', 0),
				'recharge_status' => 1,
			);
        $unpaiddata=M('PaimaiRecharge')->field($field)->order("recharge_createtime desc")->limit(($p-1)*$count . ',' . $count)->where($unpaid_where)->select();
		$paid_where = array(
				'recharge_uid' => $mid,
				'recharge_money' => array('NEQ', 0),
				'recharge_status' => 2,
			);
        $paiddata=M('PaimaiRecharge')->field($field)->order("recharge_createtime desc")->limit(($p-1)*$count . ',' . $count)->where($paid_where)->select();

		$return = array('result' => true, 'data' => $unpaiddata, 'data2' => $paiddata, 'code' => 1);
		$this->ajaxReturn($return);

	  }

       
      //账单明细
      public function UserBillDetail(){

        $mid = I('get.mid');
        $recharge_sn = I('get.recharge_sn');
        if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }
        if(!$recharge_sn){
			$return = array('result' => false, 'data' => '此账单不存在', 'code' => 2);
			$this->ajaxReturn($return);
	    }

        $field='recharge_money, recharge_createtime, recharge_style, recharge_sn, recharge_status, recharge_sn, recharge_trade_no, recharge_paytime';
		$where = array(
				'recharge_uid' => $mid,
				'recharge_sn' => $recharge_sn,
			);
	    $details= M("PaimaiRecharge")->field($field)->where($where)->select();
		$return = array('result' => true, 'data' => $details, 'code' => 1);	
        $this->ajaxReturn($return);

	  }


     //地区联动
	 public function Region(){

        $field='id, name';
        $where = array(
		    'pid' => 2,
	    );
        $reg = M("Region")->field($field)->where($where)->select();
		$reg_data = array();

        foreach($reg as $k => $v){
		    $reg_data['area0'][$v['id']] = $v['name'];
			$id = $v['id'];
			$region_where = array(
				    'pid' => $id,
				);
            $arr = M("Region")->field($field)->where($region_where)->select();
			$city_arr = array();			
			/*foreach($arr as $ck => $cv){
			    $city_arr[$cv['id']] = array($cv['name'], $cv['id']);				
			}*/
			$reg_data['area1'][$v['id']] = $arr;
		}	
        $return = array('result' => true, 'data' => $reg_data, 'code' => 1);	
        $this->ajaxReturn($return);

	 }

   
   //修改头像
    public function UpdateUserpic(){

       $mid = I('get.mid');
	   $image = I('post.image');
        if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }
       $filename="yishu".$mid."userpic.jpg";//要生成的图片名字
       /*
	   $xmlstr =  $GLOBALS[HTTP_RAW_POST_DATA];
	   if(empty($xmlstr)){ 
		   $xmlstr = file_get_contents('php://input');
	   }*/
       echo $image;exit;
	   $jpg = $image;//得到post过来的二进制原始数据
	   $file = fopen("Uploads/User/HeadPic".$filename,"w");//打开文件准备写入
	   fwrite($file,$jpg);//写入
	   fclose($file);//关闭

       $data = array();
       $data['userpic'] = "Uploads/User/HeadPic".$filename."";
	   $where = array('mid' => $mid);
	   $updatepic = M("member","bsm_","BSM")->where($where)->save($data);

        if($updatepic){
			$return = array('result' => true, 'data' => '设置成功', 'code' => 1);
			$this->ajaxReturn($return);
	    }else{
			$return = array('result' => false, 'data' => '设置失败', 'code' => 2);
			$this->ajaxReturn($return);
		}
	}


 }