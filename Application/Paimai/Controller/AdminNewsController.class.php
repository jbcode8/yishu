<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午1:51
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminNewsController extends AdminController
{
    public function _initialize()
    {
        parent::_initialize();

    }

    public function index()
    {
		$cate = M('paimai_cate')->order('id')->select();
		$this->cates = cates($cate);
        $this->display();
    }
	public function add(){
		$cate = M('paimai_cate')->select();
		$this->cates = cates($cate);
		if(IS_POST){
			if(! I('post.name')){
				$this->error("名称必填");
			}
			$cate = M('paimai_cate')->add($_POST);
			if($cate){
				$this->success("写入成功");
			}else{
				$this->error("写入失败");
			}
		}
		$this->display();
	}
	public function edit(){
		$id = I('get.id');
		$cate = M('paimai_cate')->select();
		$this->cates = cates($cate);
		$this->pid = M('paimai_cate')->where(array('id'=>$id))->getField('pid');
		$this->vo = M('paimai_cate')->where(array('id'=>$id))->find();
		if(IS_POST){
			if(! I('post.name')){
				$this->error("名称必填");
			}
			$cate = M('paimai_cate')->where(array('id'=>I('post.id')))->save($_POST);
			if($cate){
				$this->success("修改成功");
			}else{
				$this->error("内容没有变化");
			}
		}
		$this->display();
	}
	public function del(){
		$id = I('get.id');
		if(M('paimai_news')->where(array('cid'=>$id))->select()){
			$this->error("请先删除核栏目下的新闻");
		}
		if(M('paimai_cate')->where(array('pid'=>$id))->select()){
			$this->error("请先删除子栏目");
		}else{
			if(M('paimai_cate')->delete($id)){
				$this->success("删除成功");
			}else{
				$this->error("删除失败");
			}
		}
	}
    public function lists()
    {
		$this->news = M('paimai_news')->field('title,id,cid')->select();
		$this->display();
    }
	public function newsadd(){
		$cate = M('paimai_cate')->order('id')->select();
		$this->cates = cates($cate);
		if(IS_POST){
			$data = array(
				"cid" => I('post.cid'),
				"title" => I('post.title'),
				"keyword" => I('post.keyword'),
				"dis" => I('post.dis'),
				"content" => I('post.content'),
				'time' => time(),
				'uid' => session('admin_auth')['uid']
			);
			if(! I('post.cid')){
				$this->error("所属栏目必选");
			}
			if(! I('post.title')){
				$this->error("请填写名称");
			}if(! I('post.content')){
				$this->error("请填写内容");
			}
			if(M('paimai_news')->add($data)){
				$this->success("添加成功",U('Paimai/AdminNews/lists'));
			}else{
				$this->error("添加失败");
			}
			die;
		}
		$this->display();
	}
	public function newsedit(){
		$id = I('get.id');
		$cate = M('paimai_cate')->select();
		$this->cates = cates($cate);
		$this->pid = M('paimai_news')->where(array('id'=>$id))->getField('cid');
		$this->vo = M('paimai_news')->where(array('id'=>$id))->find();
		if(IS_POST){
			$data = array(
				"cid" => I('post.cid'),
				"title" => I('post.title'),
				"keyword" => I('post.keyword'),
				"dis" => I('post.dis'),
				"content" => I('post.content'),
				'time' => time(),
				'uid' => session('admin_auth')['uid']
			);
			if(! I('post.cid')){
				$this->error("所属栏目必选");
			}
			if(! I('post.title')){
				$this->error("请填写名称");
			}if(! I('post.content')){
				$this->error("请填写内容");
			}
			if(M('paimai_news')->where(array('id'=>I('post.id')))->save($data)){
				$this->success("修改成功",U('Paimai/AdminNews/lists'));
			}else{
				$this->error("修改失败");
			}
			die;
		}
		$this->display();
	}
	public function newsdel(){
		$id = I('get.id');
		if(M('paimai_news')->delete($id)){
			$this->success("删除成功",U('Paimai/AdminNews/lists'));
		}else{
			$this->error("删除失败");
		}
	}
	public function message(){
		$this->message = M('paimai_message')->select();
		$this->display();
	}
}