<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 配送方式模型
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Model;

class DeliveryModel extends MallBaseModel{
    
    // 自动验证
    protected $_validate = array(
        array('delivery_name', 'require', '请填写配送方式名称！', 1),
        array('delivery_name', '', '配送方式名称已存在！', 1, 'unique'),
        array('delivery_desc', 'require', '请填写配送方式描述！', 1),
        array('delivery_price', 'require', '请填写配送方式价格！', 1),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
    );
}