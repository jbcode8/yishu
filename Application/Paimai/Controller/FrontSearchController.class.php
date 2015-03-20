<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Paimai\Controller;

use Paimai\Controller\PaimaiPublicController;

class FrontSearchController extends PaimaiPublicController
{

    public function _initialize()
    {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
		

		#接受关键词，并过滤
        $keyword=I('keyword','','strip_tags');

        $this->title="{$keyword}拍卖_{$keyword}价格_{$keyword}图片_{$keyword}在线拍卖_中国艺术网在线竞拍";
        $this->keywords="{$keyword}拍卖,{$keyword}价格,{$keyword}图片,{$keyword}在线拍卖";
        $this->desc="中国艺术网在线竞拍{$keyword}拍卖专场为用户提供最新{$keyword}图片,{$keyword}价格,{$keyword}在线拍卖信息，高端的{$keyword}拍卖专场，每天带来最新的{$keyword}图片和{$keyword}价格信息，权威机构 ，值得信赖。
";
        //$this->assign('title', '中国艺术网-拍卖');
    }

    /**
     * 关键词搜索
     *by:
     */
    public function index()
    {

    #接受关键词，并过滤
        $keyword=I('keyword','','strip_tags');
        //p($keyword);
        //分配关键词给模板
        $this->keyword=$keyword;

	#查询数据库
        //字段field，根据需要添加字段
        $sogoods_field=array(
            'goods_id',//id
            'goods_name',//商品名字
            'goods_nowprice',//商品现价
            'goods_bidtimes',//商品竞拍次数
            'goods_starttime',//商品开拍时间
            'goods_endtime',//商品结束时间
            'recordid',
            );
        //条件where
        $sogoods_where=array(
            'goods_name'=>array('LIKE',array("%$keyword%")),
            'goods_isshow'=>1,
            'goods_isdelete'=>0,
            );

        $gswhere=array(
            'goods_isshow'=>1,
            'goods_isdelete'=>0,
            'goods_endtime'=>array("GT",time()),
            'goods_starttime'=>array("LT",time()),
            );

        //查询数据库，返回数组
        $sogoods_arr = 
		M("PaimaiGoods")->field($sogoods_field)->where($sogoods_where)->order('goods_endtime desc')->limit(100)->select();
        //加上Limit是为了提速
		//获取猜你喜欢的数据
        $goodsarray = 
		M("PaimaiGoods")->field($sogoods_field)->order('goods_endtime desc')->limit(4)->where($gswhere)->select();

        $sogoods_jin = array();   //正在进行中数据组
        $sogoods_going = array();  //即将开始数据组
        $sogoods_over = array();  //已经结束数据组

        foreach($sogoods_arr as $v){
			//判断正在进行中的， 商品结束时间 > 当前时间  &&   商品开拍时间  < 当前时间
			if($v['goods_endtime'] > time() && $v['goods_starttime'] < time()){
				$sogoods_jin[] = $v;				
			} 			
		}

        foreach($sogoods_arr as $v){
			 //判断即将开始的， 商品开拍时间 > 当前时间 &&  商品结束时间 > 当前时间
			if($v['goods_starttime'] > time() && $v['goods_endtime'] > time()){
				$sogoods_going[] = $v;				
			} 			
		}

		foreach($sogoods_arr as $v){
			 //判断已经结束的，当前时间  >=  商品结束时间 && 当前时间 >  商品开拍时间
			if(  time() >= $v['goods_endtime'] && time() > $v['goods_starttime'] ){
				$sogoods_over[] = $v;				
			} 			
		}
      
        //循环获取全部图片
        foreach ($sogoods_arr as $k => $v) {
			$sogoods_arr[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
		 }

        //循环获取正在进行中的图片
        foreach ($sogoods_jin as $k => $v) {
			$sogoods_jin[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
		 }

        //循环获取即将开始的图片
        foreach ($sogoods_going as $k => $v) {
			$sogoods_going[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
		 }

        //循环获取已经结束的图片
        foreach ($sogoods_over as $k => $v) {
			$sogoods_over[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
		 }

		//循环获取猜你喜欢的图片
        foreach ($goodsarray as $k => $v) {
			$goodsarray[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
		 }

		//p($goodsarray); 

        $this->goodsings = $sogoods_jin; //正在进行中的数据
        $this->goodgoing = $sogoods_going; //即将开始的数据
        $this->goodover = $sogoods_over; //已经结束的数据
		$this->goods = $sogoods_arr;   //全部数据
		$this->goodsarray = $goodsarray;//猜你喜欢的数据
		
              
    #分配模板
        //exit("1");
        $this->display('Front:search4');
    }

}
