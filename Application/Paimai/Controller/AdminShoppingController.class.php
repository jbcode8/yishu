<?php

namespace Paimai\Controller;
use Admin\Controller\AdminController;

Class AdminShoppingController extends AdminController {
	public function index() {
		//接收订单号

		$orderinfo_id = I('get.id', 0, 'intval');
		if($orderinfo_id == 0){
			echo "改订单不存在！";die;
		}
		//查询出数据
		$this->list_info = M('PaimaiOrderInfo')->where(array('orderinfo_id'=>$orderinfo_id))->find();
		$this->list_order = M('PaimaiOrder')->where(array('order_orderinfoid'=>$orderinfo_id))->find();

		$this->display();
	}
}
