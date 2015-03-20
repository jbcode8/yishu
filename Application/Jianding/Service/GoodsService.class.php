<?php
	/**
	*  鉴定商品数据操作的层
	*  GoodsService.class.php
	*  author: zhihui 
	*  date: 2015-2-6
	*/
	namespace Jianding\Service;
	class GoodsService{
		//定义页面初始化的商品的显示数量
		protected $page_limit = 10;
		//瀑布流每次获取商品的默认四个
		protected $wf_num = 4;
		//初始化页面获取商品的数量
		public function getGoods($where=array(),$order='',$limit=''){
			$limit = empty($limit)?'0,'.$this->page_limit:$limit.','.$this->page_limit;
			$order = empty($order)?'goods_atime desc':$order;
			$goods = M('JiandingGoods')
				->where($where)
				->order($order)
				->limit($limit)
				->select();

			foreach($goods as &$g){
				$member = D('Members')->field('username,email,mobile,lastlogintime,regdate')->find($g['user_id']);
				$g['user_info']=$member;

			}


			return $goods;
		}


		//属性筛选商品的入口
		public function getAttrGoods($where=array(),$order='',$limit=''){
			$limit = empty($limit)?'0,'.$this->page_limit:$limit.','.$this->page_limit;
			$order = empty($order)?'goods_atime desc':$order;
			$goods = M('JiandingGoods')->join('yishu_jianding_goods_attr ON yishu_jianding_goods_attr.goods_id=yishu_jianding_goods.goods_id')
				->where($where)
				->order($order)
				->limit($limit)
				->select();

			foreach($goods as &$g){
				$member = D('Members')->field('username,email,mobile,lastlogintime,regdate')->find($g['user_id']);
				$g['user_info']=$member;

			}
			return $goods;
		}

		//属性筛选商品ajax返回的数据
		public function attrwf($which_time,$order='',$where=array()){
			$limit = empty($which_time)?$page_limit.','.$this->wf_num:($this->page_limit+($which_time-1)*$this->wf_num).','.$this->wf_num;
			$order = empty($order)?'goods_atime desc':$order;
			$goods = M('JiandingGoods')->join('yishu_jianding_goods_attr ON yishu_jianding_goods_attr.goods_id=yishu_jianding_goods.goods_id')
				->where($where)
				->order($order)
				->limit($limit)
				->select();
				
			foreach($goods as &$g){
				$member = D('Members')->field('username,email,mobile,lastlogintime,regdate')->find($g['user_id']);
				$g['user_info']=$member;

			}
			return $goods;
		}


		//首页,目录页，瀑布流获取的数据
		public function indexwf($which_time,$order='',$where=array()){
			$limit = empty($which_time)?$page_limit.','.$this->wf_num:($this->page_limit+($which_time-1)*$this->wf_num).','.$this->wf_num;
			$order = empty($order)?'goods_atime desc':$order;
			$goods = M('JiandingGoods')
				->where($where)
				->order($order)
				->limit($limit)
				->select();
			foreach($goods as &$g){
				$member = D('Members')->field('username,email,mobile,lastlogintime,regdate')->find($g['user_id']);
				$g['user_info']=$member;

			}

			return $goods;

		}



	}
