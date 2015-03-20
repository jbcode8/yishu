<?php
// +----------------------------------------------------------------------
// | 专访模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Model;


class InterviewModel extends ArtistModel{

    protected $_validate = array(
        array('aid', 'require', '艺术姓名不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('title', 'require', '事件标题不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('description', 'require', '描述不能为空', self::VALUE_VALIDATE, '',self::MODEL_BOTH ),
        array('video', 'url', '视频地址格式有误', self::VALUE_VALIDATE, '',self::MODEL_BOTH ),
        array('cid', 'require', '类别不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('thumb', 'require', '没有头像', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
    );

    protected $_auto = array(
        array('createtime', 'time', self::MODEL_INSERT, 'function'),
        array('updatetime', 'time', self::MODEL_BOTH, 'function'),
        array('hits', 0, self::MODEL_INSERT, 'string'),
    );
} 