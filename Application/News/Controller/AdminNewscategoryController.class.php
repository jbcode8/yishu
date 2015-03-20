<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午5:45
 */

namespace News\Controller;


use Admin\Controller\AdminController;

class AdminNewscategoryController extends AdminController{
    /*新闻列表*/
    public function index(){

        $where=array(
            "category_isdelete"=>0,
            );
        $this->category=M('NewsCategory')->field('*')->order("category_id asc")->where($where)->select();

        $this->display();
    }
    /*增加*/
    public function add(){
        $this->display();
    }
    /**插入*/
    public function insert(){

        $category=D('NewsCategory');
        if($category->create()){
            //添加时间
            $category->category_createtime=time();
            $category->add()?$this->success("新闻栏目添加成功"):$this->error("新闻栏目添加失败");
        }else{
            $this->error($category->getError());
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
    /*删除*/
    public function delete(){

        if(!IS_GIE) $this->error("你请求的页面不存在");
        //删除数据同时把isshow置为0,把isdelete置为1
        M("NewsCategory")->where("category_id=".$_GET['category_id'])->setField(array("category_isdelete"=>1,"category_isshow"=>1))?$this->success("删除成功"):$this->error("删除失败");
        
    }
} 