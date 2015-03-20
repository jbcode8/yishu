<?php
	/**
	*	admin 后台admin商品管理Controller
	*  	AdminGoodsManageController.class.php
	*	author:zhihui
	*   date:2015-3-3
	*/
	namespace Jianding\Controller;
	use Jianding\Controller\AdminJiandingController;

	class AdminGoodsManageController extends AdminJiandingController{

		/**
		* 后台商品管理的首页
		* 数据提取在模板中定义
		*/
		public function mindex(){
			$this->display('Admin:manage_index');
		}

		/**
		*  获取要管理的鉴定的商品
		*  @method getConfirmGoods  获取鉴定未确认的商品
		*  @return  array  返回商品信息的数组
		*/
		public function getConfirmGoods(){
			//获取page  and rows
			$page = I('post.page')?I('post.page'):1;
			$rows = I('post.rows')?I('post.rows'):10;
			$where = array('is_delete'=>0,'status'=>0);
			$order = 'goods_atime desc';
			$total_rows = M('JiandingGoods')->where($where)->count();
			$goods = M('JiandingGoods')->where($where)
					->order($order)
					->limit($rows*($page-1),$rows)->select();
			foreach($goods as &$g){
				$user_info = D('Members')->find($g['user_id']);
				$g['username'] = $user_info['username']?$user_info['username']:'匿名';
				$g['goods_atime']=date('Y-m-d H:i',$g['goods_atime']);
				$url = U('/Jianding/1-1-'.$g['goods_id'].'-1-1');
				$confirm_url = U('Jianding/AdminGoodsManage/jdConfirm',array('goods_id'=>$g['goods_id'],'type'=>1));
				$refuse_url = U('Jianding/AdminGoodsManage/jdConfirm',array('goods_id'=>$g['goods_id'],'type'=>2));
				$g['handle']="<a href='javascript:void(0)' target='_self' onclick='if(confirm(\"通过后将会在前台展示,确定吗?\"))location.href=
					\"{$confirm_url}\"'>通过</a> | <a href='javascript:void(0)' target='_self' onclick='if(confirm(\"选择不通过商品将不会在前台展示,确定吗?\"))location.href=
					\"{$refuse_url}\"'>不通过</a> | <a href='{$url}' target='_top'>预览</a>";
			}

			$result['total'] = $total_rows;
			$result['rows'] = $goods;
			echo json_encode($result);
		}
		/**
		*  商品鉴定确认入口
		*  @param   $_GET['type'] =>1  则为通过   =>2 则为不通过
		*/
		public function jdConfirm(){
			$type = I('get.type');
			$goods_id = I('get.goods_id');
			if(empty($type)||empty($goods_id)){
				echo '数据错误';
			}

			switch($type){
				case 1:
					$flag = M('JiandingGoods')->where('goods_id='.$goods_id)->setField(array('status'=>$type,'confirm_time'=>time()));
					break;
				case 2:
					$flag = M('JiandingGoods')->where('goods_id='.$goods_id)->setField(array('status'=>$type,'confirm_time'=>time()));
					break;
				default:
					echo '数据错误';exit;

			}
			if($flag){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}

		/***
		*  提取管理员已对鉴定通过或不通过操作后的商品
		*  @param $_GET['type'] =>1 验证通过的  $_GET['type']  =>2  验证未通过的商品
		*  @return  返回满足条件的商品
		*/
		public function hasConfirmGoods(){
			//获取get方式传递过来的type
			$type = I('get.type');
			//获取page  and rows
			$page = I('post.page')?I('post.page'):1;
			$rows = I('post.rows')?I('post.rows'):10;
			$where = array('is_delete'=>0,'status'=>$type);
			$order = 'goods_atime desc';
			$total_rows = M('JiandingGoods')->where($where)->count();
			$goods = M('JiandingGoods')->where($where)
					->order($order)
					->limit($rows*($page-1),$rows)->select();
			foreach($goods as &$g){
				$user_info = D('Members')->find($g['user_id']);
				$g['username'] = $user_info['username']?$user_info['username']:'匿名';
				$g['goods_atime']=date('Y-m-d H:i',$g['goods_atime']);
				$url = U('/Jianding/1-1-'.$g['goods_id'].'-1-1');
				$confirm_url = U('Jianding/AdminGoodsManage/jdConfirm',array('goods_id'=>$g['goods_id'],'type'=>1));
				$refuse_url = U('Jianding/AdminGoodsManage/jdConfirm',array('goods_id'=>$g['goods_id'],'type'=>2));
				$delete_url = U('Jianding/AdminGoodsManage/jdDelete',array('goods_id'=>$g['goods_id']));
				$g['handle']="<a href='javascript:void(0)' target='_self' onclick='if(confirm(\"删除后前后台都不可见,慎重,确定吗?\"))location.href=
					\"{$delete_url}\"'>删除</a> | <a href='javascript:void(0)' target='_self' onclick='if(confirm(\"通过后将会在前台展示,确定吗?\"))location.href=
					\"{$confirm_url}\"'>通过</a> | <a href='javascript:void(0)' target='_self' onclick='if(confirm(\"选择不通过商品将不会在前台展示,确定吗?\"))location.href=
					\"{$refuse_url}\"'>不通过</a> | <a href='{$url}' target='_top'>预览</a>";
			}

			$result['total'] = $total_rows;
			$result['rows'] = $goods;
			echo json_encode($result);
		}


		/**
		*  鉴定商品删除操作
		* @param   $_GET['goods_id']  要删除的商品
		*/
		public function jdDelete(){
			$goods_id = I('get.goods_id');
			$flag = M('JiandingGoods')->where('goods_id='.$goods_id)->setField('is_delete',1);
			if($flag){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}





	}