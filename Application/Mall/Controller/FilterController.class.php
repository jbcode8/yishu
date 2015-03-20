<?php

// +------------------------------
// | 筛选页的 前端控制器文件
// +------------------------------
// | Author: Rain Zen
// +------------------------------

namespace Mall\Controller;

class FilterController extends FpublicController {

    public function _initialize(){

        parent::_initialize();

        // 店铺总数 和 产品总数
        $this->allStore = D('Store')->where(array('status'=>1))->count();
        $this->allGoods = D('Goods')->where(array('status'=>2))->count();

        // 筛选分类
        $this->pareCate = D('Category')->resetCategore(0,0,1);

        // 品牌列表
        $this->brand = D('Brand')->where(array('recommend'=>1))->select();
    }

    public function index(){

        // 筛选条件开始
        //$cid = isset($_GET['cid']) ? intval($_GET['cid']) : 0;	
		if(!empty($_GET['short_name'])){
			if($_GET['short_name'] == 'all'){
				$this->all = 1;
			}
			$cid = $this->getCidByShortname($_GET['short_name'])?$this->getCidByShortname($_GET['short_name']):0;
			$this->short_name = $_GET['short_name'];
		}
        if($cid){

            $this->cid = $cid;
            //exit($cid);
            $aryCids = D('Category')->getSubCategoryId($cid);
            /*echo "<pre>";
            print_r($aryCids);exit;*/
            if($aryCids){
                $map['cate_id'] = array('in', $aryCids);
            }else{
                $map['cate_id'] = $cid;
            }
            $this->cateName = getCateName($cid, 'Category');

            // 子级类别
            $subCate = D('Category')->resetCategore($cid);
            if(empty($subCate)){
				$parent_id = D('Category')->getParentCategory($cid)[1][0];	
				$parent_name = D('Category')->getParentCategory($cid)[1][1];
				$parent_shortname = D('Category')->getParentCategory($cid)[1][2];
				$parentCate = D('Category')->resetCategore($parent_id);
				$this->parentName = $parent_name;
				$this->parentCate = $parentCate;
				$pparent_id = D('Category')->getParentCategory($parent_id)[1][0];	
				$pparent_name = D('Category')->getParentCategory($parent_id)[1][1];	
				$pparent_shortname = D('Category')->getParentCategory($parent_id)[1][2];	
				$pparentCate = D('Category')->resetCategore($pparent_id);
				$this->pparentName = $pparent_name;
				$this->pparentCate = $pparentCate;
				$this->top_id = $pparent_id;
				$this->next_id = $parent_id;
				$this->all2_name = $pparent_shortname;
				$this->all3_name = $parent_shortname;
			}else{
				$parent_id = D('Category')->getParentCategory($cid)[1][0];	
				$parent_name = D('Category')->getParentCategory($cid)[1][1];
				$parent_shortname = D('Category')->getParentCategory($cid)[1][2];
				if(empty($parent_id)){ //当前已处在顶级分类
					$pparent_id = 1;
				}else{ //处在二级分类
					$pparent_id = D('Category')->getParentCategory($parent_id)[1][0];	
				}
				if($pparent_id == 0){
					$psubCate = D('Category')->resetCategore($parent_id);
					$this->psubName = $parent_name;
					$this->psubCate = $psubCate;
					$this->all2_name = $parent_shortname;
				}else{
					$this->all2_name = $_GET['short_name'];				
				}
				$this->subCate = $subCate;
				$this->top_id = $parent_id;				
				$this->all2 = 1;
				$this->all3_name = $_GET['short_name'];

			}

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
        }else{
			$this->cid = 0;
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
		if(isset($_GET['price'])){
			$this->price = $_GET['price'];
			switch($_GET['price']){
				case '0':
				break;
				case '1':$map['goods_price'] = array('LT',1000);
				break;
				case '2':$map['goods_price'] = array(array('EGT',1000),array('LT',10000),'AND');
				break;
				case '3':$map['goods_price'] = array('EGT',10000);
				break;
				default:
				break;
			}
		}

        // 读取产品信息
        $map['status'] = 2;

        // 读取总数
        $intCount = D('Goods')->where($map)->count();
        if($intCount > 0){

            $size = 25;
            $zPage = new \Org\Util\Zpage($intCount, $size, 11, $_GET['page'], '?page=', $uri);
            $this->htmlPage = $zPage->html();
            $this->allPage = $zPage->allPage;
            $this->thenPage = $zPage->thenPage;
            $this->intCount = $intCount;

            $start = ($zPage->thenPage - 1) * $size;
            $limit = $start == 0 ? $size : $start.','.$size;

            $field = array('goods_name','market_price','goods_price', 'default_img','goods_id','store_id', 'cate_id', 'brand_id');
            $sort = I('get.sort',1,intval);
            switch ($sort){
                case 1:
                    $order = 'goods_id DESC';
                    break;
                case 2:
                    $order = 'views DESC';
                    break;
                case 3:
                    $order = 'comment DESC';
                    break;
                case 4:
                    $order = 'goods_price DESC';
                    break;
                case 5:
                    $order = 'goods_price ASC';
                    break;
                default :
                    $order = 'goods_id DESC';
                    break;
            }
            //$aryGoods = D('Goods')->field($field)->where($map)->select();
            $aryGoods = D('GoodsView')->getAll($map, $order, $limit);
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

		//seo标题 关键词 描述
		$arr_category = D('Category')->find($cid);
		if(empty($this->cateName)){
			$this->cateName = '所有商品';
		}
		if(empty($arr_category['seo_title'])){
			$seo_title = "【".$this->cateName."】".$this->cateName."价格_".$this->cateName."图片_收藏_交易_大全_".C('WEB_SITE.name');
		}else{
			$seo_title = $arr_category['seo_title'];
		}
		if(empty($arr_category['seo_keyword'])){
			$seo_keyword = $this->cateName.",".$this->cateName."价格,".$this->cateName."图片,".$this->cateName."收藏,".$this->cateName."交易,".$this->cateName."大全";
		}else{
			$seo_keyword = $arr_category['seo_keyword'];
		}
		if(empty($arr_category['seo_description'])){
			$seo_description = C('WEB_SITE.name')."为玩家提供最新".$this->cateName."、".$this->cateName."价格、".$this->cateName."图片、".$this->cateName."收藏、".$this->cateName."交易、".$this->cateName."大全,".C('WEB_SITE.name').$this->cateName."平台是国内外领先的专业古玩交易网站,古玩捡漏,服务专业,安全交易,值得信赖!";
		}else{
			$seo_description = $arr_category['seo_description'];
		}
		$this->assign('seo_title',$seo_title);
		$this->assign('seo_keyword',$seo_keyword);
		$this->assign('seo_description',$seo_description);

        $this->aryUri = $aryUri;
        $this->history = aryHistory();
        $this->position = '商品筛选';
        $this->position_1 = $this->_lists(1);
        $this->position_2 = $this->_lists(2);
        $this->position_3 = $this->_lists(3);
        $this->position_4 = $this->_lists(4);
        $this->position_7 = $this->_lists(7);
        $this->position_6 = $this->_lists(6);
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
    //通过所在父ID取出所有数据
    private function _lists($id){
        foreach ($this->_relaction($id) as $v){
            if($goods = M('mall_goods')->field('goods_id,goods_name,goods_price,default_img')->where(array('cate_id' => $v))->select()){
                $goodes[] = $goods;
            }
        }
        return $goodes;
    }
    //按父级ID取出所有子ID
    private function _relaction($id){
        if($id){
            $goods = getChildsId(M('mall_category')->select(), $id);
        }
        return $goods;
    }

	//根据short_name反查cid
	private function getCidByShortname($short_name){
		return M('mall_category')->where(array('short_name'=>$short_name))->getField('cate_id');
	}
} 