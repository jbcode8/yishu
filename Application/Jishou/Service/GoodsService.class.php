<?php
	namespace Jishou\Service;
	use Think\Model;

	class GoodsService{

		/*
			array(
				'goods'=>  goods表中的商品信息
				'img'  => goods_img表中的商品信息
				'attrs' => goods_attr 表中的信息
			);
		*/
		protected $goods_info= array();

		protected $goods_field = array(  'cat_id',
                            'user_id',
							'goods_id',
							'goods_name',
							'goods_sn',
							'goods_price',
							'goods_num',
							'goods_brief',
							'goods_desc',
							'keyword',
							'goods_atime',
							'is_delete',
							'is_on_sale',
							'status',
							'region_name',
							'page_view',
							'index_show');
		protected $img_field = array('img_name','img_path','img_type','img_order');
		protected $attr_field = array('attr_name','attr_value','attr_ext');
		protected $goodsid = null;
		
		function __construct($goodsid){
			$goodsid = intval($goodsid);
			$this->goodsid = $goodsid;

			$jishou_goods =
				D('JishouGoods')
					->field($this->goods_field)
					->where(array('goods_id'=>$goodsid))
					->limit(1)->select();
			$goods_img = 
				D('JishouGoodsImg')
					->field($this->img_field)
					->where(array('goods_id'=>$goodsid))
					->select();
			$goods_attrs =
				D('JishouGoodsAttr')->
				field($this->attr_field)
				->where(array('goods_id'=>$goodsid))
				->select();
			
			$this->goods_info['goods'] = $jishou_goods[0];
			$this->goods_info['img'] = $goods_img;
			$this->goods_info['attrs'] = $goods_attrs;
			

		}

		public function info(){
			return $this->goods_info;
		}
		
		
		/**
		  *@method 获取商品所属的目录
		  *@param null
		  *@return String 目录导航信息
		*/
		public function getDir(){
			$cat_id = $this->goods_info['goods']['cat_id'];
			
			/*$category = D('JishouCategory')
					->field('cat_name,cat_id,parent_id')
					->where(array('cat_id'=>$cat_id))->select();*/

			$cats = $this->cats($cat_id);
			//return $cats;
			$directory = '';
			foreach($cats as $k=>$v){
				$directory.= '<a href="'.U('/jishou/'.$v).'">'.$k.'</a>&gt;';
			}
				$directory.="<i class=' red'>".$this->goods_info['goods']['goods_name']."</i>";

			return $directory;
		}
		
		/**
		  *@method 获取所有的父级目录
		  *@param $cat_id 目录ID
		  *@return array   cat_id=>cat_name 型的关联数组
		*/
		protected function cats($cat_id){
			$cat = array();
			$pcat = array();
			$category = D('JishouCategory')
					->field('cat_name,cat_id,parent_id,cat_spell')
					->where(array('cat_id'=>$cat_id))->limit(1)->select();
			
			$category=$category[0];
			//return $category;
			$cat[$category['cat_name']]=$category['cat_spell'];
			if($category['parent_id']!=0){
				$pcat =$this->cats($category['parent_id']);
			}

			return array_reverse(array_merge($cat,$pcat),false);
			
		}

		/**
		  *@method 测试数据是否准备好了
		  *@param null
		  *@return boolean trun表示数据准备好了
		*/
		public function is_ready(){
			if(	empty($this->goods_info)
				||empty($this->goods_info['goods'])
				||empty($this->goods_info['img'])
				||empty($this->goods_info['attrs'])
			   ){
				return false;
			}
			return true;
		}
		
		/**
		  *@method 获取商品的全部信息
		  *@param null
		  *@return array(goods=>商品的基本信息 ，attrs=>商品的额属性信息)
		*/
		public function goods_info(){
			$goods_info=array();
			$goods_info['goods']= $this->goods_info['goods'];
			$goods_imgs = $this->goods_info['img'];

			foreach($goods_imgs as $goods_img){
				if($goods_img['img_type']=='origin'){
					$goods_info['goods']['img_url']=$goods_img['img_path'].$goods_img['img_name'];
					break;
				}
			}

			$goods_info['attrs'] = $this->goods_info['attrs'];

			return $goods_info;
			
		}
		/*
		*method 商品浏览自增
		*@return  boolean 成功返回true 
		*/
		public function goodsViewInc($goods_id){
            $pageviewinc = unserialize($_COOKIE['pageviewinc']);
            if(empty($pageviewinc)){$pageviewinc = array();}
			if(!in_array($goods_id,$pageviewinc)){
				$flag = M('jishou_goods')->where(array('goods_id'=>$this->goodsid))
						->setInc('page_view');
                $pageviewinc[] =$goods_id; 
                //print_r($pageviewinc);
				setcookie('pageviewinc',serialize($pageviewinc));
			}
			return true;
		}
		
		/*
		*method 获取订单显示也的商品信息
		*@param  $goods_id  商品的ID
		*@return array 返回匹配到的商品信息
		*/

		public function orderGoods($goods_id){
			//传进的goods_id为Y-id-m格式
			$goods_id = explode('-',$goods_id);
			//获取商品的goods的ID
			$goods_id = $goods_id[1];
			$goods_field = array(
				'user_id',
				'goods_id',
				'goods_name',
				'goods_price',
			);

			$goods_data= M('JishouGoods')->where(array('goods_id'=>$goods_id))->select();

			return $goods_data;
		}
		
	
	}

?>
