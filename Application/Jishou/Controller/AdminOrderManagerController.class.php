<?php

	namespace Jishou\Controller;
	use Admin\Controller\AdminController;

	class AdminOrderManagerController extends AdminController{
		public function mindex(){
			$this->display('Admin/order_index');
		}


		public function getPayOrder(){
			$page_num = I('post.rows','10','intval');
			$offset = I('post.page')?(I('post.page','0','intval')-1)*$page_num:0;
			$field = array(
				'yishu_jishou_order_info.*',
				'yishu_jishou_order_goods.*',
				);
			$all_num = M('JishouOrderInfo')->field('order_id')->where(array('pay_status'=>1))->count();
			$order = M()->table('__JISHOU_ORDER_GOODS__ og')
						->join('__JISHOU_ORDER_INFO__ o ON o.order_id=og.order_id')
						->where(array('pay_status'=>1))
						//->field($field)
						->order('atime desc')
						->limit($offset,$page_num)
						->select();
			if(!empty($all_num)){
				foreach($order as &$o){
					$o['atime'] = date('Y-m-d H:s',$o['atime']);
					$o['pay_time'] = date('Y-m-d H:s',$o['pay_time']);
					$o['img'] = $o['img_path'].$o['img_name'];
				}
				$result['total'] = $all_num;
				$result['rows']=$order;
			}else{
				$result=array();
			}

			echo json_encode($result);
		}

		//获取列的扩展信息
		public function getDetailInfo(){
			$sin = M('JishouOrderInfo')->where(array('order_id'=>I('get.order_id')))->find();
			echo <<<end
			<p><span>收货人 : </span> {$sin['recipient']} <span>联系电话 : </span> {$sin['mobilephone']} </p>
			<p><span>收货地址 : </span> {$sin['province']} {$sin['city']}&nbsp;&nbsp;&nbsp;{$sin['detail_address']}<span></p>
			<p><span>物流信息 : </span> {$sin['invoice_sn']} </p>
end;
		}



		public function getNotPayOrder(){
			$page_num = I('post.rows','10','intval');
			$offset = I('post.page')?(I('post.page','0','intval')-1)*$page_num:0;
			$field = array(
				'yishu_jishou_order_info.*',
				'yishu_jishou_order_goods.*',
				);
			$all_num = M('JishouOrderInfo')->field('order_id')->where(array('pay_status'=>0))->count();
			$order = M()->table('__JISHOU_ORDER_GOODS__ og')
						->join('__JISHOU_ORDER_INFO__ o ON o.order_id=og.order_id')
						->where(array('pay_status'=>0))
						//->field($field)
						->order('atime desc')
						->limit($offset,$page_num)
						->select();
			if(!empty($all_num)){
				foreach($order as &$o){
					$o['atime'] = date('Y-m-d H:s',$o['atime']);
					$o['pay_time'] = date('Y-m-d H:s',$o['pay_time']);
					$o['img'] = $o['img_path'].$o['img_name'];
				}
				$result['total'] = $all_num;
				$result['rows']=$order;
			}else{
				$result=array();
			}

			echo json_encode($result);
		}

		public function getFinishOrder(){
			$page_num = I('post.rows','10','intval');
			$offset = I('post.page')?(I('post.page','0','intval')-1)*$page_num:0;
			$field = array(
				'yishu_jishou_order_info.*',
				'yishu_jishou_order_goods.*',
				);
			$all_num = M('JishouOrderInfo')->field('order_id')->where(array('pay_status'=>1,'shipping_status'=>2))->count();
			$order = M()->table('__JISHOU_ORDER_GOODS__ og')
						->join('__JISHOU_ORDER_INFO__ o ON o.order_id=og.order_id')
						->where(array('pay_status'=>1,'shipping_status'=>2))
						//->field($field)
						->order('atime desc')
						->limit($offset,$page_num)
						->select();
			if(!empty($all_num)){
				foreach($order as &$o){
					$o['atime'] = date('Y-m-d H:s',$o['atime']);
					$o['pay_time'] = date('Y-m-d H:s',$o['pay_time']);
					$o['img'] = $o['img_path'].$o['img_name'];
				}
				$result['total'] = $all_num;
				$result['rows']=$order;
			}else{
				$result=array();
			}

			echo json_encode($result);
		}




	}