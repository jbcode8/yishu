<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 前端控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class IndexController extends FpublicController {

    public function _initialize(){

        parent::_initialize();

        $this->Goods = D('Goods');
    }

    // 首页
    public function index() {

        $seo['title'] = '中国古玩城';
        $seo['keywords'] = '中国古玩城';
        $seo['desc'] = '中国古玩城';

		//echo session('mid');exit;

        // 网站公告
        $this->aryNotice = D('Article')->field(array('article_id AS id','title'))->where(array('cate_id'=>12, 'store_id'=>0))->limit(6)->select();

        // 网站类别
        $aryCate = D('Category')->resetCategore();
        $this->aryCate = $aryCate;

        // 推荐店铺
        $aryStore = D('Store')->field(array('store_id','store_logo','store_name'))->where(array('status'=>1))->order('create_time DESC')->limit(6)->select();
        $this->aryStore = $aryStore;

        // 类别下的产品
        $aryCateGoodsCount = $aryCateGoods = array();
        if(!empty($aryCate) && is_array($aryCate)){
            foreach($aryCate as $rs){
                $aryCateGoods[$rs['cate_id']] = D('Goods')->getGoodsByCateId($rs['cate_id']);
                $aryCateGoodsCount[$rs['cate_id']] = D('Goods')->getGoodsCountByCateId($rs['cate_id']);
            }
        }
        $this->aryCateGoods = $aryCateGoods;
        $this->aryCateGoodsCount = $aryCateGoodsCount;

        // 总数统计
        $count['store'] = D('Store')->where(array('status'=>1))->count();
        $count['goods'] = D('Goods')->where(array('status'=>1))->count();
        $count['category'] = D('Category')->where(array('status'=>1))->count();
        $this->count = $count;

        $this->assign('seo', $seo);

		//获取BANNER图
		$array_banner = M('mall_banner')->field('img_url')->order('create_time desc,img_id desc')->limit(6)->select();
		$array_banner = array_reverse($array_banner);
		$this->assign('array_banner', $array_banner);

        $this->display();
    }
	public function solr(){
		
		$counts =  M('mall_keywords')->field('key_id,words')->order('hits DESC')->count();
		$this->lists = M('mall_keywords')->field('key_id,words')->order('hits DESC')->select();
		
		if($counts > 0){
            $size = 300;
            $zPage = new \Org\Util\Zpage($counts, $size, 11, $_GET['page'], '?page=', $uri);
            $this->htmlPage = $zPage->html();
            $this->allPage = $zPage->allPage;
            $this->thenPage = $zPage->thenPage;
            $this->intCount = $counts;
            $start = ($zPage->thenPage - 1) * $size;
            $limit = $start == 0 ? $size : $start.','.$size;
            $lists = M('mall_keywords')->field('key_id,words')->order('hits DESC')->limit($limit)->select();
        }
		$this->lists = $lists;
		$this->display();
	}
}