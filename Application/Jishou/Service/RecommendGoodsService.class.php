<?php
	/*推荐商品调用接口  处理所有的目录分类，商品页面商品推送
	*RecommentGoodsService.class.php
	* author zhihui
	*/
	namespace Jishou\Service;
	use Think\Model;

	class RecommendGoodsService {
		protected $field= array(
			'g.goods_id'=> 'gid',
			'g.goods_name' =>'gname',
			'g.goods_price'=> 'gprice',
			'm.img_name' =>'img_name',
			'm.img_path' =>'img_path',
			);
		protected $table= array(
			'jishou_goods'=>'g',
			'jishou_goods_img' =>'m'
		);


		protected $where = null;

		protected $model = null;

		/*
		*@param  $goods_id 商品页面的ID   $prefix  table 的前缀，多表查询手动添加
		*/
		public function __construct($goods_id,$prefix=''){
			$prefix = empty($prefix)?'yishu_':$prefix;
			foreach($this->table as $k=>$v){
				$this->table[$prefix.$k] = $v;
				unset($this->table[$k]);
			}
			$this->model = new Model();
			$this->where =array(
				'g.goods_id'=>array('neq',$goods_id),
				'm.goods_id = g.goods_id'
			);
		}

		/*
		*@method goodsPage 商品页面的推荐商品
		*/
		public function goodsPage($goods_id){
			$recommend_goods = array();
			
			/*$recommend_goods = $this->model
					->table($this->table)
					->field($this->field)
					->where($this->where)->limit(5)->select();*/

			$recommend_goods = M()->table('__JISHOU_GOODS__ g')->join('__JISHOU_GOODS_IMG__ m on g.goods_id=m.goods_id')
			->field($this->field)
			->where('m.img_type="origin" and g.is_on_sale=1 and g.status=2 and g.goods_id <>'.$goods_id)
			->order('goods_atime desc')
			->limit(5)
			->select();

			foreach($recommend_goods as $k=>$goods){   //拼接图片的额路径
				$recommend_goods[$k]['img_url']=$goods['img_path'].$goods['img_name'];
			}

			return $recommend_goods;
		}
	
	
	}


?>