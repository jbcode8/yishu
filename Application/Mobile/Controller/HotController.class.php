<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Mobile\Controller;

use Mobile\Controller\MobileController;

class HotController extends MobileController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->title="热门拍品";
        $this->keywords="热门拍品";
        $this->desc="热门拍品";
    }
    /**
     * 拍卖wap hot页面
     */
    public function index()
    {

        $hotgoods_field=array(
            "goods_id",
            "goods_name",
            "goods_bidtimes",
            "recordid",
            "goods_nowprice",
            );
        $hotgoods_where=array(
            'goods_isshow'=>1,
            'goods_isdelete'=>0,
            'goods_endtime'=>array("GT",time()),
            'goods_starttime'=>array("LT",time()),
            );
        //取出个数
        $hotgoods_limit=10;
        $hotgoods_arr=M('PaimaiGoods')->field($hotgoods_field)->where($hotgoods_where)->limit($hotgoods_limit)->order("goods_bidtimes desc,goods_id desc")->select();
        foreach ($hotgoods_arr as &$v) {
            //商品图片
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        $this->hotgoods_arr=$hotgoods_arr;

        $this->display("Front:hot");
    }
}
