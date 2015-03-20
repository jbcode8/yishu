<?php

// +----------------------------------------------------------------------
// | 前端 品牌 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen   modify 2014/07/18  663642331@qq.com  Kaiwei Sun
// +----------------------------------------------------------------------

namespace Mall\Controller;

class BrandController extends FpublicController {
    public function _initialize(){
        parent::_initialize();
        // 店铺总数 和 产品总数
        $this->allStore = D('Store')->where(array('status'=>1))->count();
        $this->allGoods = D('Goods')->where(array('status'=>1))->count();
        // 筛选分类
        $this->pareCate = D('Category')->resetCategore(0,0,1);
        // 品牌列表
        $this->brand = D('Brand')->where(array('recommend'=>1))->select();
    }
    
    public function index() {
        // 筛选条件开始
        $cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;
        if($cid){

            $this->cid = $cid;

            $aryCids = D('Category')->getSubCategoryId($cid);
            if($aryCids){
                $map['cate_id'] = array('in', $aryCids);
            }else{
                $map['cate_id'] = $cid;
            }
            $this->cateName = getCateName($cid, 'Category');

            // 子级类别
            $subCate = D('Category')->resetCategore($cid);
            empty($subCate) OR $this->subCate = $subCate;

            // 面包屑导航
            $toParentCate = D('Category')->getParentCategory($cid);
            empty($toParentCate) OR $this->toParentCate = array_reverse($toParentCate);

            // 传值
            $aryUri['cid'] = $cid;

            // 根据类别ID(cate_id)获取对应的属性值
            $aryAttr = $this->_aryAttr($cid);
            if($aryAttr){
                $this->attrVal = $aryAttr[1];
                $this->attrName = $aryAttr[0];
            }
        }

        $bid = isset($_GET['bid']) ? intval($_GET['bid']) : 0;
        if($bid){
            $this->bid = $bid;
            $map['brand_id'] = $bid;
        }

        $attr_id = isset($_GET['ati']) ? intval($_GET['ati']) : 0;
        $attr_vid = isset($_GET['avi']) ? intval($_GET['avi']) : 0;
        if($attr_id && $attr_vid){
            $gids = M('MallGoodsAttr')->field('goods_id')->where(array('val_id'=>$attr_vid))->select();
            if(!empty($gids) && is_array($gids)){
                $inGids = array();
                foreach($gids as $rs){
                    $inGids[] = $rs['goods_id'];
                }
            }
            empty($inGids) OR $map['goods_id'] = array('in', $inGids);
        }

        // 读取产品信息
        $map['status'] = 2;

        // 读取总数
        $intCount = D('Goods')->where($map)->count();
        if($intCount > 0){
            $onepage = I('get.onepage',2,intval) ? I('get.onepage',2,intval) : 2;
            $onpage = I('get.p',1,intval) ? I('get.p',1,intval) : 1;
            $page = new \Think\Pages($intCount,$onepage);
            $page->setConfig('prev',"◀ 上一页");   
            $page->setConfig('next','下一页 ▶');   
            $page->setConfig('first','◀ 首页');   
            $page->setConfig('last','尾页 ▶'); 
            $pages = ceil($intCount / $onepage);
            $field = array('goods_name','market_price','goods_price', 'default_img','goods_id','store_id', 'cate_id', 'brand_id');
            $order = 'goods_id DESC';
            $p = 1;
            $ps = '';
            while ($p <= $pages) {
                if(I('get.p')==$p){
                    $ps .= "<option value='".$p."' selected>".$p."</option>";
                }else{
                    $ps .= "<option value='".$p."'>".$p."</option>";
                }
                $p++;
            }
            $aryGoods = D('Goods')->field($field)->where($map)->order($order)->page($onpage,$onepage)->select();
            $show = $page->show();
            $this->intCount = $intCount;
            $this->page = $show;
            $this->pages = $pages;
            $this->ps = $ps;
        }
        // 获取产品ID的数组
        if(!empty($aryGoods)){
            $this->goods = $aryGoods;
            list($goodsIds, $storeIds) = $this->_aryIds($aryGoods);
            $storeIds = array_unique($storeIds);
        }

        // 获取产品对应的静态信息 和 店铺信息
        if(is_array($goodsIds) && !empty($goodsIds) && !empty($storeIds)){

            // 产品静态信息
            $aryStatic = M('MallGoodsCount')->where(array('goods_id'=>array('in',$goodsIds)))->select();
            empty($aryStatic) OR $this->goodsStatic = $this->_parseAryForKey($aryStatic);

            // 店铺信息
            $aryStore = D('Store')->field('store_id, store_name')->where(array('store_id'=>array('in',$storeIds)))->select();
            empty($aryStore) OR $this->store = $this->_parseAryForKey($aryStore, 'store_id');

            // 店铺静态信息
            $aryStoreStatic = M('MallStoreCount')->where(array('store_id'=>array('in', $storeIds)))->select();
            empty($aryStoreStatic) OR $this->storeStatic = $this->_parseAryForKey($aryStoreStatic, 'store_id');
        }
        //热点推荐
        $fields = 'goods_id, goods_name, default_img, goods_price, market_price';
        $hot['recommend'] = 1;
        $hot['status'] = 2;
        $hotgoods = D('Goods')->field($fields)->where($hot)->order('goods_id desc')->limit(6)->select();
        $this->hot = $hotgoods;
        $this->aryUri = $aryUri;
        $this->menu = '店铺列表';
        $this->display();
    }
    /**
     * 根据产品数组返回产品ID和店铺ID
     * @param $ary
     * @param array $keys
     * @return array二维数组
     */
    private function _aryIds($ary, $keys = array('goods_id', 'store_id')){
        $ids = $sids = array();
        if(is_array($ary)){
            foreach($ary as $rs){
                $ids[] = $rs[$keys[0]];
                $sids[] = $rs[$keys[1]];
            }
        }
        return array($ids, $sids);
    }

    /**
     * 转化数组为带指定键的数组
     * @param $ary
     * @param string $key
     * @return array
     */
    private function _parseAryForKey($ary, $key = 'goods_id'){
        $reAry = array();
        foreach($ary as $rs){
            $reAry[$rs[$key]] = $rs;
        }
        return $reAry;
    }

    private function _aryAttr($cate_id){

        // 先根据小类别获取大类别
        $cateInfo = D('Category')->field('cate_id, cate_name, parent_id, attribute')->find($cate_id);

        if($cateInfo){

            // 获取此类别的父类别的属性名
            $parentAttr = D('Category')->parentAttr($cate_id);

            // 将当前类别的属性值合并到父级属性中
            if(empty($cateInfo['attribute'])){
                $allAttr = implode(',', $parentAttr);
            }else{
                empty($parentAttr) OR $strParentAttr = implode(',', $parentAttr);
                $allAttr = empty($strParentAttr) ? $cateInfo['attribute'] : $cateInfo['attribute'].','.$strParentAttr;
            }

            // 再根据类别获取属性值
            empty($allAttr) OR $attrVal = getAttrVal($allAttr);

            // 获取属性名
            $arrAttrName = D('Attribute')->getAttributeCache();
            if($attrVal){
                foreach($arrAttrName as $rs){
                    $arr[$rs['attr_id']] = $rs['attr_name'];
                }
                $attrName = $arr;
                unset($arr);
            }

            return array($attrName, $attrVal);
        }
    }
}