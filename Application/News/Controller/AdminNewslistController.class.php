<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午5:45
 */

namespace News\Controller;


use Admin\Controller\AdminController;

class AdminNewslistController extends AdminController{
    /*新闻列表*/
    public function index(){

        //分页
		$p = I('p',1,'intval');
        $this->p=$p;
		//每页显示的数量
        $prePage = 12;
        $where=array(
            'news_isdelete'=>0,
            );
        $this->news=M('NewsList')->field('*')->order("news_id desc")->page($p . ',' . $prePage)->where($where)->select();

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
		//p($this->r);

		$this->assign('page', $show); // 赋值分页输出
        $this->display();
    }
    /*增加*/
    public function add(){
		//新闻栏目列表
        $this->category = M('NewsCategory')->field("category_id,category_name")->where("category_isshow=0")->order("category_id asc")->select();

        $this->display();
    }
    /**插入*/
    public function insert(){
        
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        //p($_POST);
        $news=D('NewsList');
        if($news->create()){

            //添加时间
            $news->recordid=I('recordid');

            $news->news_createtime=time();
			$rand_where=array(
				'news_isshow'=>0,
				'news_isdelete'=>0,
			);

		#随机产生10条新闻
			$rand_arr=M('NewsList')->field('news_id')->where($rand_where)->order('rand()')->limit(10)->select();
			$rand_str="";
			foreach($rand_arr as $k=>&$v){
				$rand_str=$v['news_id'].",".$rand_str;
			}
			$rand_str=substr($rand_str,0,-1);
			
			
			$news->news_rand=$rand_str;
            
			//$news_arrposid = I('news_arrposid');
            //$news->news_arrposid='';

			//foreach($news_arrposid as $v){
			//    $news->news_arrposid .= ','.$v;
			//}
           // $news->news_arrposid = substr($news->news_arrposid,1,strlen($news->news_arrposid));
			
            $news->add()?$this->success("新闻添加成功"):$this->error("新闻添加失败");
        }else{
            $this->error($news->getError());
        }

        
    }
    /*编辑*/
    public function edit(){
		//新闻栏目列表
        $this->category = M('NewsCategory')->field("category_id,category_name")->where("category_isshow=0")->order("category_id asc")->select();

        $this->news=M("NewsList")->find($_GET['news_id']);
        //p($this->news);
        $this->display();
    }
    /*修改*/
    public function update(){
        
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        //$news_desc=$_POST['news_desc'];

        $news=D('NewsList');
        $news_id=I('news_id');

        if($news->create()){


			//$news_arrposid = I('news_arrposid');
           // $news->news_arrposid='';

			//foreach($news_arrposid as $v){
			//    $news->news_arrposid .= ','.$v;
			//}
           // $news->news_arrposid = substr($news->news_arrposid,1,strlen($news->news_arrposid));

			//p( $news->news_arrposid);

            //$news->recordid=I('recordid');
            $news->where("news_id=".$news_id)->save()?$this->success("新闻信息修改成功"):$this->error("新闻信息修改失败或者没有修改");
        }else{
            $this->error($news->getError());
        }


        
    }
    /*删除*/
    public function delete(){

        if(!IS_GIE) $this->error("你请求的页面不存在");
        //删除数据同时把isshow置为0,把isdelete置为1
        M("NewsList")->where("news_id=".$_GET['news_id'])->setField(array("news_isdelete"=>1,"news_isshow"=>1))?$this->success("删除成功"):$this->error("删除失败");
        
    }
} 