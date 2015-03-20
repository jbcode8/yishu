<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-16
 * Time: 上午10:24
 */

namespace Seckill\Controller;


use Admin\Controller\AdminController;

class AdminSeckillordersController extends AdminController{
    /*订单列表*/
    public function index(){

		//p($_GET);
        $orderinfo_where=array();
        //开始时间
        $starttime=I('starttime');
        $endtime=I('endtime');
        if(!empty($starttime)&&empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('GT',strtotime($starttime));
        }elseif(empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('LT',strtotime($endtime)+86400);
        }elseif(!empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('BETWEEN',array(strtotime($starttime),strtotime($endtime)+86400));
        }
        $this->starttime=$starttime;
        $this->endtime=$endtime;
        //付款状态
        $paystatus=I('paystatus');
        if($paystatus==1){//未付款
            $orderinfo_where['orderinfo_paystatus']=0;

        }elseif($paystatus==2){//已经付款
            $orderinfo_where['orderinfo_paystatus']=2;
        }
        $this->paystatus=$paystatus;
        //收货状态
        $status=I('status');
        if($status==1){//未确认
            $orderinfo_where['orderinfo_status']=5;
        }elseif($status==2){//已发货
            $orderinfo_where['orderinfo_status']=6;
        }elseif($status==3){//已收货,交易完成
            $orderinfo_where['orderinfo_status']=7;
        }
        $this->status=$status;


		//分页
		$p = I('p',1,'intval');
		//每页显示的数量
        $prePage = 5;
		//分页商品
		$this->orderinfo = M("SeckillOrderInfo")->field($field)->where($orderinfo_where)->page($p . ',' . $prePage)->order('orderinfo_id desc')->select();
        
		//分页商品总数
		$total_num=M("SeckillOrderInfo")->where($orderinfo_where)->count();
        //p($total_num);
		$Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        
        $this->assign('page', $show); // 赋值分页输出

        $this->display();
    }

   //修改发货状态
	    public function ajax_chargesendstatus()
    {
        $orderinfo_id = I("get.id", 0, "intval");
        $orderinfo_sendtime = time();
        if (M("SeckillOrderInfo")->where("orderinfo_id=" . $orderinfo_id)->setField(array("orderinfo_status" => 6, 'orderinfo_sendtime' => $orderinfo_sendtime))) {
            echo 1; //发货成功
            exit;
        } else {
            echo 0;
            exit; //发货失败
        }
    }

    /*编辑*/
    public function edit(){
        $this->category=M("NewsCategory")->find($_GET['category_id']);
        $this->display();
    }
    /*修改*/
    public function update(){

        $category=D('NewsCategory');
        if($category->create()){
            $category->save($_POST)?$this->success("新闻栏目修改成功"):$this->error("新闻栏目修改失败或者没有修改");
        }else{
            $this->error($category->getError());
        }     
    }

} 