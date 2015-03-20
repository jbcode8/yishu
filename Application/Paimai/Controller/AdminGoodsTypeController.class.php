<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-6
 * Time: 下午5:38
 */

namespace Paimai\Controller;
use Admin\Controller\AdminController;

class AdminGoodsTypeController extends AdminController{
    public function _initialize() {
        parent::_initialize();

    }
    public function index(){
        $this->lists=M('PaimaiGoodsType')->where("goodstype_isdelete=0")->select();
        //p($this->lists);
        $this->display();
    }
    public function add(){
        $this->display();
    }
    public function insert(){
        //p($_POST);
        $goodstype=D("PaimaiGoodsType");
        if( $goodstype->create()){
            $goodstype->add();
            $this->success("类型添加成功");
        }else{
            $this->error($goodstype->getError());
        }
        //$this->display();
    }
    public function edit(){
        $goodstype_id=I('goodstype_id');
        //p($cat_id);
        $this->lists=M('PaimaiGoodsType')->find($goodstype_id);
        //p($this->lists);
        $this->display();
    }
    public function update(){
        //$cat_id=I('cat_id');
        //p($_POST);;
        $goodstype=D('PaimaiGoodsType');
        if($goodstype->create()){
            $goodstype->save()?$this->success("类型更新成功"):$this->error("你没有更新内容或类型更新失败");
        }else{
            $this->error($goodstype->getError());
        }
    }
    public function delete(){
        //p($_GET);
        $goodstype=M('PaimaiGoodsType');
        $goodstype_id=I("get.goodstype_id","0","intval");
        if($goodstype->where("goodstype_id=".$goodstype_id)->save(array("goodstype_enable"=>0,"goodstype_isdelete"=>1))){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }
} 