<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-16
 * Time: 上午10:24
 */

namespace Seckill\Controller;


use Admin\Controller\AdminController;

class AdminSeckillspecialController extends AdminController{
    /*秒杀专场列表*/
    public function index(){

		//分页
		$p = I('p',1,'intval');
		//每页显示的数量
        $prePage = 15;
		//分页商品
		$this->skspecial=M("SeckillSpecial")->where("skspecial_isdelete=0")->page($p . ',' . $prePage)->order("skspecial_id desc")->select();
		//分页商品总数
		$total_num=M("SeckillSpecial")->where("skspecial_isdelete=0")->count();
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
    /*增加*/
    public function add(){
        $this->display();
    }
    /**插入*/
    public function insert(){
		$param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);

        $special=D('SeckillSpecial');
        if($special->create()){
            //添加时间
			//p($special->skspecial_starttime);
           // $special->skspecial_createtime=time();
            $special->add()?$this->success("专场添加成功"):$this->error("专场添加失败");
        }else{
            $this->error($special->getError());
        }

        
    }
    /*编辑*/
    public function edit(){
        $this->special=M("SeckillSpecial")->find($_GET['skspecial_id']);
        $this->display();
    }
    /*修改*/
    public function update(){
		$param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);

        $special=D('SeckillSpecial');
        if($special->create()){
            $special->save()?$this->success("专场修改成功"):$this->error("专场修改失败或者没有修改");
        }else{
            $this->error($special->getError());
        }


        
    }
    /*删除*/
    public function delete(){

        if(!IS_GIE) $this->error("你请求的页面不存在");
        //删除数据同时把isshow置为0,把isdelete置为1
        M("SeckillSpecial")->where("skspecial_id=".$_GET['skspecial_id'])->setField(array("skspecial_isdelete"=>1,"skspecial_isshow"=>1))?$this->success("删除成功"):$this->error("删除失败");
        
    }
} 