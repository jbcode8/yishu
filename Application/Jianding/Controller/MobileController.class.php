<?php
	/**
	* 鉴定移动端入口
	* MobileController.class.php
	* date: 2015-3-4
	* author:zhihui
	*/

	namespace Jianding\Controller;
	use Jianding\Controller\JiandingController;

	class MobileController extends JiandingController{
		public function index(){
			$this->display("FrontPage:Mobile:m_index");
		}

	}
