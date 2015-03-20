<?php
//+------------------------------------------------
//|	问答模块_评论回复_模型(CURD)
//+------------------------------------------------
//|	Author: tangyong <695841701@qq.com>
//-------------------------------------------------

namespace Ask\Model;


class ReplyModel extends AskModel{
    /**
     *	自动验证
     */
    protected $_validate = array(
        array('content', 'require', '内容不能为空!', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     *	自动完成
     */
    protected $_auto = array(
        array('input_time', NOW_TIME, self::MODEL_INSERT),
    );
} 