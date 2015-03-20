<?php
	/**
	* 寄售web端首页
	* author : zhihui
	* date : 2015-3-12
	*/
	namespace Mobile\Controller;
	use Mobile\Controller\MobileController;
	class JishouIndexController extends MobileController{
		public function index(){
			//首页获取所有的在售的商品
			$where = array('is_delete'=>0,'is_on_sale'=>1,'status'=>2);
			$goods_is_sale = M('JishouGoods')->where($where)->order('goods_atime desc')->select();
			foreach($goods_is_sale as &$is){
				$is['img'] = M('JishouGoodsImg')->where(array('goods_id'=>$is['goods_id'],'img_type'=>'origin'))->find();
			}
			$where = array('is_delete'=>0,'is_on_sale'=>0,'status'=>2);
			$goods_has_sale = M('JishouGoods')->where($where)->order('goods_atime desc')->select();
			foreach($goods_has_sale as &$has){
				$has['img'] = M('JishouGoodsImg')->where(array('goods_id'=>$has['goods_id'],'img_type'=>'origin'))->find();
			}
			$this->assign(array(
				'goods_is_sale' => $goods_is_sale,
				'goods_has_sale' => $goods_has_sale,
				));
			$this->display("Jishou:js_index");
		}
	}