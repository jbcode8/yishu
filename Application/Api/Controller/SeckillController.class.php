<?php

/**
 *	手机APP  抢拍接口
 *
 */

 namespace Api\Controller;

 Class SeckillController extends BaseController {

      //抢拍商品专场
      public function SeckillSpecial(){

	     $field='skspecial_id, skspecial_name, skspecial_starttime, skspecial_endtime, recordid';
		 $where = array(
			'skspecial_isdelete'=> 0,
			'skspecial_isshow'=> 0,
			'skspecial_starttime' => array('LT',time()),
			'skspecial_endtime' => array('GT',time()),
			);
         $seck= M('SeckillSpecial')->field($field)->where($where)->limit(1)->select();
		 foreach ($seck as $k => $v) {
			$seck[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['skspecial_id']);
		 }
		 if(!$seck){
              $return = array('result' => false, 'data' => 目前没有专场, 'code' => 3);
		      $this->ajaxReturn($return); 
         }else{
			  $return = array('result' => true, 'data' => $seck, 'code' => 1);
			  $this->ajaxReturn($return);
		 }

	 }

     //抢拍列表详情
	 public function SeckillGoods($p = 1, $count = 10){

		 $field=array(
				'skgoods_id',
				'skgoods_name',
				'skgoods_sn',
			 	'skgoods_brief',
			 	'skgoods_intro',
			 	'skgoods_marketprice',
			 	'skgoods_killprice',
			 	'skgoods_sellername',
			 	'skgoods_city',
			    'skgoods_size',
                'skgoods_weight',
			    'skgoods_inventory',
				'recordid',
		 );
		 $where = array(
			'skgoods_isdelete'=> 0,
			'skgoods_isshow'=> 0,
			'skgoods_inventory'=> array('GT',0),
			);
         $goods= M('SeckillGoods')->field($field)->order("skgoods_id desc")->limit(($p-1)*$count . ',' . $count)->where($where)->select();
		 foreach ($goods as $k => $v) {
			$goods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['skgoods_id']);
		 }

		 $return = array('result' => true, 'data' => $goods, 'code' => 1);
		 $this->ajaxReturn($return);

	 }

     //抢拍购买
     public function SeckillBuy(){

		 $mid = I('get.mid');
		 $skgoods_id = I('get.goodsid');
		 if(!$mid){
			$return = array('result' => false, 'data' => '缺少用户id', 'code' => 2);
			$this->ajaxReturn($return);
		 }  
		 if(!$skgoods_id){
			$return = array('result' => false, 'data' => '缺少商品ID', 'code' => 2);
			$this->ajaxReturn($return);
		 }
		 //第一步：查询该用户是否已抢到过
		 $field_orderid = 'order_id';
		 $where_order = array('order_uid' => $mid, 'orderinfo_goodsid'=>$skgoods_id);
         $order= M('SeckillOrderInfo')->field($field_orderid)->where($where_order)->find();
         if(!empty($order)){
			$return = array('result' => false, 'data' => '已经抢拍过商品', 'code' => 2);
			$this->ajaxReturn($return);
		 }
         //第二步：查询是否库存足量
		 $where_goods = array('skgoods_id' => $skgoods_id);
         $goods= M('SeckillGoods')->field($field_inventory)->getField('skgoods_inventory');
         if($goods <=0){
			$return = array('result' => false, 'data' => '商品库存不足', 'code' => 2);
			$this->ajaxReturn($return);
		 }
        //第三步：既没抢过，库存也足,则生成订单
         $goods_info = M('SeckillGoods')->find($skgoods_id);
		//echo"<pre>";print_r($goods_info);exit;
			//生成订单号
		 $orderinfo_sn = date('ymdHis').mt_rand(100,999);
		 $order_info_data = array(
				'orderinfo_sn' => $orderinfo_sn,
				'orderinfo_goodsid' => $goods_info['skgoods_id'],
				'orderinfo_uid' => $mid,
				'orderinfo_createtime' =>time(),
				'orderinfo_amount' =>$goods_info['skgoods_killprice'],
				);
		 $order_orderinfoid = M('SeckillOrderInfo')->add($order_info_data);
		 if(!$order_orderinfoid){
			$return = array('result' => false, 'data' => '订单生成失败', 'code' => 2);
			$this->ajaxReturn($return);
		 }
		 $order_data = array(
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
		  $order_data['order_orderinfoid'] =$order_orderinfoid;
		  $flag = M('SeckillOrder')->add($order_data);
		 if(!$flag){
			$return = array('result' => false, 'data' => '订单生成失败', 'code' => 2);
			$this->ajaxReturn($return);
		 }
		 //库存减一
		M('SeckillGoods')->where(array('skgoods_id'=>$skgoods_id))->setDec('skgoods_inventory');

		$return = array('result' => true, 'data' => '订单生成成功', 'code' => 1);
		$this->ajaxReturn($return);

	 }

    //订单列表
    public function SeckillList( $p = 1, $count = 10, $status = 0){

		 $mid = I('get.mid');
         if(!$mid){
		    $return = array('result' => false, 'data' => '缺少用户id', 'code' => 2);
			$this->ajaxReturn($return);
		 }
         
		$order_field = array(
			'a.orderinfo_id',         //商品ID
			'a.orderinfo_sn',         //订单号
			'a.orderinfo_goodsid',    //商品ID
			'a.orderinfo_amount',     //商品金额
			'a.orderinfo_createtime', //创建时间
			'a.orderinfo_paystatus',  //订单状态
			'b.order_goodsname',      //商品名称
			'b.order_goodsrecordid',  //图片ID
		    'b.order_number',         //数量
            'b.order_marketprice',    //商品原价
			);
		if($status == 0){
			$order_where = array(
				'a.orderinfo_uid' => $mid,
				'a.orderinfo_paystatus' => array('EQ', 0)
			);
		}else{
			$order_where = array(
				'a.orderinfo_uid' => $mid,
				'a.orderinfo_paystatus' => array('EQ', 2)
			);
		}
         $order= M('SeckillOrderInfo')->alias('a')->join('LEFT JOIN yishu_seckill_order b on a.orderinfo_id = b.order_orderinfoid')->field($order_field)->limit(($p-1)*$count . ',' . $count)->where($order_where)->select();
		 foreach($order as &$v){
			 $v['thumb'] = $this->getPic($v['order_goodsrecordid']);
		 }     

		 if(!$order){
              $return = array('result' => false, 'data' => '暂无订单', 'code' => 3);
		      $this->ajaxReturn($return); 
         }else{
			  $return = array('result' => true, 'data' => $order, 'code' => 1);
			  $this->ajaxReturn($return);
		 }


	}


	//订单提交确定->修改用户收货地址等资料，修改订单状态
    public function OrderConfirmation(){
     
		 $mid = I('get.mid');    //用户id
         $orderinfo_id = I('get.orderinfo_id');    // 订单id
         $provinceid = I('get.provinceid');        //省份id
         $cityid = I('get.cityid');                //市id
         $districtid = I('get.districtid');        //区id
         $address = I('get.address');              //详细地址
         $reciver = I('get.reciver');              //收货人
		 $mobile = I('get.mobile');                //手机
		 $zip = I('get.zip');                      //邮编

         if(!$mid){
		    $return = array('result' => false, 'data' => '缺少用户id', 'code' => 2);
			$this->ajaxReturn($return);
		 }         
         if(!$orderinfo_id){
		    $return = array('result' => false, 'data' => '缺少订单id', 'code' => 2);
			$this->ajaxReturn($return);
		 }

         $data = array();
         $order_where = array('orderinfo_uid' => $mid , 'orderinfo_id' => $orderinfo_id );
         $user_where = array('mid' => $mid);
         $provinceid_where = array('id' => $provinceid);
		 $cityid_where = array('id' => $cityid);
		 $districtid_where = array('id' => $districtid);
         $data['orderinfo_uname'] = M("member","bsm_","BSM")->where($user_where)->getField('username');
         $data['orderinfo_address_exists'] = 1;
         $data['orderinfo_provincename'] = M("Region")->where($provinceid_where)->getField('name');
         $data['orderinfo_cityname'] = M("Region")->where($cityid_where)->getField('name');
         $data['orderinfo_district'] = M("Region")->where($districtid_where)->getField('name');
         $data['orderinfo_district'] = $address;
         $data['orderinfo_reciver'] = $reciver;
         $data['orderinfo_mobile'] = $mobile;
         $data['orderinfo_zip'] = $zip;
		 $data['orderinfo_status'] = 5;
		 $data['orderinfo_updatetime'] = time();
         $orderconf = M("SeckillOrderInfo")->where($order_where)->save($data);

		 if(!$orderconf){
              $return = array('result' => false, 'data' => '订单确认失败', 'code' => 2);
		      $this->ajaxReturn($return); 
         }else{
			  $return = array('result' => true, 'data' => '订单确认成功', 'code' => 1);
			  $this->ajaxReturn($return);
		 }
	}





   


 }