<?php

	namespace Jishou\Controller;
	use Jishou\Controller\JishouController;
	use Jishou\Service\CategoryService;
	use Jishou\Service\SaleGoodsService;

	class CategoryController extends JishouController{
		
		public function show($catspell){
              //获取广告位信息
            //大图广告位
           $big_ads = M('Ads')->where(array('ad_belong'=>'jishou','ad_type'=>1))
                        ->order('sort_order desc')
                        ->order('add_time desc')
                        ->limit(5)
                        ->select();
            //小图广告位
           $small_ads = M('Ads')->where(array('ad_belong'=>'jishou','ad_type'=>2))
                        ->order('sort_order desc')
                        ->order('add_time desc')
                        ->limit(1)
                        ->select();
            //广告位模板赋值

            $this->assign('jishou_ads',array('big_ads'=>$big_ads,'small_ads'=>$small_ads));

			$categoryid = M('JishouCategory')->where(array('cat_spell'=>$catspell))->find();
			$categoryid = $categoryid['cat_id'];

            //实例化目录数据层
			$category = new CategoryService($categoryid);
			 $saleGoods = new SaleGoodsService();	
			

			if(is_array($hasError=$category->is_ready())){
				$this->error($hasError['name']);exit;
			}

			$info = M('JishouNews')->order('add_time desc')->limit(5)->select();
        	$this->assign('info',$info);
			

			//把当前ID传递过去主要是为了目录底下横条的选择
			$this->assign('current_cat',intval($categoryid));
			$this->assign('goods',$category->getCategoryGoods());
			$this->assign('categoryall',$category->getAllCategory());
			$this->assign('saleGoods',$saleGoods->saleGoods(0,$categoryid));
			
			$this->display();
		}
		
	}

?>
