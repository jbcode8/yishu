<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 商品详细 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class DetailController extends FpublicController {

    public function _initialize(){

        parent::_initialize();

        $this->Goods = D('Goods');
    }

    // 首页
    public function index() {

        // 检测信息ID是否存在
        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) OR $this->error('信息不存在或者已经被删除');
        $where['goods_id'] = $id;

        // 数据库读取信息
        $info = $this->Goods->where($where)->find();
        empty($info) AND $this->error('信息不存在或者已经被删除');
        $this->info = $info;

        // 获取属性值
        $aryGoodsAttr = aryGoodsAttr($info['goods_id']);
        empty($aryGoodsAttr) OR $aryAttrVal = aryAttrVal($aryGoodsAttr);
        empty($aryGoodsAttr) OR $aryAttrName = aryAttrName($aryGoodsAttr);

        // 获取产品图片
        $aryGoodsImage = aryGoodsImage($info['goods_id']);

        // 获取产品的静态值
        $aryGoodsCount = aryGoodsCount($info['goods_id']);

        // 获取品牌名
        $brandName = getBrandInfo($info['brand_id'], 'brand_name');

        // 获取支付方式
        $brandName = getBrandInfo($info['brand_id'], 'brand_name');

        // 店铺名称
        $storeName = getStoreName($info['store_id']);

        // 类别名
        $aryCate = D('Category')->getParentCategory($info['cate_id']);

        echo $_SESSION['user_auth']['uid'];

        // SEO信息
        $seo['title'] = '古玩城';
        $this->assign('seo', $seo);

        addHistory($id);

        $this->display();
    }

    public function ajax(){

        // 检测信息ID
        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');
        $where['goods_id'] = $id;

        // 收藏类型: 0为产品，1为店铺
        $type = max(intval($_GET['type']), 1);

        // 数据库检测
        $data = D('Goods')->field('goods_name')->where($where)->find();
        empty($data) AND die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');
        $collect_title = $data['goods_name'];
        unset($where, $data);

        // 检测是否为登录用户
        if(isset($_SESSION['user_auth']['uid']) && $_SESSION['user_auth']['uid'] > 0){
            $uid = $_SESSION['user_auth']['uid'];
        }else{
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请先登录后再收藏')) .')');
        }

        // 判断是否已经加入
        $where['uid'] = $uid;
        $where['collect_type'] = $type;
        $where['item_id'] = $id;
        $data = D('Collect')->field('collect_id')->where($where)->find();
        empty($data) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'您已经收藏过了')) .')');
        unset($where, $data);

        // 加入收藏表
        $data['collect_type'] = $type;
        $data['collect_title'] = $collect_title;
        $data['item_id'] = $id;
        $data['uid'] = $uid;
        $data['create_time'] = time();
        $bool = D('Collect')->add($data);

        // 加入成功后，更新产品静态信息表的收藏数
        if($bool){

            // 更新产品静态信息表
            D('GoodsCount')->where(array('goods_id' => $id))->setInc('collect');

            // 返回成功信息
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>'收藏成功')) .')');

        }else{

            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'收藏失败')) .')');
        }

    }
} 