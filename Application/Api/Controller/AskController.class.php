<?php

/**
 *	手机APP 发现->交流接口
 *
 */

 namespace Api\Controller;

 Class AskController extends BaseController {
	
	//填写方法 get传一个参数：verify=1


    //交流列表   回复表yishu_ask_reply . b    提问表yishu_ask_question . a
    public function AskList( $p = 1, $count = 10 ){

       $field = array(  
		   'id',
           'title',     //提问标题
		   'input_time', //提问时间
		   'tag',        //提问标签
		   'cate_id',     //类别ID
		   'user_id',     //提问人ID
		 );
		$where = array('tag'=>1,);

		$ask = M('AskQuestion')->field($field)->limit(10)->order("input_time desc")->where($where)->select();
		foreach($ask as $k => $v){
             $member_where = array('mid' => $v['user_id']);
			 $nickname_field= array(
				 'nickname',
				 );
             $nickname = M("member","bsm_","BSM")->field($nickname_field)->where($member_where)->select();

		     $where = array('question_id' => $v['id']);   //条件：回复ID-》提问的ID
             $field = array(  
				   'id as reply_id',
				   'content as reply_content',   
				   'input_time as reply_time',       
				   'user_id as reply_userid', 
		     );
			$reply = M('AskReply')->field($field)->order("input_time desc")->where($where)->select();

            
			$r_count = count($reply);
			$ask[$k]['nickname'] = $nickname;
			$ask[$k]['count'] = $r_count;
			$ask[$k]['reply'] = $reply;
		}

	   if(!$ask){
			$return = array('result' => false, 'data' => '', 'code' => 2);
			$this->ajaxReturn($return);
	   }

       $return = array('result' => true, 'data' => $ask, 'code' => 1);
	   $this->ajaxReturn($return);  
		
	}

    //评论详情列表   可看到所有评论
	public function UserReply(){

         $id = I('get.id');
		 if(!$id){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
		 }  

		$field='id, content, user_id, input_time, question_id';
		$where = array('question_id'=> $id);

        $count = M('AskReply')->where($where)->count();
		$reply=M('AskReply')->field($field)->order("input_time desc")->where($where)->select();
  
        foreach($reply as $k => $v){
		    $member_where = array('mid' => $v['user_id']);
			$nickname_field= array(
				 'nickname',
				 );

            $nickname = M("member","bsm_","BSM")->field($nickname_field)->where($member_where)->select();
			$reply[$k]['nickname'] = $nickname;
		}

       if(!$reply){
			$return = array('result' => false, 'data' => '', 'code' => 2);
			$this->ajaxReturn($return);
	   }

		$return = array('result' => true, 'data' => $reply,'count' => $count, 'code' => 1);
		$this->ajaxReturn($return);

	}

     //发布提问
	 public function AddQuestion(){

           $user_id = I('get.mid');
		   $title = I('get.title');
		   $content = I('get.content');

           if(!$user_id){
			   $return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			   $this->ajaxReturn($return);
	       }
           if(!$title){
			$return = array('result' => false, 'data' => '内容不能为空', 'code' => 2);
			$this->ajaxReturn($return);
		   }

           $data = array();
           $data['user_id'] = $user_id;
           $data['title'] = $title;
		   $data['content'] = $content;
           $data['input_time'] = time();

        $questionlist = M('AskReply')->add($data);

        if($questionlist){
			$return = array('result' => true, 'data' => '提问成功', 'code' => 1);
			$this->ajaxReturn($return);
	    }else{
			$return = array('result' => false, 'data' => '提问失败', 'code' => 2);
			$this->ajaxReturn($return);
		}
	 }

      //发布评论
      public function AddReply(){
                    
        $user_id = I('get.mid');
	    $question_id = I('get.question_id');
	    $content = I('get.content');

        if(!$user_id){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }	
		if(!$question_id){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	    }
        if(!$content){
			$return = array('result' => false, 'data' => '内容不能为空', 'code' => 2);
			$this->ajaxReturn($return);
		}
        
        $data = array();
        $data['content'] = $content;
        $data['user_id'] = $user_id;
        $data['question_id'] = $question_id;
        $data['input_time'] = time();
        $replylist = M('AskReply')->add($data);

        if($replylist){
			$return = array('result' => true, 'data' => '回复成功', 'code' => 1);
			$this->ajaxReturn($return);
	    }else{
			$return = array('result' => false, 'data' => '回复失败', 'code' => 2);
			$this->ajaxReturn($return);
		}

	  }









 }