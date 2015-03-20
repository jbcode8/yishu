<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午5:45
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminUserFeedbackController extends AdminController{
    /*列表*/
    public function index(){
		//分页
		$p = I('p',1,'intval');
		//每页显示的数量
        $prePage = 15;
		//分页商品
		$this->feedback=M("UserFeedback")->where("feedback_isdelete=0")->page($p . ',' . $prePage)->order("feedback_time desc")->select();
		//分页商品总数
		$total_num=M("UserFeedback")->where("feedback_isdelete=0")->count();
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

    /*编辑*/
    public function edit(){
        $this->feedbacks=M("UserFeedback")->find($_GET['feedback_id']);
        //p($this->feedbacks);
        $this->display();
    }
    /*修改*/
    public function update(){

        $feedbacks=D('UserFeedback');
        if($feedbacks->create()){
            $feedbacks->save($_POST)?$this->success("商家信息修改成功"):$this->error("商家信息修改失败或者没有修改");
        }else{
            $this->error($feedbacks->getError());
        }     
    }
    /*删除*/
    public function delete(){

        if(!IS_GIE) $this->error("你请求的页面不存在");
        //删除数据同时把isshow置为0,把isdelete置为1
        M("UserFeedback")->where("feedback_id=".$_GET['feedback_id'])->setField(array("feedback_isdelete"=>1,"feedback_isshow"=>1))?$this->success("删除成功"):$this->error("删除失败");
        
    }


} 