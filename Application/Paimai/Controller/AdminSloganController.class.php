<?php
/**
 * Created by PhpStorm.
 * User: LUXIAOFEI
 * Date: 14-8-8
 * Time: 下午4:26
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminSloganController extends AdminController{
    public function index(){
        $field=array(
            "*",
            "case
                when slogan_isshow=1 then '是'
                else '否' end"
            =>"slogan_isshow"
        );
        $this->lists=M('PaimaiSlogan')->field($field)->where("slogan_isdelete=0")->order("slogan_id desc")->select();
        $this->display();
    }
    public function add(){
        $this->display();
    }
    public function insert(){
        $slogan=D('PaimaiSlogan');
        if($slogan->create()){
            $slogan->add()?$this->success("口号填加成功"):$this->error("口号填加失败");
        }else{
            $this->error($slogan->getError());
        }
    }
    public function edit(){
        $this->slogan=M("PaimaiSlogan")->find($_GET['slogan_id']);
        $this->display();
    }
    public function update(){
        $slogan=D('PaimaiSlogan');
        if($slogan->create()){
            $slogan->save()?$this->success("口号修改成功"):$this->error("口号修改失败或者没有修改内容");
        }else{
            $this->error($slogan->getError());
        }
    }
    public function delete(){
        if(!IS_GIE) $this->error("你请求的页面不存在");
		$slogan_id=I('slogan_id',0,'intval');
		//删除商品口号对照表中的口号记录
		M('PaimaiGoodsSlogan')->where('gs_sloganid='.$slogan_id)->delete();
        //把isdelete置为1,同时把isshow置为0
        M("PaimaiSlogan")->where("slogan_id=".$slogan_id)->setField(array('slogan_isdelete'=>1,"slogan_isshow"=>0))?$this->success("删除成功"):$this->error("删除失败");
    }
} 