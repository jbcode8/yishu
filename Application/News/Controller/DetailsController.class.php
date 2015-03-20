<?php

// +----------------------------------------------------------------------
// | 新闻模块_新闻信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace News\Controller;

use News\Controller\NewsController;

class DetailsController extends NewsController
{
    public function _initialize()
    {
        
	   //接受参数
        $news_id=I('param',0,'intval');
	    $seo_where=array(
            'news_id'=>$news_id,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

		$auth = getLoginStatus();
        $this->assign('auth', $auth);
		$news_field=array(
            'news_id',
			'news_name',
			'news_summary',
			'news_desc',
            'seo_title',
            'seo_keys',
            'seo_desc',
            );
        $seo_array = M("NewsList")->field($news_field)->where($seo_where)->select();

		if(empty($seo_array[0]['seo_title'])){

        $this->title= $seo_array[0]['news_name']."_中国艺术网资讯中心(Yishu.com)";

		}else{

	    $this->title=$seo_array[0]['seo_title']."_中国艺术网资讯中心(Yishu.com)";

		}
		//p($seo_array);
        $this->keywords=$seo_array[0]['seo_keys'];

      if(empty($seo_array[0]['seo_title'])){

        $this->desc=$seo_array[0]['news_summary'];

	  }else{
  
        $this->desc=$seo_array[0]['seo_desc'];

      }
        parent::_initialize();
	
    }
    /**
     * 新闻详情页
     */
    public function index()
    {
        $news_id=I('param',0,'intval');

	    $where = array('news_id' => $news_id);
        $now= M("NewsList")->where($where)->getField('examine');			 
		$data['examine'] = $now + 1;
    	$exam = M("NewsList")->where($where)->save($data);
        
	$news_field=array(
            'news_id',//id
            'news_name',//新闻标题名字
            'news_summary',//新闻摘要
            'news_desc',//新闻内容
            'news_author',//新闻作者
            'news_source',//新闻来源
            'news_type',//新闻类型
			'news_url',//新闻链接
			'news_recommend',//是否推荐
			'news_createtime',//记录创建时间
			'news_rand',//随机获取10个ID
            'recordid',
            );
		//根据获取的ID
	   $details_where=array(
            'news_id'=>$news_id,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );
        $details_array = M("NewsList")->field($news_field)->where($details_where)->select();
		foreach ($details_array as $k => $v) {
			$details_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}


       //转格式
		$details_array[0]['news_desc'] = html_entity_decode($details_array[0]['news_desc'], ENT_QUOTES, 'UTF-8');

		$this->details_array=$details_array[0];
		//p($details_array);

        //根据新闻类型筛选->1:行业热点
        $hangye_where=array(
            'news_type'=>1,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的行业热点数据 
        $hangye_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(10)->where($hangye_where)->select();

  
        //根据新闻类型筛选->2:拍卖新闻
        $paimai_where=array(
            'news_type'=>2,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的拍卖新闻数据 
        $paimai_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(10)->where($paimai_where)->select();


        //根据新闻类型筛选->3:名家大师
        $dashi_where=array(
            'news_type'=>3,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的名家大师数据 
        $dashi_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(10)->where($dashi_where)->select();

        //根据新闻类型筛选->4:展览动态
        $zhanlan_where=array(
            'news_type'=>4,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的展览动态数据 
        $zhanlan_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(10)->where($zhanlan_where)->select();

 
		//获取图片新闻推荐位
        $tupian_where=array(
            'news_arrposid'=>2,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );
        //获取图片新闻推荐位数据 
        $tupian_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(4)->where($tupian_where)->select();

		//循环图片新闻推荐位图片
        foreach ($tupian_array as $k => $v) {
			$tupian_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
	    //所有图片新闻
		
        $this->other_tupian_news= $tupian_array;


	    $this->hangye_array= $hangye_array;
		$this->paimai_array= $paimai_array;
		$this->dashi_array= $dashi_array;
		$this->zhanlan_array= $zhanlan_array;

		//今日开拍------------------------------------------------------------------------------------------
		$sogoods_field=array(
            'goods_id',//id
            'goods_name',//商品名字
            'goods_nowprice',//商品现价
            'goods_bidtimes',//商品竞拍次数
            'goods_starttime',//商品开拍时间
            'goods_endtime',//商品结束时间
            'recordid',
            );
        $gswhere=array(
            'goods_isshow'=>1,
            'goods_isdelete'=>0,
            'goods_endtime'=>array("GT",time()),
            'goods_starttime'=>array("LT",time()),
            );
        $goodsarray = 
		M("PaimaiGoods")->field($sogoods_field)->limit(1)->where($gswhere)->select();

        foreach ($goodsarray as $k => $v) {
			$goodsarray[$k]['image'] = D('Content/Document')->getPic($v['recordid'], 'image',$v['goods_id']);
		 }

	   //$this->goodsarrays = $goodsarray;
	   $this->goodsarray = $goodsarray[0];

      //今日开拍 end---------------------------------------------------------------------------------------

	   //最底下5个-----------------------------------------------------------------------------------------

	   //根据新闻类型筛选->5:艺术趣闻
        $quwen_where=array(
            'news_type'=>5,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );
        //获取推荐的艺术趣闻数据 
        $quwen_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(10)->where($quwen_where)->select();
        foreach ($quwen_array as $k => $v) {
			$quwen_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        $this->first_quwen_news=$quwen_array[0];
		$this->other_quwen_news=array_slice($quwen_array,1,4);
        $this->five_quwen_news=$quwen_array[5];
		$this->end_quwen_news=array_slice($quwen_array,5,10);
        //根据新闻类型筛选->6:人文艺术
        $renwen_where=array(
            'news_type'=>6,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的人文艺术数据 
        $renwen_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(10)->where($renwen_where)->select();
        foreach ($renwen_array as $k => $v) {
			$renwen_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        $this->first_renwen_news=$renwen_array[0];
		$this->other_renwen_news=array_slice($renwen_array,1,4);
        $this->five_renwen_news=$renwen_array[5];
		$this->end_renwen_news=array_slice($renwen_array,5,10);

		//根据新闻类型筛选->8:独家专题
        $dujia_where=array(
            'news_type'=>8,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的独家专题数据 
        $dujia_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(10)->where($dujia_where)->select();
        foreach ($dujia_array as $k => $v) {
			$dujia_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        $this->first_dujia_news=$dujia_array[0];
		$this->other_dujia_news=array_slice($dujia_array,1,4);
        $this->five_dujia_news=$dujia_array[5];
		$this->end_dujia_news=array_slice($dujia_array,5,10);

        //收藏知识
		$shoucang=new \Home\Model\DocumentModel();
		//getCollectionKnow这个方法中  三个参数，第一个参数不用管，第二个参数为要取的条数，第三个参数为是否推荐 1为推荐的0为不推荐的
		$shoucang_arr = $shoucang->getCollectionKnow('',10,1);

	   //p($shoucang_arr);
		$this->first_shoucang_news=$shoucang_arr[0];
		$this->other_shoucang_news=array_slice($shoucang_arr,1,4);
        $this->five_shoucang_news=$shoucang_arr[5];
		$this->end_shoucang_news=array_slice($shoucang_arr,5,10);

       //延伸阅读
        $yanshen_where=array(
			'news_type'=>(rand(1,6)),
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的延伸阅读数据 
        $yanshen_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(10)->where($yanshen_where)->select();
        foreach ($yanshen_array as $k => $v) {
			$yanshen_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}


        $this->first_yanshen_news=$yanshen_array[0];
		$this->other_yanshen_news=array_slice($yanshen_array,1,4);
        $this->five_yanshen_news=$yanshen_array[5];
		$this->end_yanshen_news=array_slice($yanshen_array,5,10);


	   //最底下5个 END---------------------------------------------------------------------------------

       //上一篇数据----------------------------------------------------------------------------------

       $this->det = $details_array[0]['news_type'];
	   $front_where=array(
            'news_id'=>array("GT",$news_id),
		    'news_type' =>$this->det,
		    'news_isshow'=>0,
            'news_isdelete'=>0,
	    );
      $front_array = M("NewsList")->field($news_field)->order('news_id asc')->limit(1)->where($front_where)->select();
      $this->front_data = $front_array[0];

       //上一篇数据end-------------------------------------------------------------------------------

	   //下一篇数据----------------------------------------------------------------------------------  

	   $next_where=array(
            'news_id'=>array("LT",$news_id),
		    'news_type' =>$this->det,
		    'news_isshow'=>0,
            'news_isdelete'=>0,
	    );
       $next_array = M("NewsList")->field($news_field)->order('news_id desc')->limit(1)->where($next_where)->select();
       $this->next_data = $next_array[0];

		//下一篇数据end------------------------------------------------------------------------------

       //猜你喜欢 --拍卖专场---------------------------------------------------------------------------

	   $like_where=array(
		    'news_type' =>$this->det,
		    'news_isshow'=>0,
            'news_isdelete'=>0,
	    );
        $like = 
		M("NewsList")->field($news_field)->order('news_id desc')->limit(5)->where($like_where)->select();
		$this->like = $like;

        //猜你喜欢 --拍卖专场end---------------------------------------------------------------------------





        $this->display("Front:details");

    }
}
