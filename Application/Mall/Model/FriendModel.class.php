<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 友情链接模型
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Model;

class FriendModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('title', 'require', '请填写链接标题！', 1),
        array('title', '', '链接标题已存在！', 1, 'unique'),
        array('url', 'require', '请填写链接地址！', 1),
        array('store_id', 'require', '请选择店铺！', 0), // 0：存在字段就验证(默认)
        array('desc', 'require', '请填写链接描述！', 1),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 2, 'function'),
    );
}