<?php

	/*
	* 杂项类，放置一些无法归类的功能
	* 
	*/
	namespace Jishou\Service;
	use Think\Model;

	class MiscellaneousService {
		public function getAllGoods($prefix=''){
			$prefix = empty($prefix)?'yishu_':$prefix;
			$m= new Model();

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
		
		$goods_data = $m->table('__JISHOU_GOODS__ g')->join('__JISHOU_GOODS_IMG__  i on g.goods_id=i.goods_id')
			->field(array_merge($img_field,$goods_field))
			->where('i.img_type="origin" and g.is_on_sale=1')
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