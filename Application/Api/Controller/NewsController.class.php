<?php

/**
 *	手机APP  发现->新闻资讯接口
 *
 */

 namespace Api\Controller;

 Class NewsController extends BaseController {
	//填写方法 get传一个参数：verify=1
    //  http://www.yishu.com/api/news/newsdetails/?verify=1&news_id=561

   //热门推荐->行业热点->5条数据->根据查看数
     public function HotNews(){
   
             $where=array(
			  'news_type'=>1,
		      'news_isshow'=>0,
			  'news_isdelete'=>0,
						);
             $field=array(
					       'news_id',
						   'news_name',
						   'recordid',
				           'news_createtime',
				           'news_summary',
				           'news_desc',
				           'news_author',
				           'examine',   //'查看数'
				           'praise',    //'点赞数'
				   );
             $hotnews= M("NewsList")->field($field)->where($where)->order('examine desc')->limit(5)->select();
			 foreach ($hotnews as $k => $v) {
						$hotnews[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
					  }
		     $return = array('result' => true, 'data' => $hotnews, 'code' => 1);
             $this->ajaxReturn($return);
	 }

   //资讯新闻百叶窗
       public function ImgNews(){

				 $where=array(
						'news_arrposid'=>3,
						'news_isshow'=>0,
						'news_isdelete'=>0,
						);
				 $field=array(
					   'news_id',
					   'news_name',
					   'recordid',
				 );
				 $imgs= M("NewsList")->field($field)->where($where)->order('news_createtime desc')->limit(3)->select();
					 foreach ($imgs as $k => $v) {
						$imgs[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
					  }
				 $return = array('result' => true, 'data' => $imgs, 'code' => 1);
				 $this->ajaxReturn($return);    
        }

	//资讯新闻列表

	public function NewsList( $p = 1, $count = 10){
          
	    $field='news_id, news_name, recordid, news_desc,news_summary';
		$where = array('news_type'=> 2, 'news_isdelete'=> 0);
        $news=M('NewsList')->field($field)->order("news_id desc")->limit(($p-1)*$count . ',' . $count)->where($where)->select();
		foreach ($news as $k => $v) {
			$news[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		 }
		$return = array('result' => true, 'data' => $news, 'code' => 1);
		$this->ajaxReturn($return);

	}


	//新闻详情
		 public function NewsDetails(){
 
			  $news_id = I('get.news_id');
			  
			  if(!$news_id){
					$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
					$this->ajaxReturn($return);
			  }
              $where=array(
						'news_id'=>$news_id,
						);
			   $field=array(
						   'news_name',
						   'recordid',
				           'news_createtime',
				           'news_desc',
				           'news_author',
				           'examine',
				           'news_summary',
				           'praise',
					 );
				 $details= M("NewsList")->field($field)->where($where)->select();
					 foreach ($details as $k => $v) {
						$details[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
					  }

                 $return = array('result' => true, 'data' => $details, 'code' => 1);
				 $this->ajaxReturn($return);
		 }

        //新闻查看数
        public function NewsExamine(){

            $news_id = I('get.news_id');
			//$examine = I('get.examine');

            if(!$news_id){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	        }
			/*
            if(!$examine){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	        }*/

              $where = array('news_id' => $news_id);
              $now= M("NewsList")->where($where)->getField('examine');			 
              $data['examine'] = $now + 1;
			  $exam = M("NewsList")->where($where)->save($data);

			 if($exam){
			       $return = array('result' => true, 'data' => '更新成功', 'code' => 1);
			       $this->ajaxReturn($return);
	         }else{
			       $return = array('result' => false, 'data' => '更新失败', 'code' => 2);
			       $this->ajaxReturn($return);
		     }

		}

        //新闻点赞
        public function NewsPraise(){

            $news_id = I('get.news_id');
			$praise = I('get.praise');

            if(!$news_id){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	        }
			
            if(!$praise){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	        }

              $where = array('news_id' => $news_id);
              $now= M("NewsList")->where($where)->getField('praise');			 
              $data['praise'] = $now + 1;
			  $array_praise = M("NewsList")->where($where)->save($data);

			 if($array_praise){
			       $return = array('result' => true, 'data' => '更新成功', 'code' => 1);
			       $this->ajaxReturn($return);
	         }else{
			       $return = array('result' => false, 'data' => '更新失败', 'code' => 2);
			       $this->ajaxReturn($return);
		     }

		}


		//
          



 }