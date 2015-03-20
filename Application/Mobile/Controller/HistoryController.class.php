<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Mobile\Controller;

use Mobile\Controller\MobieController;

class HistoryController extends MobileController
{
    public function _initialize()
    {
        parent::_initialize();
		$this->title="预拍/历史";
        $this->keywords="预拍/历史";
        $this->desc="预拍/历史";
    }
    /**
     * 拍卖wap首页
     */
    public function index()
    {

        //今天晚上22点时间戳
        $today_fixtime=strtotime(date("Y-m-d", time()))+3600*22;
        //今天早上0点时间戳
        $today_starttime=strtotime(date("Y-m-d", time()));
        //今天晚上23点59分时间戳
        $today_endtime=strtotime(date("Y-m-d", time()))+84600-1;


        $goods_field=array(
            "goods_id",
            "goods_name",
            "recordid",
            "goods_nowprice",
            "goods_hits",
            "goods_starttime",
            "goods_endtime",
            );
        $goods_where=array(
            "goods_isshow"=>1,
            "goods_isdelete"=>0,
            );

    #即将开始
        //即将开始where
        $startgoods_where=$goods_where;
        $startgoods_where['goods_starttime']=array("between",array($today_starttime,$today_endtime));

        $startgoods_limit=4;
        $startgoods_arr=M('PaimaiGoods')->field($goods_field)->where($startgoods_where)->limit($startgoods_limit)->order("goods_id desc")->select();
        foreach ($startgoods_arr as &$v) {
            //商品图片
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        $this->assign('startgoods_arr',$startgoods_arr);

    #过去场次
        //过去场次where
        $endgoods_where=$goods_where;
        $endgoods_where['goods_endtime']=array("lt",time());
		$endgoods_where['goods_status'] = 3;
        
        $endgoods_limit=4;

        $endgoods_arr=M('PaimaiGoods')->field($goods_field)->where($endgoods_where)->limit($endgoods_limit)->order("goods_id desc")->select();
        //echo M('PaimaiGoods')->getLastSql();exit;
        foreach ($endgoods_arr as &$v) {
            //商品图片
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        
        $this->endgoods_arr=$endgoods_arr;
		$this->display("Front:history");
    }
}
