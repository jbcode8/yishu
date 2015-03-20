<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 发货地址模型
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Model;

class ShoppingAddressModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('consignee', 'require', '请填写收货人！'),
        array('consignee', '', '收货人已存在！', 2, 'unique'),
        array('address', 'require', '请填写地址！'),
        array('postcode', 'require', '请填写邮编！'),
        array('mobile', 'require', '请填写手机！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
    );
}
