<?php


	/*
	*GategoryService  目录操作的数据返回接口
	*/
	namespace Jishou\Service;
	use Think\Model;
	use Think\Page;

	
	class CategoryService{
		protected $cat_field = array(
		'cat_id','cat_name','keywords','cat_desc','cat_spell'
	);

		protected $has_error = array(); //错误的存放地址

		protected $cat_list = array();  //保存该目录及其所有子目录


		protected $goods_field = array(  'cat_id',
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
		protected $img_field = array('img_name','img_path','img_type','img_order');

		
		protected $attr_field = array('attr_name','attr_value','attr_ext');
		

		public function __construct($catsid){
			$catsid = intval($catsid);
			$data = M('JishouCategory')->where(array('cat_id'=>$catsid))->select();

			if(empty($data)){
				$this->has_error['name']='目录不存在';
			}else{
				array_push($this->cat_list,$catsid);
				$this->getChildrenCats($this->cat_list);
			}
		
		}

		
		/*
		* is_ready 该类实例化有错呢
		* @parameter null
		* @return true  没有错误   array返回错误的this->has_error;
		*/
		public function is_ready(){
			if(empty($this->has_error)){
				return true;
			}else{
			
				return $this->has_error;
			}
		
		}
		
		/*
		* method  递归获取子目录
		* @parameter  $cat_list 父目录列表在 获取子目录
		* @return null 
		*/
		protected function getChildrenCats($cat_list){
			$cats = M('JishouCategory')->field('cat_id')
					->where(array('parent_id'=>array('in',$cat_list)))
					->select();
			$cat_list = array();
			if(!empty($cats)){
				foreach($cats as $cat){
					array_push($cat_list,$cat['cat_id']);
				}
				$this->getChildrenCats($cat_list);
			}

			//保存目录在到属性中
			$this->cat_list = array_merge($this->cat_list,$cat_list);
		}


		/*
		*@method 获取商品及所有的子目录下的商品  同MiscelaneousService类的getAllGoods功能
		*@param null
		*@return   array 返回所有匹配到的商品
		*/
		public function getCategoryGoods(){


			$allnum = M('JishouGoods')
			->where(array('is_on_sale'=>1, 'status'=>2,'is_delete'=>0))
			->where(array('cat_id'=>array('in',$this->cat_list)))
			->count();
			$pagenum = 8;

			$Page = new Page($allnum,$pagenum);

			$Page->setConfig('prev','上一页');
			$Page->setConfig('next','下一页');
			$m = new Model();
			$goods_data = $m->table('__JISHOU_GOODS__ g')->join('__JISHOU_GOODS_IMG__  i on g.goods_id=i.goods_id')
			->field(array_merge($this->img_field,$this->goods_field))
			->where('i.img_type="origin" and g.is_on_sale=1 and g.status=2 and g.is_delete=0')
			->where(array('cat_id'=>array('in',$this->cat_list)))
			->order('goods_atime desc')
			->limit($Page->firstRow.','.$Page->listRows)
			->select();
			foreach($goods_data as &$data){

			$data['img_url'] = $data['img_path'].$data['img_name'];
			$attr = D('JishouGoodsAttr')
				->field($attr_field)
				->where(array('goods_id'=>$data['goods_id']))
				->select();
			$data['attr']=$attr;
				
		}

			return array('goods_data'=>$goods_data,'page_info'=>$Page->show());

		}
		
		/*
		* 获取所有的目录信息
		*@return array 所有的分类目录数组
		*/
		public function getAllCategory(){
			$cat = M('JishouCategory')->field($this->cat_field)->select();
			return $cat;
		}
	}
