<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-9
 * Time: 下午12:14
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminSpecialController extends AdminController{
    public function index(){
        //$this->lists=M("PaimaiSpecial")->where("special_isdelete=0")->order("special_id desc")->select();
		
		//分页
		$p = I('p',1,'intval');
		//每页显示的数量
        $prePage = 20;
		$starNum = ($p-1)*$prePage;
		//分页商品
		$this->lists=M("PaimaiSpecial")->where("special_isdelete=0")->limit($starNum, $prePage)->order("special_id desc")->select();
		//分页商品总数
		$total_num=M("PaimaiSpecial")->where("special_isdelete=0")->count();
		$Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        
        $this->assign('page', $show); // 赋值分页输出

       // p($this->lists);
        $this->display();
    }
 #插入数据
    public function add(){
        //$this->region=M("Region")->where("pid=2")->select();
        $this->display();
    }
    public function insert(){

        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        //p($_POST);
        $special=D("PaimaiSpecial");
        if($special->create()){
            $special->add()?$this->success("专场添加成功"):$this->error("专场添加失败");
        }else{
            $this->error($special->getError());
        }
    }
 #编辑数据
    public function edit(){
        $this->special=M("PaimaiSpecial")->where("special_id=".$_GET['special_id'])->find();
        $this->display();
    }
    public function update(){
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        $special=D("PaimaiSpecial");
        if($special->create()){
            $special->where("special_id=".$_POST['special_id'])->save()?$this->success("专场添加成功"):$this->error("专场添加失败");
        }else{
            $this->error($special->getError());
        }
    }
 #删除数据
    public function delete(){
        if(!IS_GET) $this->error("你请求的页面不存在");
		$special_id=I("special_id",0,"intval");
		//把这个专场下的商品的专场id置为0
		M("PaimaiGoods")->where("goods_specialid=".$special_id)->setField("goods_specialid","0");
        //把isdelete置为1,把isshow置为0
        $result = M("PaimaiSpecial")->where("special_id=".$_GET['special_id'])->setField(array("special_isdelete"=>1,"special_isshow"=>0));
		if($result){
			$this->success("删除成功");
		}else{
			$this->error("删除失败");
		}
    }
 #一些共用的方法
   /**
     * 排序
     */
    public function listorder(){
        $model = M('PaimaiSpecial');
        $pk = $model->getPk();
        //p($pk);
        foreach ($_POST['listorder'] as $id => $v) {
            $condition = array($pk => $id);
            $model->where($condition)->setField('special_order', $v);
        }
        $this->success('更新排序成功');
    }
} 