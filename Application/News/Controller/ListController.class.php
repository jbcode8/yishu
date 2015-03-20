<?php

// +----------------------------------------------------------------------
// | 新闻模块_新闻信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace News\Controller;

use News\Controller\NewsController;

class ListController extends NewsController
{
    public function _initialize()
    {

        //接受参数
        $param=I('param','','strip_tags');
        $type_where=array(
            'category_spell'=>$param,
            );
        $type_arr=M('NewsCategory')->where($type_where)->find();
        $type_id=$type_arr['category_id'];
        $this->type_id = $type_id;
        $this->param = $param;

        //404页面，因为tp路由不到404，只能在这里定义
        if(empty($type_id)){
            header("Location: http://www.yishu.com/search/?m=content&c=index&a=error_404&model=paimai"); 
            exit();
        }
          //页面获取
        $where=array(
            'category_id'=>$type_id,
            );

        $seo_array = M("NewsCategory")->field('*')->where($where)->select();

       // p($seo_array);

	    $this->title = $seo_array[0]['seo_title'];
        $this->keywords = $seo_array[0]['seo_keys'];
        $this->desc = $seo_array[0]['seo_desc'];



		parent::_initialize();
    }
    /**
     * 新闻列表页
     */
    public function index()
    {
     
        //接受参数
        $param=I('param','','strip_tags');
        $type_where=array(
            'category_spell'=>$param,
            );
        $type_arr=M('NewsCategory')->where($type_where)->find();
        $type_id=$type_arr['category_id'];
        $this->type_id = $type_id;
        
        if(empty($type_id)){
            $type_id=empty($type_id)&&$param=="toutiao"?11:null;
            $type_id=empty($type_id)&&$param=="tupian"?7:$type_id;
            $type_id=empty($type_id)&&$param=="yaowen"?12:$type_id;
            $type_id=empty($type_id)&&$param=="tuijian"?10:$type_id;
        }
        //404页面，因为tp路由不到404，只能在这里定义
        if(empty($type_id)){
            header("Location: http://www.yishu.com/search/?m=content&c=index&a=error_404&model=paimai"); 
            exit();
        }
          //页面获取

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
            'recordid',
            );
        //获取不同的文章列表
       $where=array(
            'news_type'=>$type_id,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

	    //分页--------------------------------------------------------------------------------------
		$p = I('p',1,'intval');
        //$this->p=$p;
		//每页显示的数量
        $prePage = 10;
        $news_fenye=M('NewsList')->field('*')->order("news_id desc")->page($p . ',' . $prePage)->where($where)->select();

		foreach ($news_fenye as $k => $v) {
			$news_fenye[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
		}

		$this->news_fenye =  $news_fenye;
        //echo M('NewsList')->getLastSql();exit;
        //p($this->news);
		//分页商品总数
		$total_num=M("NewsList")->where($where)->count();
        
		$Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        $show = $Page->show(); // 分页显示输出

        $suffix=$_SERVER['QUERY_STRING'];
        //去除开头两个字符串,并替换&&为?
        $suffix=str_replace("&&","?&",substr($suffix,3));
        //去除分页 p=3中的数字
        $suffix=preg_replace("/&p=(\d+)*/","",$suffix);
        $show=preg_replace("/(.*)News\/List\/index(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);
		//p($this->r);
	   $this->assign('page', $show); // 赋值分页输出
       //分页---------------------------------------------------------------------------------------

	   $where_recommend=array(
            'news_type'=>$type_id,
		    'news_recommend'=>1,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );
	    //获取不是推荐所有文章信息
        //$news_type_array = M("NewsList")->field($news_field)->order('news_createtime desc')->where($where)->select();

        //获取推荐所有文章信息
        $news_type_recommend = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(1)->where($where_recommend)->select();


	    foreach ($news_type_recommend as $k => $v) {
			$news_type_recommend[$k]['thumb'] =  D('Content/Document')->getPic($v['recordid'], 'thumb');
		}
       //所有新闻
        /*$this->news=$news_type_array;*/
       //第一条推荐新闻
		$this->new_recommend=$news_type_recommend[0];
		//p($this->news);

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

		//头条新闻
       $top_where=array(
            'news_type'=>11,
            'news_isshow'=>0,
            'news_isdelete'=>0,
            );

        //获取推荐的图片新闻数据 
        $top_array = M("NewsList")->field($news_field)->order('news_createtime desc')->limit(4)->where($top_where)->select();

        foreach ($top_array as $k => $v) {
			$top_array[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}

        $this->top_array= $top_array;

		//p($this->top_array);
       //头条新闻end

        $this->tupian_array= $tupian_array;
	    $this->hangye_array= $hangye_array;
		$this->paimai_array= $paimai_array;
		$this->dashi_array= $dashi_array;
		$this->zhanlan_array= $zhanlan_array;

       
		$this->display("Front:list");

    }
}
