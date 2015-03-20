<?php

// +----------------------------------------------------------------------
// | 新闻模块_新闻信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace News\Controller;

use News\Controller\NewsController;

class IndexController extends NewsController
{
    public function _initialize()
    {

        parent::_initialize();
		$auth = getLoginStatus();
        $this->assign('auth', $auth);

		$this->param='index';

	    $this->title="中国艺术网资讯中心-聚焦艺术新闻,随时随地掌握第一手艺术资讯";
        $this->keywords="中国艺术网资讯,中国艺术网资讯中心,艺术资讯,艺术新闻";
        $this->desc="中国艺术网资讯中心为艺术爱好者提供最新每日艺术动态、收藏知识、拍卖新闻、展览动态、人文艺术等信息,打造最专业最及时的艺术资讯平台,随时随地掌握第一手艺术资讯。";
    }
    /**
     * 新闻首页
     */
    public function index()
    {

		$news_field=array(
            'news_id',//id
            'news_name',//新闻标题名字
            'news_summary',//新闻摘要
            'news_desc',//新闻内容
            'news_author',//新闻作者
            'news_source',//新闻来源
			'news_arrposid',//新闻推荐位
            'news_type',//新闻类型
			'news_url',//新闻链接
			'news_createtime',//记录创建时间
            'recordid',
            );
        $news_where=array(
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );
      //推荐位----------------------------------------------------------------------------------------------
        //筛选是新闻头条推荐位
        $news_recommend_where=array(
            'news_arrposid'=>4,//推荐位
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );
        //获取新闻头条推荐位数据 
        $recommend_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(1)->where($news_recommend_where)->select();
		//循环获取新闻头条图片
        foreach ($recommend_array as $k => $v) {
			$recommend_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}

		//头条新闻推荐位
        $this->first_news=$recommend_array[0];

		//p($this->first_news);
        
        //筛选是百叶窗推荐位
        $news_img_where=array(
            'news_arrposid'=>3,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取百叶窗数据
        $news_img = M("NewsList")->field($news_field)->where($news_img_where)->order('news_createtime desc')->limit(5)->select();
		//echo  M("NewsList")->getLastSql();exit;
        //循环百叶窗图片
        foreach ($news_img as $k => $v) {
			$news_img[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        $this->news_imgs=$news_img;

		//p($this->news_imgs);

       //根据要闻推荐位
        $yaowen_where=array(
            'news_arrposid'=>5,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取要闻推荐位数据 
        $yaowen_array = M("NewsList")->field($news_field)->order('news_createtime desc')->where($yaowen_where)->limit(10)->select();
      
        //第一条要闻推荐位
        $this->second_news=$yaowen_array[0];
        //除了第一条要闻推荐位
        $this->other_news=array_slice($yaowen_array,1);

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
		//p($this->other_tupian_news);

     //推荐位end--------------------------------------------------------------------------------------------

        //根据新闻类型筛选->1:行业热点
        $hangye_where=array(
            'news_type'=>1,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的行业热点数据 
        $hangye_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(9)->where($hangye_where)->select();

        //根据新闻类型筛选->2:拍卖新闻
        $paimai_where=array(
            'news_type'=>2,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的拍卖新闻数据 
        $paimai_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(9)->where($paimai_where)->select();


        //根据新闻类型筛选->3:名家大师
        $dashi_where=array(
            'news_type'=>3,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的名家大师数据 
        $dashi_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(5)->where($dashi_where)->select();

        //根据新闻类型筛选->4:展览动态
        $zhanlan_where=array(
            'news_type'=>4,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的展览动态数据 
        $zhanlan_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(9)->where($zhanlan_where)->select();

        //根据新闻类型筛选->5:艺术趣闻
        $quwen_where=array(
            'news_type'=>5,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的艺术趣闻数据 
        $quwen_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(9)->where($quwen_where)->select();

        //根据新闻类型筛选->6:人文艺术
        $renwen_where=array(
            'news_type'=>6,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的人文艺术数据 
        $renwen_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(9)->where($renwen_where)->select();

        //根据新闻类型筛选->8:独家专题
        $dujia_where=array(
            'news_type'=>8,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的独家专题数据 
        $dujia_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(2)->where($dujia_where)->select();


        //循环获取行业热点图片
        foreach ($hangye_array as $k => $v) {
			$hangye_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        //循环获取拍卖新闻图片
        foreach ($paimai_array as $k => $v) {
			$paimai_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        //循环获取展览动态图片
        foreach ($zhanlan_array as $k => $v) {
			$zhanlan_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        //循环获取艺术趣闻图片
        foreach ($quwen_array as $k => $v) {
			$quwen_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        //循环获取人文艺术图片
        foreach ($renwen_array as $k => $v) {
			$renwen_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
		//循环获取独家专题图片
        foreach ($dujia_array as $k => $v) {
			$dujia_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        //循环获取名家大师图片
        foreach ($dashi_array as $k => $v) {
			$dashi_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}


        //第一条人文艺术
        $this->first_renwen_news=$renwen_array[0];
		//除了第一条其它的人文艺术
        $this->other_renwen_news=array_slice($renwen_array,1);

        //第一条艺术趣闻
        $this->first_quwen_news=$quwen_array[0];
		//除了第一条其它的艺术趣闻
        $this->other_quwen_news=array_slice($quwen_array,1);

        //第一条展览动态
        $this->first_zhanlan_news=$zhanlan_array[0];
		//除了第一条其它的展览动态
        $this->other_zhanlan_news=array_slice($zhanlan_array,1);

		//所有独家新闻
        $this->other_dujia_news= $dujia_array;

        //第一条名家大师
        $this->first_dashi_news=$dashi_array[0];
		//除了第一条其它的名家大师
        $this->other_dashi_news=array_slice($dashi_array,1);

        //第一条拍卖新闻
        $this->first_paimai_news=$paimai_array[0];
		//除了第一条其它的拍卖新闻
        $this->other_paimai_news=array_slice($paimai_array,1);

        //第一条行业热点
        $this->first_hangye_news=$hangye_array[0];
		//除了第一条其它的行业热点
        $this->other_hangye_news=array_slice($hangye_array,1);

        // p( $this->other_news);

	#收藏知识
		$shoucang=new \Home\Model\DocumentModel();
		//getCollectionKnow这个方法中  三个参数，第一个参数不用管，第二个参数为要取的条数，第三个参数为是否推荐 1为推荐的0为不推荐的
		$shoucang_arr = $shoucang->getCollectionKnow('',9,1);

	   //p($shoucang_arr);
       $this->first_shoucang_news=$shoucang_arr[0];
       $this->other_shoucang_news=array_slice($shoucang_arr,1);

    #特卖专场----------------------------------------------------------------------------------------------
        //专场字段
        $special_field=array(
            'special_id',
            'special_name',
            'special_starttime',
            'special_endtime',
            'recordid',
            );
        //专场条件
        $special_where=array(
            'special_isshow'=>1,
            'special_isdelete'=>0,
            'special_endtime'=>array("GT",time()),
            'special_starttime'=>array("LT",time()),
            );
        //取出个数
        $special_limit=4;
        $special_arr=M('PaimaiSpecial')->field($special_field)->where($special_where)->limit($special_limit)->order("special_order desc,special_id desc")->select();
        foreach ($special_arr as &$v) {
            //得到本专场下面商品的竞拍次数,本应该去重，这里没有去重
            $v['goods_bidtimes']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_bidtimes");
            //得到本专场下面商品的个数
            $v['goods_count']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->count();
            //专场图片
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        $this->special_arr=$special_arr;

	  #特卖专场end----------------------------------------------------------------------------------------------
        //p($special_arr);
		$this->display("Front:index");

    }
}
