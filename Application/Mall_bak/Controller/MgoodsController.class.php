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
    }

    // 初始化入口
    public function init(){

        // 操作方式
        $act = isset($_GET['act']) ? trim($_GET['act']) : 'list';
        in_array($act, $this->aryAct) OR $this->error('不合法URL');

        // 操作函数
        $act == 'list' AND $this->_list($this->where);
        $act == 'addcate' AND $this->_addcate();
        $act == 'addinfo' AND $this->_addinfo();
        $act == 'adddoit' AND $this->_adddoit($this->where);
        $act == 'edit' AND $this->_edit($this->where);
        $act == 'status' AND $this->_status($this->where);
    }

    // 更改信息状态
    private function _status($where){

        // 预定义状态的取值范围
        $aryStatus = array(0, 2);

        // 获取ID
        isset($_GET['id']) AND $id = intval($_GET['id']);
        if(empty($id)) die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效信息')) .')');
        $where['goods_id'] = $id;

        // 获取状态
        $val = isset($_GET['val']) ? intval($_GET['val']) : 0;
        in_array($val, $aryStatus) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效状态')) .')');
        $data['status'] = $val;

        // 更新状态
        if($this->Model->where($where)->data($data)->save()){
            $msg = $val == 2 ? '正常' : '下架';
            echo trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>$msg,'id'=>$id)) .')';
        }
    }

    // 列表页
    private function _list($where){

        // 状态
        isset($_GET['status']) AND $where['status'] = intval($_GET['status']);

        // 查询
        $aryList = $this->Model->where($where)->select();

        empty($aryList) OR $this->aryList = $aryList;
        $this->display('list');
    }

    // 添加信息之选择类别[私有:不可直接地址访问]
    private function _addcate(){

        $this->display('addcate');
    }

    // 添加信息之完善信息[私有:不可直接地址访问]
    private function _addinfo(){

        if(IS_POST){

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

            $this->display('addinfo');
        }
    }

    // 添加信息之入库操作[私有:不可直接地址访问]
    private function _adddoit($where){

        if(IS_POST){

            // 店铺ID
            $_POST['store_id'] = $where['store_id'];

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
                        M('MallGoodsAttr')->addAll($dataAttr);
                    }

                    // 3.产品组图入库
                    isset($_POST['pics']['url']) AND $picsUrl = $_POST['pics']['url'];
                    isset($_POST['pics']['name']) AND $picsName = $_POST['pics']['name'];
                    if((isset($picsUrl) && !empty($picsUrl)) && (isset($picsName) && !empty($picsName))){
                        foreach($picsUrl as $k => $rs){
                            $dataPics[] = array('img_url'=>$rs, 'img_name'=>$picsName[$k], 'goods_id'=>$inId);
                        }
                        unset($picsUrl, $picsName, $rs, $k);
                        // 执行批量插入[addAll]
                        M('MallGoodsImage')->addAll($dataPics);
                    }

                    // 4.静态数据[浏览量,被收藏数,被评论数,销售数等]的入库
                    M('MallGoodsCount')->add(array('view'=>0,'collect'=>0,'sales'=>0,'comment'=>0,'goods_id'=>$inId));

                    // 5.如果有前端已经上传的组图，但删除未入库的垃圾图片需要删除，以节省空间
                    (isset($_POST['removePic']) && !empty($_POST['removePic'])) AND $removePics = trim($_POST['removePic']);
                    if(isset($removePics) && !empty($removePics)){
                        $arrRemPics = explode(',', $removePics);
                        foreach($arrRemPics as $rs){
                            $img = trim($rs, '/');
                            unlink($img);
                        }
                    }

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

            // 数据比对，构建入库数组
            $data = $this->Model->create();

            if ($this->Model->where($where)->save($data)) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
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

                // 获取此类别下的品牌
                $arrBrand = $this->_aryBrand($data['cate_id']);
                empty($arrBrand) OR $this->arrBrand = $arrBrand;

                // 获取组图
                $this->aryPics = D('GoodsImage')->where(array('goods_id'=>$data['goods_id']))->select();
            }

            $this->display('edit');
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