<?php

	namespace Jishou\Controller;
	use Admin\Controller\AdminController;
	use Think\Page;

	class AdminGoodsConfirmController extends AdminController{


		//商品确认的首页
		public function cindex(){
			$allnum = M('JishouGoods')->where(array('status'=>3,'is_delete'=>0))->count();
			$pagenum = 10;
			$Page = new Page($allnum,$pagenum);
			$confirmGoods = D('JishouGoods')->relation(true)->field('goods_name,goods_id,user_id,user_id,goods_atime') 
				->where(array('status'=>3,'is_delete'=>0))
                ->order('goods_atime desc')
				->limit($Page->firstRow.','.$Page->listRows)->select();
			
            $Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页'); 
			$this->assign('confirm_data',$confirmGoods);
			$this->assign('page_info',$Page->show()); //分页信息
			$this->display('GoodsConfirm/cindex');
		}


		public function userShow($user_id){
			$mem_info = D('Member')->where(array('mid'=>$user_id))->find();
			$this->assign('mem_info',$mem_info);	
			$this->display('GoodsConfirm/userShow');

		}


		//商品删除的按钮  把商品字段的is_delete 设为1
		public function del($goods_id){
			$m = M('JishouGoods');
			$flag = $m->where(array('goods_id'=>$goods_id))
					->limit(1)
					->save(array('is_delete'=>1));
			if($flag){
				$this->success('删除成功');
			}else{
				$this->error('操作失败');
			}
		}

		//后台商品查封操作  商品状态更改为 status=>1
		public function deny($goods_id){
			$m = M('JishouGoods');
			$flag = $m->where(array('goods_id'=>$goods_id))
					->limit(1)
					->save(array('status'=>1));
			if($flag){
				$this->success('商品已查封');
			}else{
				$this->error('操作失败');
			}
		}

		//商品删除的按钮  把商品字段的status 设为2
		public function confirm($goods_id){
			$m = M('JishouGoods');
			$flag = $m->where(array('goods_id'=>$goods_id))
					->limit(1)
					->save(array('status'=>2));
			if($flag){
				$this->success('商品确认成功');
			}else{
				$this->error('操作失败');
			}
		}

	}
