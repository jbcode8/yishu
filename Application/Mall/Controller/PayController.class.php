<?php

// +----------------------------------------------------------------------
// | 古玩城 第三方支付(支付宝) 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.03.18
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Home\Controller\HomeController;

class PayController extends HomeController{

    public function _initialize(){

        parent::_initialize();

        // 检测是否为登录用户
        isset($_SESSION['user_auth']) AND $loginUser = $_SESSION['user_auth'];
        if(isset($loginUser) && !empty($loginUser)){
            $this->loginUser = $loginUser;
        }else{
            $step2 = U('/guwan/cart/init',array('step'=>2));
            header('location: '.'http://passport.yishu.com/login.html?returnUrl='.urlencode($step2));
            exit;
        }

        // 检测购物车的产品信息
        $intCount = cartGoodsCount();
        if($intCount <= 0){
            $this->error('购物车为空，请先选中商品后再支付！');
        }
    }

    private function _getCartInfo($arr = array()){
        empty($arr) AND $arr = session('sessCart');
        $arrGids = $arrGnum = $arrSn = array();
        foreach($arr as $sid => $list){
            $arrSn[$sid] = $list['sn'];
            foreach($list['g'] as $gid => $num){
                $arrGids[] = $gid;
                $arrGnum[$gid] = $num;
            }
        }
        return array($arrGids, $arrGnum, $arrSn);
    }

    private function _getGoodsInfo($aryGids){
        $data = D('Goods')->field('goods_id,goods_name,goods_price,default_img,store_id')->where(array('goods_id'=>array('in',$aryGids)))->select();
        $ary = array();
        foreach($data as $rs){
            $ary[$rs['goods_id']] = $rs;
        }
        return $ary;
    }

    private function _getTotalPrice($aryGoods, $aryNum){
        $ary = array();
        foreach($aryGoods as $rs){
            $ary[$rs['goods_id']] = $rs['goods_price'] * $aryNum[$rs['goods_id']];
        }
        return $ary;
    }

    /**
     * 将购物车信息处理为订单入库信息
     * @param $cart
     * @param $goods
     * @param $loginUser
     * @param $aryOrderDesc
     * @param $pay_id
     * @param $delivery_id
     * @return array
     */
    private function _processCart2order($cart, $goods, $loginUser, $aryOrderDesc, $pay_id, $delivery_id){

        $arrData = array();
        foreach($cart as $sid => $rs){
            $seller_id = getStoreInfo($sid, 'uid');
            $arrData[$sid]['seller_id'] = $seller_id;
            $arrData[$sid]['seller_name'] = getUserInfo($seller_id, 'username');
            $arrData[$sid]['order_sn'] = $rs['sn'];
            $arrData[$sid]['order_desc'] = $aryOrderDesc[$sid][0];
            $arrData[$sid]['add_time'] = $rs['tm'];
            $arrData[$sid]['pay_id'] = $pay_id;
            $arrData[$sid]['delivery_id'] = $delivery_id;
            $arrData[$sid]['buyer_id'] = $loginUser['uid'];
            $arrData[$sid]['buyer_name'] = $loginUser['username'];
            foreach($rs['g'] as $gid => $num){
                $arrData[$sid]['goods'][$gid]['goods_id'] = $gid;
                $arrData[$sid]['goods'][$gid]['goods_name'] = $goods[$gid]['goods_name'];
                $arrData[$sid]['goods'][$gid]['goods_price'] = $goods[$gid]['goods_price'];
                $arrData[$sid]['goods'][$gid]['goods_img'] = $goods[$gid]['default_img'];
                $arrData[$sid]['goods'][$gid]['store_id'] = $goods[$gid]['store_id'];
                $arrData[$sid]['goods'][$gid]['goods_count'] = $num;
            }
        }
        return $arrData;
    }

    /**
     * 支付数据组装和提交
     */
    public function doalipay(){

        if(IS_POST){
            
            // 订单说明
            $aryOrderDesc = $_POST['sDesc'];
            $pay_id = intval($_POST['pay_id']);
            $delivery_id = intval($_POST['delivery_id']);

            // 支付宝接口需要的订单号
            $order_num = orderSN();

            // 获取购物车的SESSION信息
            $sessionCart = session('sessCart');

            // 产品ID 和 产品对应的数量
            list($aryGids, $aryGno, $arrSn) = $this->_getCartInfo($sessionCart);

            // 购物车的商品信息
            $goodsList = $this->_getGoodsInfo($aryGids);

            // 根据价格和数量算出总的价格
            $aryPrice = $this->_getTotalPrice($goodsList,$aryGno);
            $order_money = array_sum($aryPrice);

            // 支付宝订单名称
            $order_name = '关于商品ID为('.implode('|', $aryGids).')的订单信息';
            $order_desc = '此订单下的子订单号('.implode('|', $arrSn).')';
            $order_goods = U('/guwan/goods-'.$aryGids[0]);

            // 现将订单的信息入库
            $orderInfo = $this->_processCart2order($sessionCart, $goodsList, $this->loginUser, $aryOrderDesc, $pay_id, $delivery_id);
            if(!empty($orderInfo)){
                foreach($orderInfo as $rs){
                    // 新增数据到表[mall_order]
                    $inId = D('Order')->add($rs);
                    if($inId){

                        // 新增收货地址 先读取默认地址 再入库[待确定]

                        // 增加数据到表[mall_order_goods]
                        foreach($rs['goods'] as $goods){
                            $goods['order_id'] = $inId;
                            M('MallOrderGoods')->add($goods);
                        }
                    }
                }
            }

            /** 读取配置文件的参数 **/
            $alipay_config = C('ALI_CONFIG');

            /** 构造需要的参数数组，无需改动 **/
            $parameter = array(
                "service" => "create_direct_pay_by_user",
                "payment_type" => '1',

                "partner" => $alipay_config['partner'],
                "_input_charset" => $alipay_config['input_charset'],

                "notify_url" => C('ALI_INFO.notify_url'),
                "return_url" => C('ALI_INFO.return_url'),
                "seller_email" => C('ALI_INFO.seller_email'),
                "anti_phishing_key" => '',
                "exter_invoke_ip" => get_client_ip(),

                "out_trade_no" => $order_num,
                "subject" => $order_name,
                "total_fee" => $order_money,
                "body" => $order_desc,
                "show_url" => $order_goods
            );
            header("Content-type: text/html; charset=utf-8");
            // 建立请求
            vendor('Alipay.AliSubmit');
            $alipaySubmit = new \AlipaySubmit($alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter, 'post', '确认');
            echo $html_text;
        }
    }

    /**
     *  页面跳转处理方法
     */
    function returnurl(){

        $alipay_config = C('ALI_CONFIG');

        vendor('Alipay.AliNotify');
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();

        if($verify_result) {

            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {

                $out_trade_no = $_GET['out_trade_no'];
                $total_fee = $_GET['total_fee'];
                $order_body = $_GET['body'];
                $nx = strpos($order_body,'(');
                $noList = trim(substr($order_body,($nx+1)),')');

                /** 支付成功后的数据操作 */
                // 1.清空购物车
                session('sessCart', null);

                // 2.SESSION保存商户订单号$out_trade_no
                session('out_trade_no', $out_trade_no);

                // 3.子订单传值到步骤三
                $this->redirect(C('ALI_INFO.successpage'), array('ono'=>$out_trade_no,'fee'=>$total_fee,'nolt'=>$noList));

            }else {

                // 支付失败后的数据操作
                $this->redirect(C('ALI_INFO.errorpage'));
            }

        }else {

            // 如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "支付失败！";
        }
    }

    /**
     * 服务器异步通知页面方法
     */
    function notifyurl(){

        $alipay_config = C('ALI_CONFIG');

        // 计算得出通知验证结果
        vendor('Alipay.AliNotify');
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {

            // 验证成功 获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no   = $_POST['out_trade_no'];      //商户订单号
            $trade_no       = $_POST['trade_no'];          //支付宝交易号
            $trade_status   = $_POST['trade_status'];      //交易状态
            $total_fee      = $_POST['total_fee'];         //交易金额
            $notify_id      = $_POST['notify_id'];         //通知校验ID。
            $notify_time    = $_POST['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
            $buyer_email    = $_POST['buyer_email'];       //买家支付宝帐号；
            $parameter = array(
                "out_trade_no"     => $out_trade_no, //商户订单编号；
                "trade_no"     => $trade_no,     //支付宝交易号；
                "total_fee"     => $total_fee,    //交易金额；
                "trade_status"     => $trade_status, //交易状态
                "notify_id"     => $notify_id,    //通知校验ID。
                "notify_time"   => $notify_time,  //通知的发送时间。
                "buyer_email"   => $buyer_email,  //买家支付宝帐号；
            );
            if($_POST['trade_status'] == 'TRADE_FINISHED') {
                //
            }else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                if(!checkorderstatus($out_trade_no)){
                    orderhandle($parameter);
                    //进行订单处理，并传送从支付宝返回的参数；
                }
            }
            echo "success";        //请不要修改或删除

        }else {
            echo "fail";
        }
    }
}