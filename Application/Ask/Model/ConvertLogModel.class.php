<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-19
 * Time: 下午2:42
 */

namespace Ask\Model;

class ConvertLogModel extends AskModel{
    /**
     *	自动完成
     */
    protected $_auto = array(
        array('input_time', NOW_TIME, self::MODEL_INSERT),
    );
} 