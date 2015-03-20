<?php
	/**
	* 鉴定移动端入口
	* MobileController.class.php
	* date: 2015-3-4
	* author:zhihui
	*/

	namespace Mobile\Controller;
	use Mobile\Controller\MobileController;
	use Jianding\Service\CategoryService;

	class JiandingIndexController extends MobileController{
		/**
		* 鉴定首页入口
		*/
		public function index(){
			//实例化目录的数据接口
			$cs = new CategoryService;
			$this->assign('cats',$cs->getAllCategory());
			//获取所有的鉴定品
			$where= array('status'=>1,'is_delete'=>0);
			$jd_goods = M('JiandingGoods')->where($where)->order('confirm_time desc')->select();
			foreach($jd_goods as &$goods){
				$goods['comment_num'] = M('JiandingComment')->where('goods_id='.$goods['goods_id'])->count();
			}
			$this->assign('jd_goods',$jd_goods);
			$this->display("Jianding:m_index");
		}

	}
