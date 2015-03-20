<?php
// +----------------------------------------------------------------------
// | HomeController.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

class HomeController extends Controller{

    //TODO 此处需要重写
    public function _initialize(){
        C(api('Admin/Config/lists',array('group'=>1)));
		$special_where=array(
			"special_isshow"=>1,
			"special_isdelete"=>0,
		);
		$Special=M("PaimaiSpecial")->where($special_where)->order("special_id desc")->limit(5)->select();
		$this->assign("Special",$Special);
		$this->assign("action",__ACTION__);
		$this->assign("cur_timestamp",time());
		
    }
}