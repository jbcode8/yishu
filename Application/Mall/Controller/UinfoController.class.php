<?php

// +----------------------------------------------------------------------
// | 会员中心 买家 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class UinfoController extends MpublicController {
	
    public function index(){

        $uid = $_SESSION['user_auth']['uid'];

        // 我的订单
        $count['Order'] = $this->_intCount('Order',array('buyer_id'=>$uid));

        // 我的咨询
        $count['Question'] = $this->_intCount('Question',array('uid'=>$uid,'q_id'=>0));

        // 我的评论
        $count['Comment'] = $this->_intCount('Comment',array('uid'=>$uid,'c_id'=>0));

        // 我的收藏
        $count['Collect'] = $this->_intCount('Collect',array('uid'=>$uid));

        // 我的地址
        $count['ShoppingAddress'] = $this->_intCount('ShoppingAddress',array('uid'=>$uid));

        $this->count = $count;
        $this->display();
    }

	private function _intCount($table, $where){
		$count = D($table)->where($where)->count();
		return intval($count);
	}
} 