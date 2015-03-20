<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Jishou\Controller;

//use Jishou\Controller\PaimaiPublicController;
use Jishou\Controller\JishouController;
use Jishou\Service\MiscellaneousService;
use Jishou\Service\CategoryService;
use Jishou\Service\SaleGoodsService;
//use Home\Controller\HomeController;

class IndexController extends JishouController
{
    public function _initialize()
    {

		
        parent::_initialize();
	
    }
    /**
     * 寄售首页，写清注释
     */
    public function index()
    {	
        //获取广告位信息
        //大图广告位
        $big_ads = M('Ads')->where(array('ad_belong'=>'jishou','ad_type'=>1,'enabled'=>1))
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
		$goods_data = new MiscellaneousService;
		$category = new CategoryService();
	    $saleGoods = new SaleGoodsService();	
		$this->assign('allgoods',$goods_data->getAllGoods());
		$this->assign('categoryall',$category->getAllCategory());
        $this->assign('saleGoods',$saleGoods->saleGoods());
        $info = M('JishouNews')->order('add_time desc')->limit(5)->select();
        $this->assign('info',$info);
        $this->display('Index:index');
    }
}
