<?php
// +----------------------------------------------------------------------
// | HomeController.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace News\Controller;
use Home\Controller\HomeController;

/*
	本控制器为寄售频道总控制器，继承网站总控制器
*/
class NewsController extends HomeController{
	/*
	如果寄售频道有所有页面有更改可以在这个方法里面写
	*/
	 public function _initialize(){
		 parent::_initialize();
		 //parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth',$auth);
        //p($auth);
        //echo strtolower(__MODULE__);exit;
		 
    }
}