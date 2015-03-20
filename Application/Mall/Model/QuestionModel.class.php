<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 店铺咨询模型
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Model;

class QuestionModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('content', 'require', '请填写内容！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
    );
} 