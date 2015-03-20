<?php
	
	/**
	* 寄售商品详情
	*  author :zhihui
	* date: 2015-3-12
	*/
	namespace Mobile\Controller;
	use Mobile\Controller\MobileController;

	class JishouGoodsDetailController extends MobileController{

		public function goods_detail(){
			$goods_id = I('get.goods_id');
			$jishou_goods =M('JishouGoods')->find($goods_id);;
			if(empty($jishou_goods)){
				exit('no data');
			}
			//获取图片
			$where = array('img_type'=>'origin','goods_id'=>$jishou_goods['goods_id']);
			$goods_img = M('JishouGoodsImg')->where($where)->find();
			//获取属性
			$where = array('img_type'=>'origin','goods_id'=>$jishou_goods['goods_id']);
			$goods_attrs = M('JishouGoodsAttr')->where($where)->find();

			$this->assing(array(
				'jishou_goods'=>$jishou_goods,
				'goods_img' =>$goods_img,
				'goods_attrs' =>$goods_attrs,
				));

			
			$this->display('Jishou:goods_detail');
		}
	}