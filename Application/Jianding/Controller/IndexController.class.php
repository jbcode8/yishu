<?php
	namespace Jianding\Controller;
	use Jianding\Controller\JiandingController;
	use Jianding\Service\CategoryService;
	use Jianding\Service\GoodsService;
	class IndexController extends JiandingController
	{
		public function index()
		{
			$category = new CategoryService();
			//获取所有的目录
			$this->assign('allCategory',$category->getAllCategory());
			$goods = new GoodsService();
			//查看是否有排序的
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
			$this->assign('jdgoods',$goods->getGoods(array(),$order));
			$this->display('FrontPage/jd_index');
		}



		//首页ajax 调用入口  每次取4个商品，直到没有了
		public function getWgoods(){
			if(!IS_AJAX){return;}
			$which_time = I('post.which_time');
			$goods = new GoodsService();
			$wfgoods = $goods->indexwf($which_time);


			//判断是否有商品，或者有没有4个
			$dataInt = array();
			if(empty($wfgoods)||count($wfgoods)<4){
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
