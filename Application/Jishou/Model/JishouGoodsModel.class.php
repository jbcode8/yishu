<?php
	namespace Jishou\Model;
	use Think\Model;
	use Think\Model\RelationModel;
	class JishouGoodsModel extends RelationModel{


		protected $_link = array(       
				  /*'category'=>array(          
				 	  'mapping_type'      => self::BELONGS_TO,         
				 	   'class_name'        => 'JishouCategory',            // 定义更多的关联属性            ……        
				 	   'foreign_key'  =>'cat_id',
				 	   //'relation_foreign_key'  =>'cat_id',
				 	   //'condition' =>'img_type="thumb"',
				 	   //'mapping_fields'   =>'cat_name',
				 	   ), */
				 'img'=>array(          
				 	  'mapping_type'      => self::HAS_ONE,         
				 	   'class_name'        => 'JishouGoodsImg',            // 定义更多的关联属性            ……        
				 	   'foreign_key'  =>'goods_id',
				 	   'condition' =>'img_type="thumb"',
				 	   'mapping_fields'   =>'img_name,img_path',
				 	   ), 
				 
	 	       );


		public function getIndexGoods($prefix=''){
			
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
			->where('i.img_type="origin" and g.is_on_sale=1 and g.is_delete=0 and g.status=2')
			->order('goods_atime desc')
			->limit(3)
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