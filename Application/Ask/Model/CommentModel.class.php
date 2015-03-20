<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-19
 * Time: 下午2:30
 */
//+------------------------------------------------
//|	问答模块_评论表_模型(CURD)
//+------------------------------------------------
//|	Author: songfeilong <414545427@qq.com>
//-------------------------------------------------

namespace Ask\Model;

class CommentModel extends AskModel{
    /**
     *	自动验证
     */
    protected $_validate = array(
        array('content', 'require', '评论内容不能为空!', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     *	自动完成
     */
    protected $_auto = array(
        array('input_time', NOW_TIME, self::MODEL_INSERT),

    );
}

?>