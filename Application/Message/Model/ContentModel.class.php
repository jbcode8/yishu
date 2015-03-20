<?php

namespace Message\Model;

/**
 * Content模型
 */
class ContentModel extends MessageModel
{
    protected $_validate = array(
        array('title', 'require', '留言标题不能为空'),
        array('content', 'require', '留言内容不能为空'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time','time',1,'function'),
    );

}
