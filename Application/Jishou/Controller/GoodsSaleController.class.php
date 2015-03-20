<?php
	/**
	*  前台展示的已售出的商品的获取入口
	*/
	namespace Jishou\Controller;
	use Jishou\Service\SaleGoodsService;

	class GoodsSaleController extends JishouController{

		/**
		*前台全部的显示的页面 首页的已售商品的轮换入口
		*post 传参  array('page'=>请求的第几页)
		*/
		public function ishow(){
			$page = $_POST['page'];
			$saleGoods = new SaleGoodsService;

			$return = array();
			$goods = $saleGoods->saleGoods($page);
			if(empty($goods)){
				$return = array('flag'=>false);
			}else{
				$return = array('flag' => true, 'data' => $goods );
			}

			exit(json_encode($return));
		}

		/**
		*目录页已售商品轮换调用的入口
		* post 传参  array('page'=>请求的第几页，‘cat_id’ => 当前的目录ID)
		*/
		public function cshow(){
			$page = $_POST['page'];
			$cat_id = $_POST['cat_id'];
			$saleGoods = new SaleGoodsService;

			$return = array();
			$goods = $saleGoods->saleGoods($page,$cat_id);
			if(empty($goods)){
				$return = array('flag'=>false);
			}else{
				$return = array('flag' => true, 'data' => $goods );
			}

			exit(json_encode($return));
		}




	}