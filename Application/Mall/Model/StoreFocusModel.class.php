<?php

// +--------------------------------
// | 店铺焦点图 模型
// +--------------------------------
// | Author: Rain.Zen
// +--------------------------------

namespace Mall\Model;

class StoreFocusModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('store_id', 'require', '请填写店铺ID！'),
        array('title', 'require', '请填写焦点图标题！'),
        array('img', 'require', '请填写焦点图图片！'),
        array('url', 'require', '请填写焦点图链接！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
    );
}