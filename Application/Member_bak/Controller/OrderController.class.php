<?php
/**
 * OrderController.class.php
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 sparui. All rights reserved.
 * @link          http://www.sparui.com
 * @license       http://www.sparui.com/license
 */

namespace Member\Controller;

use Member\Model;

class OrderController extends MemberController
{
    /*
     * 我的订单,显示我拍下的商品
     */
    public function index()
    {
        $uid = $_SESSION['user_auth']['uid'];
        if (!$uid) $this->error("你请求的页面不存在");
        $this->order = M('PaimaiOrder')->where(array('order_uid' => $uid, 'order_status' => 0))->select();
        //p($this->order);
        $this->display();
    }

    /*
     * 已经卖到的商品
     */
    public function buyed()
    {
        $uid = $_SESSION['user_auth']['uid'];
        if (!$uid) $this->error("你请求的页面不存在");
        $this->order = M('PaimaiOrder')->field('order_goodssn,orderinfo_paytime,order_goodsname,orderinfo_amount,order_goodsid')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where(array('order_uid' => $uid, 'order_status' => 2))->select();
        /* p($this->order);*/
        $this->display();
    }
    /**********************充值start*********************************/
    /*
     * 充值
     */
    public function recharge()
    {
        $uid = $_SESSION['user_auth']['uid'];
        if (empty($uid)) $this->error("此页面不存在");
        //总金额,但觉得 这个应该从yishu_paimai_recharge表中累加起来总好,现在是从用户表中读取的
        $this->total = M("Member")->where(array('uid' => $uid))->getField('member_cash');
        //充值表列表
        $this->lists = M('PaimaiRecharge')->where(array('recharge_uid' => $uid))->select();
        //p($this->lists);
        /* echo "<pre>";print_r($this->lists);exit;*/
        $this->display();
    }

    public function addrecharge()
    {
        $gid = I("get.gid", 0, "intval");
        $need = I("get.need", 0.00, 'floatval');

        //把金额都转成xx.00形式的
        $need = number_format($need, 2, '.', '');
        if (!empty($gid)) {
            $this->goods_needmoney = M("PaimaiGoods")->where(array('goods_id' => $gid))->getField("goods_needmoney");
            $this->assign("gid", $gid);
        }
        $this->order_sn = $this->createordersn();
        $this->display();
    }

    /*
     * 对充值提交过来的数据进行处理
     */
    public function doaddrecharge()
    {
        //接受充值订单号
        $cz_order_sn = I('post.cz_order_sn');
        //接受总金额
        $total_fee = I('post.total_fee', 0.00, 'floatval');
        //把金额都转成xx.00形式的
        $total_fee = number_format($total_fee, 2, '.', '');
        if ($total_fee == 0.00) {
            $this->error("请正确填写充值金额");
        }
        $uid = $_SESSION['user_auth']['uid'];
        //充值订单入库
        $data_one['recharge_sn'] = $cz_order_sn;
        $data_one['recharge_uid'] = $uid;
        $data_one['recharge_money'] = $total_fee;
        $data_one['recharge_createtime'] = time();
        $data_one['recharge_ip'] = get_client_ip();
        $data_one['recharge_status'] = 1; //1为提交订单但未付款
        /* $data_one['recharge_returngid'] = I('post.gid', 0, 'intval');*/
        $recharge_id = M("PaimaiRecharge")->data($data_one)->add();
        if (empty($recharge_id)) {
            $this->error("本次订单提交失败,请重新提交");
        }
        //跳转到支付控制器,把这个订单的id和类型带过去
        $this->redirect(U("Pay/Pay/doalipay", array('id' => $recharge_id, 'style' => 1), ''));
    }

    /*
     * 创建唯一充值订单号
     */
    public function createordersn()
    {
        //创建唯一订单号
        $orderinfo = M("PaimaiRecharge");
        $info_sn = 'CZ' . date("Ymd") . mt_rand(10000, 99999);
        return $orderinfo->where("info_sn=$info_sn")->count() ? $this->createordersn() : $info_sn;
    }
    /**********************提交商品订单end*********************************/
    /*
     * 支付成功页面
     */
    public function ok()
    {
        /* echo "<pre>";
         print_r($_GET);exit;*/
        $order_sn = I("get.order_sn");
        $style = I("get.style");
        $uid = $_SESSION['user_auth']['uid'];

        if (empty($uid)) $this->error('此页面不存在');
        //如果style==1则为充值页面
        //v($_GET);
        //p($style);
        if ($style == 1) {
            //这里还需要对where进行过滤,还要进行优化
            $recharge = M("PaimaiRecharge")->field("recharge_money,recharge_uid")->where(array('recharge_sn' => $order_sn))->find();
            //p($recharge);
            /*p(empty($recharge));*/
            //如果这个订单存在,或者这个订单不是本人,则报错
            if (empty($recharge) && $recharge['recharge_uid'] != $uid) {
                $this->error("此页面不存在");
            }
            $this->name = "保证金充值";

        } elseif ($style == 2) {
            $orderinfo = M('PaimaiOrderInfo')->field('orderinfo_amount,orderinfo_uid')->where(array('orderinfo_sn' => $order_sn))->find();
            //如果这个订单存在,或者这个订单不是本人,则报错
            if (empty($orderinfo) || $orderinfo['orderinfo_uid'] != $uid) {
                $this->error("此页面不存在");
            }
            $this->name = "商品购买成功";
        } else {
            $this->error("此页面不存在");
        }
        $this->assign("order_sn", $order_sn);
        $this->display();
    }

    /*
     * 支付不成功
     */
    public function nook()
    {
        $this->display();
    }
    /**********************提交商品订单start*********************************/
    /*
     * 已经拍到的商品去付款
     */
    public function finishorder()
    {
        //以后可以做多个商品传递过来,现在只实现一次一个订单
        $order_id = I("get.id", 0, "intval");
        //分配本商品的订单id,不是单号
        $this->assign("order_id", $order_id);
        if (!is_numeric($order_id) || $order_id == 0) {
            $this->error("此页面不存在");
        }
        //分配商品
        $this->goods = M('PaimaiOrder')->where(array('order_id' => $order_id))->find();
        //p($this->goods);
        //收货地址省
        $this->province = M("Region")->field("id,name")->where(array('pid' => 2))->select();
        //p($this->province);
        $this->display();
    }

    /*
     * 用户填写收货地址等等,确认订单的控制器
     */
    public function confirm_order()
    {
        //走这一条路的传过来的是yishu_paimai_order对应的id
        $uid = $_SESSION['user_auth']['uid'];
        $order_id = I("post.id", 0);
        //接受order_id进数据库查看这条数据对应的uid和当前的session中的uid是否相同,
        $orderObj = M("PaimaiOrder");
        $order_uid = $orderObj->field("order_uid")->where(array('order_id' => $order_id))->getFiele("order_uid");
        if ($uid != $order_uid) $this->error("你请求的页面不存在");

        //这里最好用个自动验证和字段映射
        //省
        $orderinfodata['orderinfo_province'] = I("post.province", '0', 'intval');
        //市
        $orderinfodata['orderinfo_city'] = I("post.city", '0', 'intval');
        //详细地址
        $orderinfodata['orderinfo_address'] = I("post.address", '', 'strip_tags');
        //固定电话
        $orderinfodata['orderinfo_tel'] = I("post.tel", '', 'strip_tags');
        //移动电话
        $orderinfodata['orderinfo_mobile'] = I("post.phone", '', 'strip_tags');
        //邮箱
        $orderinfodata['orderinfo_email'] = I("post.email", '', 'strip_tags');
        //邮编
        $orderinfodata['orderinfo_zipcode'] = I("post.zipcode", '', 'strip_tags');
        //收件人名字
        $orderinfodata['orderinfo_reciver'] = I("post.name", '', 'strip_tags');
        //订单创建时间
        $orderinfodata['orderinfo_createtime'] = time();
        //本订单状态,为1,即本订单已经确认
        $orderinfodata['orderinfo_status'] = 1;
        //付款方式1为支付宝
        $orderinfodata['orderinfo_paystyle'] = I("post.pay_style", 0, 'intval');
        //订单号
        $orderinfodata['orderinfo_sn'] = $this->creategoodsordersn();
        //订单总金额
        $total_money = I('post.total_money', 0.00, 'floatval');
        //把金额都转成xx.00形式的
        $orderinfodata['orderinfo_amount'] = number_format($total_money, 2, '.', '');
        //订单所有者
        $uid = $_SESSION['user_auth']['uid'];
        if (!$uid) {
            $this->error("请重新登录", U('Member/Passport/login'));
        }
        $orderinfodata['orderinfo_uid'] = $uid;
        $orderinfo = D('PaimaiOrderInfo');
        //自动验证
        if ($orderinfo->create($orderinfodata)) {
            //向yishu_paimai_order_info中添加数据
            if ($order_info_id = $orderinfo->data($orderinfodata)->add()) {
                //把刚才生成的yishu_paimai_order_info刚生成的id写到这个商品的订单表中,让商品order关联order_info,并把order_status状态改为1,为已经提交订单但还没有付款
                if ($orderObj->where(array("order_id" => $order_id, 'order_status' => 1))->data(array('order_orderinfoid' => $order_info_id))->save()) {
                    //跳转支付页面,带刚才生成的订单返回的id号,和这个支付类型,如果类型为1,则为充值,如果为2,则才支付商品
                    $this->redirect(U("Pay/Pay/doalipay", array('id' => $order_info_id, 'style' => 2), ''));
                }

            }
        } else {
            $this->error($orderinfo->getError());
        }
        //p($orderinfodata);
        //本商品的形成yishu_paimai_order的id号
        //$order_id=I("post.order", '', 'strip_tags');
        //接受订单信息进行入yishu_paimai_order_info表
    }

    /*
     * 创建唯一充值订单号
     */
    public function creategoodsordersn()
    {
        //创建唯一订单号
        $orderinfo = M("PaimaiOrderInfo");
        $info_sn = 'OI' . date("Ymd") . mt_rand(10000, 99999);
        return $orderinfo->where("info_sn=$info_sn")->count() ? $this->creategoodsordersn() : $info_sn;
    }

    /*
     * 收货地址ajax返回省市
     */
    public function ajax_getregion()
    {
        $pid = I("get.id", 0, "intval");
        //传入pid返回省市的名字和id
        $regionArr = M("Region")->where(array('pid' => $pid))->select();
        echo json_encode($regionArr);
    }
    /**********************提交商品订单end*********************************/


}