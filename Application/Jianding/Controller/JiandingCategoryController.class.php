<?php

	/**
	* 目录分类首页
	* JiandingCategoryController.class.php
	* author zhihui 
	* date 2015-2-9
	*/
	namespace Jianding\Controller;
	use Jianding\Controller\JiandingController;
	use Jianding\Service\CategoryService;
	use Jianding\Service\GoodsService;

	class JiandingCategoryController extends JiandingController{

		//目录的首页
		public function cindex(){
			$param = I('get.');

			//如果get 参数里面有attr_id  and  attr_value  则  走attrIndex 入口
			if(!empty($param['attr_id'])&&!empty($param['attr_value'])){
				$this->attrIndex($param);
			}



			$catspell = I('get.catspell');
			//实例化目录service
			$cs = new CategoryService();

			//当前选择的目录ID
			$cat_id = $cs->getCatId($param['catspell']);
			$this->cat_id = $cat_id;
			//提取所有的目录
			$this->assign('allCategory',$cs->getAllCategory());

			//目录首页的属性值分类赋值

			$this->assign('attr_info',$cs->getAttrs($cat_id));

			//实例化商品service
			$gs = new GoodsService();
			$gwhere = array('cat_id'=>$cat_id);

			//获取目录的排序
			switch(I('get.ordertype')){
				case 'pageview':
					$order='page_view desc,goods_atime desc';
					break;
				case 'gprice':
					$order='goods_price asc,goods_atime desc';
					break;
				default:
					$oder ='goods_atime desc';
				}

			//该目录下的所有商品
			$this->assign('jdgoods',$gs->getGoods($gwhere,$order));
			$this->display('FrontPage:cat_index');


		}


		//属性筛选的入口
		public function attrIndex($param){
			//实例化目录service
			$cs = new CategoryService();
			$cat_id = $cs->getCatId($param['catspell']);

			//当前选择的目录ID
			$this->cat_id = $cat_id;
			$param['cat_id'] = $cat_id;

			//提取所有的目录
			$this->assign('allCategory',$cs->getAllCategory());

			//目录首页的属性值分类赋值

			$this->assign('attr_info',$cs->getAttrs($cat_id));
			$where = array('cat_id'=>$cat_id,'attr_id'=>$param['attr_id'],'attr_value'=>$param['attr_value']);
			$gs = new GoodsService();
			$this->assign('jdgoods',$gs->getAttrGoods($where));
			// 把筛选的参数付给模板

			//params序列化传递给 模板  getWgoods() 则判断有没有param 有则走属性筛选;
			$this->params = serialize($param);

			$this->display('FrontPage:cat_index');
			exit;

		}



		//首页ajax 调用入口  每次取4个商品，直到没有了
		public function getWgoods(){
			if(!IS_AJAX){return;}
			if(I('get.param')){
				$this->getAttrWgoods(I('get.param'));
			}
			$cat_id = I('get.cat_id');
			$cwhere = array('cat_id'=>$cat_id);
			$which_time = I('post.which_time');
			$goods = new GoodsService();
			$wfgoods = $goods->indexwf($which_time,'',$cwhere);

			//判断是否有商品，或者有没有4个
			$dataInt = array();
			if(empty($wfgoods)){
				$dataInt['flag'] = false;
				$dataInt['retdata'] = array();
			}else{
				$dataInt['flag']=true;
				$dataInt['retdata'] = $wfgoods;
			}

			//返回数据
			exit(json_encode($dataInt));
		}

		//进入到属性筛选的时候的ajax调用的入口

		public function getAttrWgoods($param){
			$param = unserialize($param);
			$where = array('cat_id'=>$param['cat_id'],'attr_id'=>$param['attr_id'],'attr_value'=>$param['attr_value']);
			$which_time = I('post.which_time');
			$goods = new GoodsService();
			$wfgoods = $goods->attrwf($which_time,'',$where);
			
			//判断是否有商品，或者有没有4个
			$dataInt = array();
			if(empty($wfgoods)){
				$dataInt['flag'] = false;
				$dataInt['retdata'] = array();
			}else{
				$dataInt['flag']=true;
				$dataInt['retdata'] = $wfgoods;
			}

			//返回数据
			exit(json_encode($dataInt));

		}

	}