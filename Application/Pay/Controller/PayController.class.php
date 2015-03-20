<?php
/**
 * 第三方支付(支付宝) 控制器文件
 * Author: jeff # 2014.08.18
 */
namespace Pay\Controller;

use Alipay\AlipaySubmit;
use Home\Controller\HomeController;

class PayController extends HomeController
{
    public function _initialize()
    {
        //parent::_initialize();
        $auth = getLoginStatus();
        if (!$auth) {
            header("Location:" . U('/Member/Passport/login/', '', '') . '?returnUrl=' . __SELF__);
            //$this->redirect('/Member/Passport/login/returnUrl'.urlencode());//跳转到配置项中配置的支付失败页面；
            return false; //redirect to login page
        }
    }

    //doalipay方法
    public function doalipay()
    {
        header("Content-type:text/html;charset=utf-8");
        $alipay_config = C('alipay_config');
        /**************************请求参数**************************/

        $payment_type = "1"; //支付类型 //必填，不能修改
        $notify_url = C('alipay.notify_url'); //服务器异步通知页面路径
        $return_url = C('alipay.return_url'); //页面跳转同步通知页面路径
        $seller_email = C('alipay.seller_email'); //卖家支付宝帐户必填
        /*
        $out_trade_no = $_POST['trade_no'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
        $subject = $_POST['ordsubject'];  //订单名称 //必填 通过支付页面的表单进行传递
        $total_fee = $_POST['ordtotal_fee'];   //付款金额  //必填 通过支付页面的表单进行传递
        $body = $_POST['ordbody'];  //订单描述 通过支付页面的表单进行传递
        $show_url = $_POST['ordshow_url'];  //商品展示地址 通过支付页面的表单进行传递
        */
        $style = I("get.style", 0, 'intval');
        $uid = $_SESSION['user_auth']['uid'];
        $id = I("get.id", 0, 'intval'); //这个id为本订单的订单id
        if ($id == 0) $this->error("你请求的页面不存在");
        /*******************支付**********************************/
        if ($style == 2) { //支付
            $orderinfo_id = $id;
            $orderinfo = M("PaimaiOrderInfo")->field("orderinfo_sn,orderinfo_amount,orderinfo_uid")->where(array('orderinfo_id' => $orderinfo_id))->find();

            //如果订单不存在,或者这个订单的属主不是本人,则退出
            if (empty($orderinfo) || $orderinfo['orderinfo_uid'] != $uid) $this->error("你请求的页面不存在");
            //到这一步则订单正确,
            $out_trade_no = $orderinfo['orderinfo_sn'];
            $subject = "商品付款";
            $total_fee = $orderinfo['orderinfo_amount'];
            $body = "商品付款订单";
            $anti_phishing_key = "";
            $show_url = "";
            $exter_invoke_ip = get_client_ip();

        } elseif ($style == 1) {
            $recharge_id = $id;
            //根据订单在数据库中的查找这条订单信息
            $recharge = M("PaimaiRecharge")->field("recharge_money,recharge_uid,recharge_sn")->where(array('recharge_id' => $recharge_id))->find();
            //如果这个充值不存在,或者这个人不是本人则退出
            if (empty($recharge) || $recharge['recharge_uid'] != $uid) $this->error("你请求的页面不存在");
            //到这一步则订单正确
            $out_trade_no = $recharge['recharge_sn'];
            $subject = "保证金充值";
            $total_fee = $recharge['recharge_money'];
            $body = "保证金充值订单";
            $anti_phishing_key = "";
            $show_url = "";
            $exter_invoke_ip = get_client_ip();
        } else {
            $this->error("发生未错误");
        }
        /*******************支付**********************************/
        /*  //去支付宝
          $out_trade_no=$order_sn;
          $subject="保证金充值";
          $total_fee=$total_fee;
          $body="保证金充值订单";
          $anti_phishing_key = "";
          $show_url="";
          $exter_invoke_ip = get_client_ip();*/
        /*  $out_trade_no = '20140818YSOD00002';//商户订单号 通过支付页面的表单进行传递，注意要唯一！
          $subject = '测试订单';  //订单名称 //必填 通过支付页面的表单进行传递
          $total_fee = '0.01';   //付款金额  //必填 通过支付页面的表单进行传递
          $body = '这是一个测试支付宝订单';  //订单描述 通过支付页面的表单进行传递
          $show_url = 'http://www.yishu.com/Paimai/FrontData/index/id/1.html';  //商品展示地址 通过支付页面的表单进行传递
          $anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
          $exter_invoke_ip = get_client_ip(); //客户端的IP地址*/
        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($alipay_config['partner']),
            "payment_type" => $payment_type,
            "notify_url" => $notify_url,
            "return_url" => $return_url,
            "seller_email" => $seller_email,
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "show_url" => $show_url,
            "anti_phishing_key" => $anti_phishing_key,
            "exter_invoke_ip" => $exter_invoke_ip,
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
        );
        //建立请求
        vendor('Alipay.AlipaySubmit');
        $alipaySubmit = new \Alipay\AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "post", "确认");
        echo $html_text;
    }

    /******************************
     * 服务器异步通知页面方法
     * 其实这里就是将notify_url.php文件中的代码复制过来进行处理
     *******************************/
    function notifyurl()
    {
        header("Content-type:text/html;charset=utf-8");
        //这里还是通过C函数来读取配置项，赋值给$alipay_config
        $alipay_config = C('alipay_config');
        //计算得出通知验证结果
        vendor('Alipay.AlipayNotify');
        $alipayNotify = new \Alipay\AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no = $_POST['out_trade_no']; //商户订单号
            $trade_no = $_POST['trade_no']; //支付宝交易号
            $trade_status = $_POST['trade_status']; //交易状态
            $total_fee = $_POST['total_fee']; //交易金额
            $notify_id = $_POST['notify_id']; //通知校验ID。
            $notify_time = $_POST['notify_time']; //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
            $buyer_email = $_POST['buyer_email']; //买家支付宝帐号；
            $parameter = array(
                "out_trade_no" => $out_trade_no, //商户订单编号；
                "trade_no" => $trade_no, //支付宝交易号；
                "total_fee" => $total_fee, //交易金额；
                "trade_status" => $trade_status, //交易状态
                "notify_id" => $notify_id, //通知校验ID。
                "notify_time" => $notify_time, //通知的发送时间。
                "buyer_email" => $buyer_email, //买家支付宝帐号；
            );
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                //
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                if (!checkorderstatus($out_trade_no)) {
                    orderhandle($parameter);
                    //进行订单处理，并传送从支付宝返回的参数；
                }
            }
            echo "success"; //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }
    }

    /*
        页面跳转处理方法；
        */
    function returnurl()
    {
        header("Content-type:text/html;charset=utf-8");
        $alipay_config = C('alipay_config');
        vendor('Alipay.AlipayNotify');
        $alipayNotify = new \Alipay\AlipayNotify($alipay_config); //计算得出通知验证结果
        $verify_result = $alipayNotify->verifyReturn();
        //if($verify_result) {
        //验证成功
        //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
        $out_trade_no = $_GET['out_trade_no']; //商户订单号
        $trade_no = $_GET['trade_no']; //支付宝交易号
        $trade_status = $_GET['trade_status']; //交易状态
        $total_fee = $_GET['total_fee']; //交易金额
        $notify_id = $_GET['notify_id']; //通知校验ID。
        $notify_time = $_GET['notify_time']; //通知的发送时间。
        $buyer_email = $_GET['buyer_email']; //买家支付宝帐号；

        $parameter = array(
            "out_trade_no" => $out_trade_no, //商户订单编号；
            "trade_no" => $trade_no, //支付宝交易号；
            "total_fee" => $total_fee, //交易金额；
            "trade_status" => $trade_status, //交易状态
            "notify_id" => $notify_id, //通知校验ID。
            "notify_time" => $notify_time, //通知的发送时间。
            "buyer_email" => $buyer_email, //买家支付宝帐号
        );
        /* echo "<pre>";
         print_r($_GET);exit;*/
        if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
            //支付成功，之后进行数据库订单写入操作
            /*
            if(!checkorderstatus($out_trade_no)){
                orderhandle($parameter);  //进行订单处理，并传送从支付宝返回的参数；
            }
            */
            $uid = $_SESSION['user_auth']['uid'];
            if (empty($uid)) {
                $this->error("回调发现未登录,发生未知错误,请联系网站客服");
            }
            //返回我们网站用户的订单号
            $order_sn = rtrim(I('get.out_trade_no'));
            //总金额
            $total_fee = rtrim(I('get.total_fee'));
            //在支付宝的交易号
            $total_no = rtrim(I('get.trade_no'));
            //付款者的邮箱号
            $buyer_email = rtrim(I('get.buyer_email'));

            if (preg_match('/CZ/', $order_sn)) { //充值
                $input_rechargedata['recharge_style'] = 1;
                $input_rechargedata['recharge_status'] = 2; //2为已经付款
                $input_rechargedata['recharge_trade_no'] = $total_no; //在支付宝的交易号
                $input_rechargedata['recharge_paytime'] = time(); //本条记录付款时间
                $input_rechargedata['recharge_buyeremail'] = $buyer_email; //付款者的邮箱号
                if (M("PaimaiRecharge")->where(array("recharge_uid" => $uid, "recharge_sn" => $order_sn))->data($input_rechargedata)->save()) {
                    $sql = "update yishu_member set member_cash=member_cash+$total_fee where uid=$uid";

                    if (!M()->execute($sql)) {
                        $this->error("充值成功,但发生未知错误,请联系网站客服");
                    }
                    //正确跳转

                    $this->redirect(U("Member/Order/ok", array('style' => 1, 'order_sn' => $order_sn), "")); //跳转到配置项中配置的支付成功页面；
                }
            } elseif (preg_match('/OI/', $order_sn)) { //支付
                //更改yishu_paimai_order_info表中的信息,支付状态为2,已支付,更改支付时间为当前时间
                $orderinfo_data['orderinfo_paystatus'] = 2;
                $orderinfo_data['orderinfo_paytime'] = time();
                $orderinfo_data['orderinfo_trade_no'] = $total_no;
                $orderinfo_data['orderinfo_buyeremail'] = $buyer_email;
                if (M('PaimaiOrderInfo')->where(array("orderinfo_uid" => $uid, 'orderinfo_sn' => $order_sn))->data($orderinfo_data)->save()) {

                    //根据订单找出本条订单的yishu_paimai_order_info的id,再根据id更改对应yishu_paimai_order的状态
                    $orderinfo_id = M('PaimaiOrderInfo')->where(array('orderinfo_sn' => $order_sn))->getField("orderinfo_id");
                    $order = M('PaimaiOrder');
                    //获得商品id
                    $goods_id = $order->getField("order_goodsid");
                    //更改yishu_paimai_order表的状态为2,已经支付
                    if ($order->where(array('order_orderinfoid' => $orderinfo_id))->setField('order_status', 2))
                        //更改yishu_paimai_goods表的商品状态为3,即本商品已经成功交易
                        M("PaimaiGoods")->where(array('goods_id' => $goods_id))->setField("goods_status", 3);
                    //正确跳转
                    $this->redirect(U("Member/Order/ok", array('style' => 2, 'order_sn' => $order_sn), ""));
                    //跳转到配置项中配置的支付成功页面；
                }
            }

            //$this->redirect(C('alipay.successpage'));//跳转到配置项中配置的支付成功页面；

        } else {

            $this->redirect(U("Member/Order/nook", '', '')); //跳转到配置项中配置的支付失败页面；
        }
        /*
        }else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "支付失败！";
        }
        */
    }
}

?>