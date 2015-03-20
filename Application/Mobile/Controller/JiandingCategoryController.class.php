<?php
	namespace Mobile\Controller;
	use Mobile\Controller\MobileController;
	use Jianding\Service\CategoryService;

	class JiandingCategoryController extends MobileController{
		public function gList(){
			$cat_id =  I('get.cat_id');
			$cs = new CategoryService;
			$this->assign('cats',$cs->getAllCategory());
			//获取所有的鉴定品
			$where= array('status'=>1,'is_delete'=>0,'cat_id'=>$cat_id);
			$jd_goods = M('JiandingGoods')->where($where)->order('confirm_time desc')->select();
			foreach($jd_goods as &$goods){
				$goods['comment_num'] = M('JiandingComment')->where('goods_id='.$goods['goods_id'])->count();
			}
			$this->assign('jd_goods',$jd_goods);
			$this->display("Jianding:m_index");
		}
	}