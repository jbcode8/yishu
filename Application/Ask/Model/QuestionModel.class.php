<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-20
 * Time: 下午4:17
 */

namespace Ask\Model;


class QuestionModel extends AskModel{

    /**
     *	自动验证
     */

    protected $_validate = array(
        array('title','require','问题不能为空!',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
        array('cate_id','require','类别名称不能为空!',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
    );
    /**
     *	自动完成
     */
    protected $_auto = array(
        array('input_time', NOW_TIME, self::MODEL_INSERT),
    );

} 