<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午5:45
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminRecruiterController extends AdminController{
    /*列表*/
    public function index(){
        $where=array(
            "recruiter_isshow"=>0,
            "recruiter_isdelete"=>0,
            );
        $this->recr=M('PaimaiRecruiter')->field('*')->order("recruiter_id desc")->where($where)->select();

		//p($this->r);
 
        $this->display();
    }
    /*增加*/
    public function add(){
        //$this->
        $this->display();
    }
    /**插入*/
    public function insert(){


        $recruiter=D('PaimaiRecruiter');
        if($recruiter->create()){
            //添加时间
            $recruiter->recruiter_createtime=time();
            $recruiter->add()?$this->success("征集人员添加成功"):$this->error("征集人员添加失败");
        }else{
            $this->error($recruiter->getError());
        }

        
    }
    /*编辑*/
    public function edit(){
        $this->recruiter=M("PaimaiRecruiter")->find($_GET['recruiter_id']);
        $this->display();
    }
    /*修改*/
    public function update(){

        $recruiter=D('PaimaiRecruiter');
        if($recruiter->create()){
            $recruiter->save($_POST)?$this->success("征集人员信息修改成功"):$this->error("征集人员信息修改失败或者没有修改");
        }else{
            $this->error($recruiter->getError());
        }


        
    }
    /*删除*/
    public function delete(){

        if(!IS_GIE) $this->error("你请求的页面不存在");
        //删除数据同时把isshow置为0,把isdelete置为1
        M("PaimaiRecruiter")->where("recruiter_id=".$_GET['recruiter_id'])->setField(array("recruiter_isdelete"=>1,"recruiter_isshow"=>1))?$this->success("删除成功"):$this->error("删除失败");
        
    }
} 