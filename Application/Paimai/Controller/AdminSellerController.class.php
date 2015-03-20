<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午5:45
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminSellerController extends AdminController{
    /*列表*/
    public function index(){
        //exit("e");
        $where=array(
            "seller_isshow"=>0,
            "seller_isdelete"=>0,
            );
        $this->sell=M('PaimaiSeller')->field('*')->order("seller_id desc")->where($where)->select();
 
        $this->display();
    }
    /*增加*/
    public function add(){
        //$this->
        $this->display();
    }
    /**插入*/
    public function insert(){


        $seller=D('PaimaiSeller');
        if($seller->create()){
            //添加时间
            $seller->seller_createtime=time();
            $seller->add()?$this->success("商家添加成功"):$this->error("商家添加失败");
        }else{
            $this->error($seller->getError());
        }

        
    }
    /*编辑*/
    public function edit(){
        $this->seller=M("PaimaiSeller")->find($_GET['seller_id']);
        //p($this->seller);
        $this->display();
    }
    /*修改*/
    public function update(){

        $seller=D('PaimaiSeller');
        if($seller->create()){
            $seller->save($_POST)?$this->success("商家信息修改成功"):$this->error("商家信息修改失败或者没有修改");
        }else{
            $this->error($seller->getError());
        }


        
    }
    /*删除*/
    public function delete(){

        if(!IS_GIE) $this->error("你请求的页面不存在");
        //删除数据同时把isshow置为0,把isdelete置为1
        M("PaimaiSeller")->where("seller_id=".$_GET['seller_id'])->setField(array("seller_isdelete"=>1,"seller_isshow"=>1))?$this->success("删除成功"):$this->error("删除失败");
        
    }
} 