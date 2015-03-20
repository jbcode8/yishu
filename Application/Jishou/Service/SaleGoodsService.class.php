<?php

	/**
	* SaleGoodsService 处理前台页面页面要展示的已寄售成功的商品
	*/
	namespace Jishou\Service;

	class SaleGoodsService {

		/**
		*  method 获取已售出的商品
		*  @param $page  default 0 当前的页数
		*  @param $cat_id  获取的商品的目录分类
		*  @return array   获取到的商品的信息
		*/
		public function saleGoods($page=0,$cat_id=null){
			$goodsnum = 2;
			$offset = empty($page) ? 0 :$page*$goodsnum;
			$where = empty($cat_id) ? '':' and g.cat_id='.$cat_id;
			$goods_field = array(  'cat_id',
							'g.goods_id goods_id',
							'goods_name',
							'goods_sn',
							'goods_price',
							'goods_num',
							'goods_brief',
							//'goods_desc',
							//'keyword',
							'goods_atime',
							'is_delete',
							'is_on_sale',
							'status',
							'region_name',
							'page_view',
							'index_show');
		$img_field = array('img_name','img_path','img_type','img_order');

		$table =array(
			$prefix.'jishou_goods'=>'g',
			$prefix.'jishou_goods_img'=>'i'
		);
		$attr_field = array('attr_name','attr_value','attr_ext');
		$m = M();
		$goods_data = $m->table('__JISHOU_GOODS__ g')->join('__JISHOU_GOODS_IMG__  i on g.goods_id=i.goods_id')
			->field(array_merge($img_field,$goods_field))
			->where('i.img_type="origin" and g.is_on_sale=0 and g.status=2'.$where)
			->order('goods_atime desc')
			->limit($offset.','.$goodsnum)
			->select();

		/*$goods_data = $m ->table($table)
			->field(array_merge($img_field,$goods_field))
			->select();*/
		
		foreach($goods_data as &$data){

			$data['img_url'] = $data['img_path'].$data['img_name'];
			$attr = D('JishouGoodsAttr')
				->field($attr_field)
				->where(array('goods_id'=>$data['goods_id']))
				->select();
			$data['attr']=$attr;
				
		}
	
		return $goods_data;
		}
	}