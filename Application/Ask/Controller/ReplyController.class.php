<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-22
 * Time: 下午5:50
 */

namespace Ask\Controller;
use Admin\Controller\AdminController;

class ReplyController extends AdminController{
    /*public function _initialize() {
        parent::_initialize();
        $this->Model = D('Reply');
    }*/
	public function del(){
		$id = I('get.rid');
		$re = M('reply')->where(array('id'=>$id))->delete();
		//echo M('ask_reply')->getLastSql();die;
		if($re){
			$this->success('删除成功!');
		}else{
			$this->error('删除失败!');
		}
	}
} 