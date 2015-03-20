<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-16
 * Time: 上午10:24
 */
namespace Seckill\Controller;


use Admin\Controller\AdminController;

class AdminSeckillgoodsController extends AdminController{
    //*秒杀商品列表*/
    public function _initialize()
    {
        parent::_initialize();
        //管理员uid
    }

    public function index(){

		//分页
		$p = I('p',1,'intval');
		//每页显示的数量
        $prePage = 20;
		//分页商品
		$this->goods=M("SeckillGoods")->where("skgoods_isdelete=0")->page($p . ',' . $prePage)->order("skgoods_id desc")->select();
		//分页商品总数
		$total_num=M("SeckillGoods")->where("skgoods_isdelete=0")->count();
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
		$this->special = M('SeckillSpecial')->field("skspecial_id,skspecial_name")->where("skspecial_isshow=0")->order("skspecial_id desc")->select();
        $this->display();
    }
    /**插入*/
    public function insert(){
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
	//	p($_POST);
        $goods=D('SeckillGoods');
	if($goods->create()){
	    //添加时间
           // $goods->skgoods_createtime=time();
            $goods->skgoods_sn=MS.date("Ymd").rand(10000,99999);

			$goods->skgoods_adminuid=$_SESSION['admin_auth']['uid'];
			$goods->skgoods_inventory=$_POST['skgoods_quantity'];
            $goods->skgoods_quantity=$_POST['skgoods_quantity'];

            $goods->add()?$this->success("秒杀商品添加成功"):$this->error("秒杀商品添加失败");
        }else{
            $this->error($goods->getError());
        }

        
    }
    /*编辑*/
    public function edit(){
        $this->special = M('SeckillSpecial')->field("skspecial_id,skspecial_name")->where("skspecial_isshow=0")->order("skspecial_id desc")->select();
        $this->goods=M("SeckillGoods")->find($_GET['skgoods_id']);
        $this->display();
    }
    /*修改*/
    public function update(){
	    $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        $goods=D('SeckillGoods');
        if($goods->create()){
            $goods->skgoods_inventory=$_POST['skgoods_quantity'];
            //$goods->skgoods_updatetime=time();
           // $goods->save($_POST)?$this->success("商品修改成功"):$this->error("商品修改失败或者没有修改");
            $goods->save()?$this->success("商品修改成功"):$this->error("商品修改失败或者没有修改");
        }else{
            $this->error($goods->getError());
        }


        
    }
    /*删除*/
    public function delete(){

        if(!IS_GIE) $this->error("你请求的页面不存在");
        //删除数据同时把isshow置为0,把isdelete置为1
        M("SeckillGoods")->where("skgoods_id=".$_GET['skgoods_id'])->setField(array("skgoods_isdelete"=>1,"skgoods_isshow"=>1))?$this->success("删除成功"):$this->error("删除失败");
        
    }
} 
