<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-20
 * Time: 上午9:57
 */

namespace Member\Model;


use Think\Model;

class PaimaiOrderInfoModel extends Model
{
    /*
     * 创建唯一一个订单号
     */
    //自动验证
    protected $_validate = array(
        array("orderinfo_province", "0", "请选择省份", 0, 'notequal'),
        array("orderinfo_city", "0", "请选择城市", 0, 'notequal'),
        array("orderinfo_address", "require", "请填写详细地址"),
        array("orderinfo_address", "1,255", "详细地址不能为空,则小于200个字符", "3", "length"),
        array("orderinfo_reciver", "require", "请填写收货人名字"),
        array("orderinfo_reciver", "1,20", "收件人姓名不能为空,则小于20个字符", "3", "length"),
        array("orderinfo_email", "require", "邮箱不能为空"),
        array("orderinfo_email", "email", "请正确填写邮箱"),
        array("orderinfo_email", "1,40", "邮箱不能超过40个字符", "3", "length"),
        array("orderinfo_zipcode", "require", "请填写邮编"),
        array("orderinfo_zipcode", "1,5", "邮编不能超过5个字符", "3", "length"),

    );
    //自动完成
    protected $_auto = array(
        array("orderinfo_createtime", "time", "1", "function"),
    );

    public function eee()
    {
        echo "3额3";
    }
} 