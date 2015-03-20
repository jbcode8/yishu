<?php

// +----------------------------------------------------------------------
// | 会员中心 买家订单 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Mall\Model;
class UorderController extends MpublicController {

    public function _initialize(){
        header("Content-type: text/html; charset=utf-8");
        parent::_initialize();

//        $this->Model = D('Order');
//        $this->where = array('buyer_id' => $_SESSION['user_auth']['uid']);
//        $this->aryAct = array('delete', 'list');
//        $this->indexId = 'order_id';
    }
    //所有订单
    public function index(){
		
        $usersession = session('user_auth');
        $uid = $usersession['uid'];
        $map['buyer_id'] = $uid;
        if(is_numeric(I('get.status'))){
            $map['status'] = I('get.status');
        }
        $pages = 10;
        $count = D('OrderView')->counts($map);
        $p = I('get.p',1,intval) ? I('get.p',1,intval) : 1;
        if($p <= 0 || $p > $count){
            $p = 1;
        }

        $page = new \Think\Pages($count,$pages);
        $page->setConfig('prev',"◀ 上一页");   
        $page->setConfig('next','下一页 ▶');   
        $page->setConfig('first','◀ 首页');   
        $page->setConfig('last','尾页 ▶'); 
        $tolpage = ceil($count / $pages);
        $order = D('OrderView')->getOrder($map, $p, $pages);
        $i = 1;
        $select = '';
        while ($i <= $tolpage) {
            if(I('get.p')==$i){
                $select .= "<option value='".$i."' selected>".$i."</option>";
            }else{
                $select .= "<option value='".$i."'>".$i."</option>";
            }
            $i++;
        }
        $this->count = $count;
        $this->page = $page->show();
        $this->pages = $tolpage;
        $this->select = $select;
        $this->order = $order;
        $this->display();
    }
    public function remove(){
        $usersession = session('user_auth');
        $uid = $usersession['uid'];
        if(M('mall_order')->where(array('order_id' => I('get.id'),'buyer_id' => $uid))->setField('status', -1)){
            echo "<script>alert('取消成功!');self.location=document.referrer;</script>";
        }else{
            echo "<script>alert('取消失败!');history.go(-1);</script>";
        }
    }
    public function confirm(){
        $usersession = session('user_auth');
        $uid = $usersession['uid'];
        if(M('mall_order')->where(array('order_id' => I('get.id'),'buyer_id' => $uid))->setField('status', 3)){
            echo "<script>alert('操作成功!');self.location=document.referrer;</script>";
        }else{
            echo "<script>alert('操作失败!');history.go(-1);</script>";
        }
    }
}  