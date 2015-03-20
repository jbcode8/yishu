<?php
	/**
	* 鉴定web版个人中心
	* author :zhihui
	* date : 2015-3-11
	*/

	namespace Mobile\Controller;
	use Mobile\Controller\MobileController;

	class JiandingAdminController extends MobileController{
		public function jd_goods(){

			$where= array('user_id'=>$this->auth['mid'],'is_delete'=>0);
			$jd_goods = M('JiandingGoods')->where($where)->order('goods_atime desc')->select();
			foreach($jd_goods as &$goods){
				$goods['comment_num'] = M('JiandingComment')->where('goods_id='.$goods['goods_id'])->count();
			}
		/*	print '<pre>';
			print_r($jd_goods);*/
			$this->assign('jd_goods',$jd_goods);
			$this->display("Jianding:person_jdgoods");	
		}

		public function jd_comment(){
			$where= array('user_id'=>$this->auth['mid'],'is_delete'=>0);
			$jd_goods = M('JiandingGoods')->where($where)->order('goods_atime desc')->select();
			foreach($jd_goods as &$goods){
				$goods['goods_comment'] = M('JiandingComment')->where('goods_id='.$goods['goods_id'])->select();
			}
			/*print '<pre>';
			print_r($jd_goods);*/
			$this->jd_goods = $jd_goods;

			//我发表的评论

			$where = array('user_id'=>$this->auth['mid'],'is_delete'=>0,'is_anonymous'=>0);
			$jd_my_comment = M('JiandingComment')->where($where)->select();

			foreach($jd_my_comment as &$jmc){
				$jmc['goods_info']=M('JiandingGoods')->find($jmc['goods_id']);
			}

		/*	print '<pre>';
			print_r($jd_my_comment);*/
			$this->jd_my_comment = $jd_my_comment;

			$this->display("Jianding:person_jdcomment");
		}
	}