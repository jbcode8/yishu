<?php
	
	/**
	* 移动端的鉴定商品在展示的controller
	* author ： zhihui
	* date : 2015-3-6
	*/
	namespace Mobile\Controller;
	use Mobile\Controller\MobileController;
	use Jianding\Service\CategoryService;

	class JiandingGoodsController extends MobileController{
		// 商品展示的页面
		public function gIndex(){
			$goods_id = I('get.goods_id');
			$cs = new CategoryService;
			$this->assign('cats',$cs->getAllCategory());

			//获取商品的信息
			$goods = M('JiandingGoods')->where(array('status'=>1,'is_delete'=>0))->find($goods_id);
			if(empty($goods)) exit('商品不存在');
			//提取关联关联商品表的信息
			//上传者信息
			$goods['user_info'] = D('Jianding/Members')->getMembers($goods['user_id']);
			//鉴定品的图片
			$goods['gimages']  =M('JiandingImg')->where(array('img_type'=>1,'goods_id'=>$goods['goods_id']))->select();
			//鉴定品的价格选择区间
			$goods['gprange']  =M('JiandingGoodsPrange')->where(array('goods_id'=>$goods['goods_id']))->select();
			foreach($goods['gprange'] as $gp){
				$goods['prange_click'] += $gp['favor_click'];
			}
			//鉴定品的属性
			$goods['gattrs']  =M('JiandingGoodsAttr')->where(array('goods_id'=>$goods['goods_id']))->select();
			//评论数量
			$goods['comment'] = M('JiandingComment')->where(array('is_delete'=>0,'goods_id'=>$goods['goods_id']))->order('atime desc')->select();
			$this->goods = $goods;
		/*	echo '<pre>';
			print_r($goods);
			exit;*/
			$this->display('Jianding:g_index');
		}
	}