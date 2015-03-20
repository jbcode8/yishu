<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 文章模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.28
// +----------------------------------------------------------------------

namespace Mall\Model;


class ArticleModel extends MallBaseModel{
    
    // 自动验证
    protected $_validate = array(
        array('title', 'require', '请填写文章标题！', 1),
        array('title', '', '文章标题已存在！', 1, 'unique'),
        array('cate_id', 'require', '请选择类别！', 1),
        array('store_id', 'require', '请选择店铺！', 0), // 0：存在字段就验证(默认)
        array('content', 'require', '请填写文章内容！', 1),
        array('listorder', 'number', '排列顺序必须大于零(0)！', 1),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 2, 'function'),
    );
}