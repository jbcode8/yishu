<?php

// +----------------------------------------------------------------------
// | 古玩城 会员中心 店铺商品 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class MgoodsController extends MpublicController {

    // 初始化
    public function _initialize(){

        parent::_initialize();
        $this->Model = D('Goods');
        $this->aryAct = array('addcate', 'addinfo', 'adddoit', 'edit', 'status', 'list');
        $this->where = array('store_id' => $_SESSION['store_id']);

        // 检测店铺是否有类别
        $storeCate = D('StoreCategory')->where($this->where)->select();
        empty($storeCate) AND $this->error('请先添加店铺自定义的类别！', U('Mall/Mcategory/init'));

        $this->storeCate = $storeCate;
    }

    // 初始化入口
    public function init(){

        // 操作方式
        $act = isset($_GET['act']) ? trim($_GET['act']) : 'list';
        in_array($act, $this->aryAct) OR $this->error('不合法URL');

        // 操作函数
        $act == 'list' AND $this->_list($this->where);
        $act == 'addcate' AND $this->_addcate();
        $act == 'addinfo' AND $this->_addinfo($this->where);
        $act == 'adddoit' AND $this->_adddoit($this->where);
        $act == 'edit' AND $this->_edit($this->where);
    }

    public function list_action(){

        (isset($_POST['hideAct']) && !empty($_POST['hideAct'])) AND $act = trim($_POST['hideAct']);
        (isset($_POST['hideGids']) && !empty($_POST['hideGids'])) AND $gids = trim($_POST['hideGids']);

        isset($act) OR $this->error('请选择操作状态！');
        isset($gids) OR $this->error('请选择商品信息！');

        // 组装条件
        $where = $this->where;
        $where['goods_id'] = array('in', $gids);

        if($act == 'del'){

            // 删除商品[只更改状态，数据仍然存在]
            if($this->Model->where($where)->save(array('is_delete' => 0))){
                $this->success('商品删除成功！', U('Mall/Mgoods/init'));
            }

        }else{

            // 更新商品状态
            $status = $act == 'stao' ? 2 : 0;
            if($this->Model->where($where)->save(array('status' => $status))){
                $this->success('商品状态更改成功！', U('Mall/Mgoods/init'));
            }
        }
    }

    // 列表页
    private function _list($where){

        // 查询 - 只显示没删除的
        $where['is_delete'] = 1;

        // 分页数目
        $pageSize = 3;

        // 数据总数
        $count = $this->Model->where($where)->count();
        if($count > 0){

            // 导入分页类
            $Page = new \Think\StorePage($count, $pageSize);

            // 分页HTML 总条数
            $htmlPage = $Page->show();
			$htmlPage = str_replace('mall.yishu.com/Mall/','www.yishu.com/guwan/',$htmlPage);
            $this->htmlPage = $htmlPage;
            $this->count = $count;

            // 根据分页显示信息
            $aryList = $this->Model->where($where)->order('goods_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
            empty($aryList) OR $this->aryList = $aryList;
        }

        $this->display('list');
    }

    // 添加信息之前选择类别[私有:不可直接地址访问]
    private function _addcate(){

        $aryCate = S('MallCategory');
        $jsonCate = '';
        foreach($aryCate as $rs){
            $jsonCate .= $rs['cate_id'].':{"id":"'. $rs['cate_id'] .'","pid":"'. $rs['parent_id'] .'","name":"'. $rs['cate_name'] .'",},';
        }

        $this->jsonCate = '{'.$jsonCate.'}';

        $this->display('addcate');
    }

    // 添加信息之完善信息[私有:不可直接地址访问]
    private function _addinfo($where){

        if(IS_POST){

            isset($_POST['cate_str']) AND $this->cateCrumb = str_replace(',',' &gt; ', $_POST['cate_str']);

            isset($_POST['cate_id']) AND $cate_id = intval($_POST['cate_id']);
            isset($cate_id) OR $this->error('请先选择类别！');
            $this->cate_id = $cate_id;

            // 根据类别ID(cate_id)获取对应的属性值
            $aryAttr = $this->_aryAttr($cate_id);
            if($aryAttr){
                $this->attrVal = $aryAttr[1];
                $this->attrName = $aryAttr[0];
            }

            // 获取此类别下的品牌
            $arrBrand = $this->_aryBrand($cate_id);
            empty($arrBrand) OR $this->arrBrand = $arrBrand;

            // 树形的店铺类别
            $storeCate = $this->storeCate;
            $storeCate = aryTree($storeCate);
            $this->treeStoreCate = json_encode($storeCate);

            // 售后服务 & 鉴定证书 & 认证标识
            $this->htmlGoodsService = $this->Model->tagCheckbox('goods_service');
            $this->htmlGoodsCertificate = $this->Model->tagCheckbox('goods_certificate');
            $this->htmlGoodsAttest = $this->Model->tagCheckbox('goods_attest');

            // 自定义类别
            $storeCate = D('StoreCategory')->aryStoreCate($where['store_id']);
            if(!empty($storeCate)){

                // 创建顶级类别控件
                $this->tagPcateSelect = D('StoreCategory')->goods2storeCate($storeCate, 0, 0, 'p_cate');

                // JS级联数组
                $this->jsStoreCate = json_encode($storeCate);
            }

            // 自定义品牌
            $aryBrand = D('Brand')->aryBrand($where['store_id']);
            $tagSelect = D('Brand')->tagSelect($aryBrand);
            empty($tagSelect) OR $this->tagSelect = $tagSelect;

            // 配送方式
            $aryDelivery = D('Delivery')->aryDelivery($where['store_id']);
            $tagDeliverySelect = D('Delivery')->tagSelect($aryDelivery);
            empty($tagDeliverySelect) OR $this->tagDeliverySelect = $tagDeliverySelect;

            $this->display('addinfo');
        }
    }

    // 添加信息之入库操作[私有:不可直接地址访问]
    private function _adddoit($where){

        if(IS_POST){

            // 店铺ID
            $_POST['store_id'] = $where['store_id'];

            // 组图
            isset($_POST['pics']) AND $picsUrl = $_POST['pics'];

            // 默认图
            $_POST['default_img'] = isset($picsUrl) ? $picsUrl[0] : '';

            // 店铺自定义类别
            isset($_POST['p_cate']) AND $p_cate = intval($_POST['p_cate']);
            isset($_POST['s_cate']) AND $s_cate = intval($_POST['s_cate']);
            $_POST['store_cate'] = (isset($p_cate) && $p_cate > 0) ? ((isset($s_cate) && $s_cate > 0) ? $s_cate : $p_cate) : 0;

            // 数据比对，构建入库数组
            $data = $this->Model->create();

            if($data){

                // 1.基本产品信息入库
                $inId = $this->Model->add();

                if ($inId !== false) {

                    // 2.产品属性入库
                    isset($_POST['attr']) AND $arrAttr = $_POST['attr'];
                    isset($_POST['val']) AND $arrVal = $_POST['val'];
                    if($arrAttr && $arrVal){
                        foreach($arrAttr as $k => $rs){
                            $dataAttr[] = array('attr_id'=>$rs, 'val_id'=>$arrVal[$k], 'goods_id'=>$inId);
                        }
                        unset($arrAttr, $arrVal, $rs, $k);

                        // 执行批量插入[addAll]
                        D('GoodsAttr')->addAll($dataAttr);
                    }

                    // 3.产品组图入库
                    if(isset($picsUrl) && !empty($picsUrl)){
                        foreach($picsUrl as $k => $rs){
                            $dataPics[] = array('img_url'=>$rs, 'img_name'=>'', 'listorder' => ($k+1), 'goods_id'=>$inId);
                        }
                        unset($picsUrl, $rs, $k);

                        // 执行批量插入[addAll]
                        D('GoodsImage')->addAll($dataPics);
                    }

                    // 4.静态数据[浏览量,被收藏数,被评论数,销售数等]的入库
                    D('GoodsCount')->add(array('view'=>0,'collect'=>0,'sales'=>0,'comment'=>0,'goods_id'=>$inId));

                    // 6.提示信息
                    $this->success('添加成功！', U('Mall/Mgoods/init'));

                } else {

                    $this->error('添加失败！');
                }

            } else {
                $this->error($this->Model->getError());
            }
        }
    }

    // 编辑信息之入库操作[私有:不可直接地址访问]
    private function _edit($where){

        if(IS_POST){

            // 店铺ID
            $where['goods_id'] = $_POST['goods_id'];

            // 组图
            isset($_POST['pics']) AND $picsUrl = $_POST['pics'];

            // 缩略图
            $_POST['default_img'] = isset($picsUrl) ? $picsUrl[0] : '';

            // 店铺自定义类别
            isset($_POST['p_cate']) AND $p_cate = intval($_POST['p_cate']);
            isset($_POST['s_cate']) AND $s_cate = intval($_POST['s_cate']);
            $_POST['store_cate'] = (isset($p_cate) && $p_cate > 0) ? ((isset($s_cate) && $s_cate > 0) ? $s_cate : $p_cate) : 0;

            // 数据比对，构建入库数组
            $data = $this->Model->create();

            if($data){

                // 1.基本产品信息入库
                $bool = $this->Model->where($where)->save();

                if ($bool !== false) {

                    // 当前产品ID
                    $inId = $where['goods_id'];

                    // 2.产品属性入库
                    isset($_POST['attr']) AND $arrAttr = $_POST['attr'];
                    isset($_POST['val']) AND $arrVal = $_POST['val'];
                    if($arrAttr && $arrVal){
                        foreach($arrAttr as $k => $rs){
                            $dataAttr[] = array('attr_id'=>$rs, 'val_id'=>$arrVal[$k], 'goods_id'=>$inId);
                        }
                        unset($arrAttr, $arrVal, $rs, $k);

                        // 先删除旧的属性
                        D('GoodsAttr')->where(array('goods_id' => $inId))->delete();

                        // 执行批量插入[addAll]
                        D('GoodsAttr')->addAll($dataAttr);
                    }

                    // 3.产品组图入库
                    if(isset($picsUrl) && !empty($picsUrl)){
                        foreach($picsUrl as $k => $rs){
                            $dataPics[] = array('img_url'=>$rs, 'img_name'=>'', 'listorder' => ($k+1), 'goods_id'=>$inId);
                        }
                        unset($picsUrl, $rs, $k);

                        // 先删除旧的组图
                        D('GoodsImage')->where(array('goods_id' => $inId))->delete();

                        // 执行批量插入[addAll]
                        D('GoodsImage')->addAll($dataPics);
                    }

                    // 6.提示信息
                    $this->success('编辑成功！', U('Mall/Mgoods/init'));

                } else {

                    $this->error('添加失败！');
                }

            } else {
                $this->error($this->Model->getError());
            }

        }else{

            isset($_GET['id']) AND $id = intval($_GET['id']);
            isset($id) OR $this->error('无效信息');
            $where['goods_id'] = $id;

            $data = $this->Model->where($where)->find();

            if($data){

                $this->data = $data;

                // 根据类别ID(cate_id)获取对应的属性值
                $aryAttr = $this->_aryAttr($data['cate_id']);
                if($aryAttr){
                    $this->attrVal = $aryAttr[1];
                    $this->attrName = $aryAttr[0];
                }

                // 获取当前属性
                $aryThenAttr = D('GoodsAttr')->field('attr_id, val_id')->where(array('goods_id'=>$data['goods_id']))->select();
                if($aryThenAttr){
                    foreach($aryThenAttr as $rs){
                        $ary[$rs['attr_id']] = $rs['val_id'];
                    }
                    $this->aryThenAttr = $ary;
                }

                // 获取品牌
                $aryBrand = D('Brand')->aryBrand($data['store_id']);
                $tagSelect = D('Brand')->tagSelect($aryBrand, $data['brand_id']);
                empty($tagSelect) OR $this->tagSelect = $tagSelect;

                // 配送方式
                $aryDelivery = D('Delivery')->aryDelivery($data['store_id']);
                $tagDeliverySelect = D('Delivery')->tagSelect($aryDelivery, $data['delivery_id']);
                empty($tagDeliverySelect) OR $this->tagDeliverySelect = $tagDeliverySelect;

                // 获取组图
                $aryPics = D('GoodsImage')->field('img_url')->where(array('goods_id'=>$data['goods_id']))->order('listorder ASC')->select();
                empty($aryPics) OR $this->aryPics = $aryPics;

                // 售后服务 & 鉴定证书 & 认证标识
                $this->htmlGoodsService = $this->Model->tagCheckbox('goods_service', $data['goods_service']);
                $this->htmlGoodsCertificate = $this->Model->tagCheckbox('goods_certificate', $data['goods_certificate']);
                $this->htmlGoodsAttest = $this->Model->tagCheckbox('goods_attest', $data['goods_attest']);

                // 自定义类别
                $storeCate = D('StoreCategory')->aryStoreCate($where['store_id']);
                $store_cate = $data['store_cate'];
                if(!empty($storeCate) && !empty($store_cate)){

                    // 检测是否有顶级类别
                    foreach($storeCate as $rs){
                        if($rs['id'] == $store_cate){
                            $pid = $rs['pid']; break;
                        }
                    }

                    // 创建顶级类别控件
                    $tmpId = $pid > 0 ? $pid : $store_cate;
                    $this->tagPcateSelect = D('StoreCategory')->goods2storeCate($storeCate, 0, $tmpId, 'p_cate');

                    // 创建二级类别控件
                    empty($pid) OR $this->tagScateSelect = D('StoreCategory')->goods2storeCate($storeCate, $pid, $store_cate, 's_cate');

                    // JS级联数组
                    empty($storeCate) OR $this->jsStoreCate = json_encode($storeCate);
                }

                $this->display('edit');

            }else{

                $this->error('无效信息');
            }
        }
    }

    // 根据类别获取品牌值[私有]
    private function _aryBrand($cate_id){
        return D('Brand')->field(array('brand_id','brand_name'))->where(array('cate_id'=>$cate_id))->select();
    }

    // 根据类别获取属性值[私有]
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