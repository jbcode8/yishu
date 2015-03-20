<?php
	namespace Jishou\Controller;
	use Jishou\Controller\JishouController;
	use Jishou\Service\GoodsService;
	use Jishou\Service\RecommendGoodsService;

	class GoodsController extends JishouController{
		public function show($goodsid){
			$goodsid = intval($goodsid);
				if(empty($goodsid)){
					$this->error('商品不存在');exit;
				}

			$goods_data = new GoodsService($goodsid);
				if(!$goods_data->is_ready()){
					$this->error('商品不存在');exit;
				}
            //导航目录
			$this->assign('goods_dir',$goods_data->getDir());
            //商品信息
			$this->assign('goods_info',$goods_data->goods_info());
			
			$recommendGoods = new RecommendGoodsService($goodsid,'yishu_');
			$this->assign('goods_recommend',$recommendGoods->goodsPage($goodsid));

			//新闻资讯
			$info = M('JishouNews')->order('add_time desc')->limit(5)->select();
        	$this->assign('info',$info);

            //广告位
            $small_ads = M('Ads')->where(array('ad_belong'=>'jishou','ad_type'=>2))
                        ->order('sort_order desc')
                        ->order('add_time desc')
                        ->limit(1)
                        ->select();
            $this->assign('small_ads',$small_ads);
			
			$goods_data->goodsViewInc($goodsid);
			$this->display();

		}


		/**
		*后台预览商品的按钮入口
		*@param $goodsid
		*/
		public function preview($goodsid){
			$goodsid = intval($goodsid);
				if(empty($goodsid)){
					$this->error('商品不存在');exit;
				}

			$goods_data = new GoodsService($goodsid);
				if(!$goods_data->is_ready()){
					$this->error('商品不存在');exit;
				}
			$this->assign('goods_dir',$goods_data->getDir());
			$this->assign('goods_info',$goods_data->goods_info());
			
			$recommendGoods = new RecommendGoodsService($goodsid,'yishu_');
			$this->assign('goods_recommend',$recommendGoods->goodsPage($goodsid));

			//新闻资讯
			$info = M('JishouNews')->order('add_time desc')->limit(5)->select();
        	$this->assign('info',$info);
			
			//$goods_data->goodsViewInc();
			$this->display('Goods/Show');
		}
	}
?>
