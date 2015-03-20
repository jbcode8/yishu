<?php
	/**
	*    鉴定的数据调用接口controller
	*    author： zhihui
	*    date : 2015-2-16
	*/
	namespace Jianding\Controller;
	use Jianding\Controller\JiandingController;
	class JiandingInterfaceController extends JiandingController{

		/**
		*  comment 添加接口
		*  @param   $_POST goods_id comment_val   商品的ID和评论的内容
		*  @return   评论的用户信息
		*/
		public function commentApi(){
			if(!IS_AJAX) return;

			$dt = array();
			if(empty($_POST['goods_id'])||empty($_POST['comment_val'])){
				$dt['flag'] = false;	
				exit(json_encode($dt));
			}

			$comment_info = array(
				'goods_id' =>$_POST['goods_id'],
				'content'  =>$_POST['comment_val'],
				'atime'  =>time(),
				);
			
			if(empty($this->login_info)){
				$comment_info['is_anonymous'] = 0;
			}else{
				$comment_info['is_anonymous'] =1;
				$comment_info['user_id'] =$this->login_info['mid'];
				$comment_info['user_name'] =$this->login_info['username'];

			}
			$flag = M('JiandingComment')->add($comment_info);
			
			if(!$flag){
				$dt['flag'] = false;	
				exit(json_encode($dt));
			}else{
				$comment_info['content'] = htmlspecialchars($comment_info['content']);
				$dt['flag'] = true;
				$dt['da'] = $comment_info;
				exit(json_encode($dt));
			}

		}

	}