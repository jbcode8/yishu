<?php

	/*
	* 杂项类，放置一些无法归类的功能
	* 
	*/
	namespace Jishou\Service;
	use Think\Model;
	use	Think\Page;

	class MiscellaneousService {
		public function getAllGoods($prefix=''){
			$prefix = empty($prefix)?'yishu_':$prefix;
			$allnum = M('JishouGoods')->where(array('is_on_sale'=>1, 'status'=>2,'is_delete'=>0))->count();
			$pagenum =8;
			$Page = new Page($allnum,$pagenum);

			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
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
			->where('i.img_type="origin" and g.is_on_sale=1 and g.status=2 and g.is_delete=0')
			->order('goods_atime desc')
			->limit($Page->firstRow.','.$Page->listRows)
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
	
		return array('goods_data'=>$goods_data,'page_data'=>$Page->show());
		}

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
			->where('i.img_type="origin" and g.is_on_sale=1')
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

		/**
		*	@method userHasOrder用户是否有该$goods_id 的订单
		*	@parameter  $goods_id 商品的ID
		*	@parameter  $user_id  用户的ID
		*   @return  如果有则返回该用户的订单_id ,没有则返回false
		*/
		public function userHasOrder($goods_id,$user_id){
			$goods_id = explode('-',$goods_id);
			$goods_id = $goods_id[1];
			$where = array(
				'purchaser_id' =>$user_id,
				'goods_id' =>$goods_id,
				'pay_status' =>0,
				);
			//获取是否有已经生成的未付款的订单
			$order_info = M('JishouOrderInfo')
				->join('__JISHOU_ORDER_GOODS__ ON __JISHOU_ORDER_GOODS__.order_id = __JISHOU_ORDER_INFO__.order_id')
				->where($where)
				->order('atime desc')
				->select();
			if(empty($order_info)){
				return false;
			}
			return array('order_info'=>$order_info,'goods_id'=>$goods_id);

		}
	
	}
