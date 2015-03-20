<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-7
 * Time: 上午11:33
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminAttributeController extends AdminController{
    public function _initialize() {
        parent::_initialize();

    }
    public function index(){
        //exit("22");
        $field=array(
            "*",
            "case
                when attr_index=0 then '不检索'
                when attr_index=1 then '关键字检索'
                else '范围检索'
            end"=>"attr_index",
            "case
               when attr_isshow=1 then '可用'
               else '不可用'
            end"=>'attr_isshow',
            //得到这个类型的名字
            '(select yishu_paimai_goods_type.goodstype_name from yishu_paimai_goods_type where yishu_paimai_goods_type.goodstype_id=yishu_paimai_attribute.attr_goodstypeid)'=>'goodstype_name'
        );
        $this->lists=M('PaimaiAttribute')->field($field)->where("attr_goodstypeid=".$_GET['goodstype_id'])->select();
        //p($this->lists);
        $this->display();
    }
#插入数据
    public function add(){
        //$this->goodstype=M('PaimaiGoodsType')->where("goodstype_enable=1")->select();
        //调用成员方法得到属性列表
        $this->goodstype=$this->getGoodsType();
        $this->display();
    }
    public function insert(){
        //p($_POST);
        $attribute=D('PaimaiAttribute');
        if($attribute->create()){
            $attribute->add()?$this->success("添加成功"):$this->error("添加失败");
        }else{
            $this->error($attribute->getError());
        }
        //p($_POST);
    }
#编辑数据
    public function edit(){
        //得到商品类型列表
        $this->goodstype=$this->getGoodsType();
        //v($this->goodstype);
        $this->attr=M('PaimaiAttribute')->where("attr_id=".$_GET['attr_id'])->find();
        //p($this->attr);
        $this->display();
    }
    public function update(){
        //p($_POST);
        $attribute=D('PaimaiAttribute');
        if($attribute->create()){
            $attribute->save()?$this->success("修改成功"):$this->error("你没有修改内容或修改失败");
        }else{
            $this->error($attribute->getError());
        }
    }
#删除数据
    public function delete(){
		$attr_id=I("attr_id",0,"intval");
		$attribute=M('PaimaiAttribute');
		//删除分类 属性对照表
		M("PaimaiCatAttr")->where("cat_attr_catid=".$attr_id)->delete();
		//删除商品 属性对照表中的数据
		M("PaimaiGoodsattr")->where("goodsattr_attrid=".$attr_id)->delete();
		if($attribute->delete($_GET['attr_id'])){
            $this->success("删除成功");
        }else{
            $this->error("删除失败");
        }
    }
#一些方法
    /*
     * 得到商品类型,其它控制器也有调用
     */
    public function getGoodsType(){
        return M('PaimaiGoodsType')->where("goodstype_enable=1")->select();
    }
    public function getAttrByGoodstype($goodstype_id){
        return  M('PaimaiAttribute')->where("attr_isshow=1 and attr_goodstypeid=$goodstype_id")->select();
    }
} 