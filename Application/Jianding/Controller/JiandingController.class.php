<?php
// +----------------------------------------------------------------------
// | HomeController.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Jianding\Controller;
use Home\Controller\HomeController;

/*
	本控制器为寄售频道总控制器，继承网站总控制器
*/
class JiandingController extends HomeController{
	/*
	如果寄售频道有所有页面有更改可以在这个方法里面写
	*/
	 public function _initialize(){
	 	//获取用户的登录ID
	 	if(empty($_COOKIE['mid'])){
	 		$this->mid = '';
	 	}else{
	 		$this->mid = $_COOKIE['mid'];
	 	}
		//jishou基本信息的加载
		//加载全局的配置信息表yishu_config中数据
		C(api('Admin/Config/lists',array('group'=>1)));

		/*if($auth=getLoginStatus()){
			$login_info ="<a href='javascript:void(0)'>{$auth['username']}</a><a href='http://i.yishu.com'>个人中心</a>";
		}else{
			$login_info ="<a href='http://i.yishu.com/member/passport/login'>用户登录</a><a href='http://i.yishu.com/passport/register'>免费注册</a>";
		}*/

		$this->login_info = getLoginStatus();

		//$this->assign('login_info',$login_info);
		
    }
}
