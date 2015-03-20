<?php

// +----------------------------------------------------------------------
// | 用户资料
// +----------------------------------------------------------------------
// | Author: Ethan <838777565@qq.com>
// +----------------------------------------------------------------------
namespace Jishou\Controller;
use Jishou\Controller\JishouController;

/*
    测试控制器
*/
class TestController extends JishouController {
    public function _initialize()
    {
        parent::_initialize();
        
    }
  
    public function index(){
        
        exit("yao_xi");
       
        $this->display();
    }
    

    public function alipaysendmessage(){

    	$uid = session('mid');
    	$mobile = get_mobile($uid);
        //参数数组
        $arr = array();
        //支付宝支付时间
        $arr['time'] = date('m月d日 H时i分', time());
        //支付宝支付金额
        $arr['goods'] = '0.01';
        //print_r($arr);
        //die;
        Vendor('Mobile.Mobile');
        $SMS = new \Mobile();
        $code = $SMS->sendmobilecode($mobile, 3, $arr);
        var_dump($code);
    }
     public function alipaysendmail(){
    	// $uid = session('mid');
    	// $mobile = get_mobile($uid);
    	// //print_r($mobile);die;
     //    //参数数组
     //    $arr = array();
     //    //支付宝支付时间
     //    $arr['time'] = date('m月d日 H时i分', time());
     //    //支付宝支付金额
     //    $arr['money'] = '0.01';
     //    Vendor('Mobile.Mobile');
     //    $SMS = new Mobile;
     //    $code = $SMS->sendmobilecode($mobile, 3, $arr);
     	//echo 1111;die;
     	//$arrs = array('time'=>'2014', 'money' => '0.01');
        // import("@.Action.Member.IndexAction");
        // $aa = new IndexAction();
        // $aa->_send_email('feiniutest@163.com', 'abcd123', '275302692@qq.com', 'http://i.yishu.com', 2, $arr);
        // //var_dump($b);
        //redirect('Member/Index/_send_email',array('user' => 'feiniutest@163.com', 'pwd' => 'abcd123', 'tomail' => '275302692@qq.com','url' => 'http://i.yishu.com', 'param' => 2, 'arr' => $arrs));
        $arrs = array('time'=>'2014', 'money' => '0.01');
        $aa = alipay_send_email('feiniutest@163.com', 'abcd123', '275302692@qq.com','http://i.yishu.com/', $arrs);
        var_dump($aa);

    }

    // public function alipay_email(){
    // 	$uid = session('mid');
    // 	var_dump(get_email($uid));
    // }

    // public function aaa(){

    //      // //手机短信
    //      //    $mobile = get_mobile($uid);
    //      //    //print_r($mobile);die;
    //      //    //参数数组
    //      //    $arr = array();
    //      //    //支付宝支付时间
    //      //    $arr['time'] = date('m月d日 H时i分', time());
    //      //    //支付宝支付金额
    //      //    $arr['money'] = $total_fee;
    //      //    Vendor('Mobile.Mobile');
    //      //    $SMS = new Mobile;
    //      //    $code = $SMS->sendmobilecode($mobile, 3, $arr);

    //      //    //邮件
    //      //    //$arr['name'] = array('time'=>'2014', 'money' => '0.01');
    //      //    $user_email = get_email($uid);
    //      //    $aa = alipay_send_email('feiniutest@163.com', 'abcd123', $user_email,'http://i.yishu.com/auction/center_order/rechargev2', $arr);
    // }
    public function testcoupons() {
        echo return_amount('1746');
        echo change_status_false('1754');
    }
    public function testpaimaipublic() {
        $uid = $_SESSION['mid'];
       
        echo $this->FrozenAmount.'<br/>';
        echo $this->frozen.'<br />';
    }
}
