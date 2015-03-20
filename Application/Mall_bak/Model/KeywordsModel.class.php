<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 搜索关键字模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.18
// +----------------------------------------------------------------------

namespace Mall\Model;

class KeywordsModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('words', 'require', '请输入关键字！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function')
    );

} 