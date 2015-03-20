<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 支付方式模型
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Model;

class PaymentModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('pay_name', 'require', '请填写支付方式名称！', 1),
        array('pay_name', '', '支付方式名称已存在！', 1, 'unique'),
        array('pay_desc', 'require', '请填写支付方式描述！', 1),
        array('pay_config', 'require', '请填写支付方式配置！', 1),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
    );
}