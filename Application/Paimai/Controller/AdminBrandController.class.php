<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午5:45
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminBrandController extends AdminController{
    public function index(){
        /*Vendor('Log.Mylog');
        $MyLOG = new \Mylog;
        $MyLOG->write("123");*/
        $field=array(
            "*",
            "case
                when brand_isshow=1 then '是'
                else '否' end"
            =>"brand_isshow"
        );
        $this->lists=M('PaimaiBrand')->field($field)->order("brand_id desc")->where("brand_isdelete=0")->select();
        //p($this->lists);
        $this->display();
    }
    public function add(){
        //$this->
        $this->display();
    }
    public function insert(){
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        //p($_POST);
        $brand=D('PaimaiBrand');
        if($brand->create()){
            $brand->add()?$this->success("品牌填加成功"):$this->error("品牌填加失败");
        }else{
            $this->error($brand->getError());
        }
    }
    public function edit(){
        $this->brand=M("PaimaiBrand")->find($_GET['brand_id']);
        $this->display();
    }
    public function update(){
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        $brand=D('PaimaiBrand');
        //p($brand);
        //p($_POST);
        if($brand->create()){
            $brand->save($_POST)?$this->success("品牌修改成功"):$this->error("品牌修改失败或者没有修改");
        }else{
            $this->error($brand->getError());
        }
    }
    public function delete(){
        //p($_GET);
        if(!IS_GIE) $this->error("你请求的页面不存在");
        //删除数据同时把isshow置为0,把isdelete置为1
        M("PaimaiBrand")->where("brand_id=".$_GET['brand_id'])->setField(array("brand_isdelete"=>1,"brand_isshow"=>0))?$this->success("删除成功"):$this->error("删除失败");
    }
} 