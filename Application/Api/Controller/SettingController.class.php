<?php

/**
 *	手机APP  设置-接口
 *
 */

 namespace Api\Controller;

 Class  SettingController extends BaseController {

       //消息提醒开关
        public function Notifications(){

			$mid = I('get.mid');
            $notifications = I('get.notifications');
            if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	        }
			$where = array('mid' => $mid);
			$data = array('notifications' => $notifications);
            $not = M("member","bsm_","BSM")->where($where)->setField($data);
           // dump( M('member','bsm_','BSM'));exit;//->getLastSql();exit;
            if($not){
			   $return = array('result' => true, 'data' => '更新成功', 'code' => 1);
			   $this->ajaxReturn($return);
	         }else{
			   $return = array('result' => false, 'data' => '更新失败', 'code' => 2);
			   $this->ajaxReturn($return);
		     }

		}
      

      //用户反馈
        public function UserFeedback(){

			$mid = I('get.mid');
		    $feedback_content = I('get.feedback_content');
            $contact = I('get.contact');
            if(!$mid){
			$return = array('result' => false, 'data' => '缺少参数', 'code' => 2);
			$this->ajaxReturn($return);
	        }
            if(!$feedback_content){
			$return = array('result' => false, 'data' => '反馈内容不能为空', 'code' => 2);
			$this->ajaxReturn($return);
		    }

			$data['uid'] = $mid;
			$data['feedback_content'] = $feedback_content;
			$data['contact'] = $contact;
			$data['feedback_time'] = time();
            $back = M("UserFeedback")->add($data);

            if($back){
			   $return = array('result' => true, 'data' => '反馈成功', 'code' => 1);
			   $this->ajaxReturn($return);
	         }else{
			   $return = array('result' => false, 'data' => '反馈失败', 'code' => 2);
			   $this->ajaxReturn($return);
		     }

		  }
   













 }