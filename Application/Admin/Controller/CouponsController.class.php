<?php

namespace Admin\Controller;

Class CouponsController extends AdminController {
	public function index() {
		//修改过期状态 还有问题：需要不需要过期时间
		$where = array(
			'endtime' => array('LT', time()),//判断优惠券是否过期
			'type' => 2
			);
		$temp = M('coupons_code', 'yishu_', 'yishu')->where($where)->select();
        if($temp) {
            foreach($temp as $v){
                M('coupons_code', 'yishu_', 'yishu')->where(array('id'=>$v['id']))->setField('status', 0);
            }
        }
        //分配优惠码模板
		$this->list = M('coupons_code')->select();
		$this->display();
	}
	/**
	 *	添加优惠券
	 */
	public function add() {
		//优惠券码生成
		$this->coupons_code = generate_promotion_code('1', '', '6', 'B');
		//当前时间戳
		$this->creatime = date('Y-m-d H:i:s', time());
		$this->display();
	}

	/**
	 *	根据分类生成不同优惠券,已经测试过
	 */
	public function create_coupons_code(){
		//提交方式不正确
		if(!IS_AJAX){
			return false;
		}
		//接收优惠券类别
		$type = I('type');
		if(!$type){
			$this->ajaxReturn(array('status'=>0), 'JSON');
		} 
		$this->ajaxReturn(array('status' => 1, 'code' => generate_promotion_code('1', '', '6', $type)), 'JSON');
	}

	public function addHandle() {
		//接收数据
		$code = I('post.code');
		$creatime = strtotime(I('post.creatime'));
		$day = I('post.day');
		$endtime = $creatime + $day*3600*24;
		$amount = I('post.amount',0,'intval');
		$descrition = I('post.description');
		$type = I('post.type');
		//print_r(I('post.'));die;
		//组织插入数据
		$data = array(
			'code' => $code,
			'creatime' => $creatime,
			'endtime' => $endtime,
			'amount' => $amount,
			'description' => $descrition,
			'type' => $type,
			'status' => '0'
			);

		//插入数据库
		if(M('coupons_code')->add($data)){
			$this->success('添加成功!');
		} else {
			$this->error('添加失败!');
		}
		//echo M('coupons_code','yishu_','yishu')->getLastSql();
	}
	//优惠券开启/关闭
	public function start(){
		$id = I('post.id');
		$coupons = I('post.status');
		if($coupons == 1){
			$status = 0;
		} else {
			$status = 1;
		}
		if(M('coupons_code','yishu_','yishu')->where(array('id'=>$id))->setField('status',$status)){
			$this->ajaxReturn(array('status'=>1),'JSON');
		} else {
			$this->ajaxReturn(array('status'=>0),'JSON');
		}
	}
	//优惠券编辑
	public function edit() {
		$id = I('get.id');
		$this->coupons = M('coupons_code')->where(array('id'=>$id))->find();
		$this->display();
	}
	//优惠券编辑保存
	public function editHandler() {
		//接收数据
		$id = I('post.id');
		$code = I('post.code');
		$creatime = strtotime(I('post.creatime'));
		$day = I('post.day');
		$endtime = $creatime + $day*3600*24;
		$amount = I('post.amount',0,'intval');
		$descrition = I('post.description');
		$type = I('post.type');
		//组织更新数据
		$data = array(
			'code' => $code,
			'creatime' => $creatime,
			'endtime' => $endtime,
			'amount' => $amount,
			'description' => $descrition,
			'type' => $type,
			'status' => '0'
			);

		//更新数据库
		$temp = M('coupons_code')->where(array('id'=>$id))->save($data);
		if($temp){
			$this->success('更新成功!');
		} else {
			$this->error('更新失败!');
		}
	}

	//删除
	public function del() {
		$id = I('get.id');
		if(M('coupons_code')->where(array('id'=>$id))->delete()){
			$this->success('删除成功!');
		} else {
			$this->error('删除失败!');
		}
		
	}


	//获取注册返现金额(未使用)
	public function return_amount($id = '') {
		//查询是否过期 ，如果过期，修改状态
		//查询条件
		//$uid = '1737';
		$where = array(
			'uid' => $uid,
			'endtime' => array('LT', time()),//判断返现金额是否过期
			'type' => '1'//1为注册返现
			);
		$temp = M('coupons')->where($where)->select();
		if($temp) {
            foreach($temp as $v){
                M('coupons')->where(array('id'=>$v['id']))->setField('status', 0);
            }
        }
		//查询注册返现金额条件
		$where = array(
			'uid' => $uid,
			'type' => 1,//1为注册返现
			'status' => 1//1为未使用
			);
		if($temp = M('coupons')->field('amount')->where($where)->find()){
			return $temp['amount'];
		} else {
			return 0;
		}
	}

	//注册返现修改状态(未使用)
	public function change_status_false($uid = '') {
		$where = array(
			'uid' => $uid,
			'type' => 1,//1为注册返现
			'status' => 1//1为未使用
			);
		if($temp = M('coupons')->where($where)->setField('status', '2')){
			return 1;//成功
		} else {
			return 0;//失败
		}
	}

	/**
	 *	优惠券分类
	 *
	 */

	public function cate() {
		//接收参数
		$cate = I('post.value');
		if(empty($cate)) $this->ajaxReturn(array('status' => '0'), 'JSON');
		//定义一个数组
		$temp = array(
			'1' => 'A',
			'2' => 'B',
			'3' => 'C',
			'4' => 'D',
			'5' => 'E',
			'6' => 'F',
			'7' => 'G',
			'8' => 'H',
			'9' => 'I',
			'10' => 'J',
			'11' => 'K',
			'12' => 'L'
			);
		//生成优惠码
		$coupons_code = generate_promotion_code('1', '', '6', $temp[$cate]);

		$this->ajaxReturn(array('status' => '1', 'code' => $coupons_code), 'JSON');
	}
	
}