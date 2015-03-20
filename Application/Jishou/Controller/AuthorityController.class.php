<?php
	namespace Jishou\Controller;
	use Jishou\controller\JishouController;

	class AuthorityController extends JishouController{
		public function _initialize(){
			if(empty($_COOKE['mid'])){
				$this->redirect('http://i.yishu.com/member/passport/login','',3,'请先登录...');
			}

			parent::_initialize();
		
		}
	
	}


?>