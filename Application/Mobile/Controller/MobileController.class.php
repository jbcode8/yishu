<?php
// +----------------------------------------------------------------------
// | HomeController.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Mobile\Controller;
use Paimai\Controller\PaimaiPublicController;

/*
	本控制器为wap频道总控制器，继承网站总控制器
*/
class MobileController extends PaimaiPublicController{
	/*
	如果寄售频道有所有页面有更改可以在这个方法里面写
	*/
	 public function _initialize(){
		 parent::_initialize();
		 $auth = getLoginStatus();
		 $this->auth = $auth;
         $this->assign('auth', $auth);
		 //本控制器名
		 $this->assign("action",__ACTION__);
    }
}