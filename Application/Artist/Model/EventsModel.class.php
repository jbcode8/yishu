<?php
// +----------------------------------------------------------------------
// | 作品模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Model;


class EventsModel extends ArtistModel{

    protected $_validate = array(
        array('aid', 'require', '艺术姓名不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('eventtime', 'require', '事件时间不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('title', 'require', '事件标题不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('content', 'require', '内容不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
    );

    protected $_auto = array(
        array('eventtime', 'strtotime', self::MODEL_BOTH, 'function'),
        array('createtime', 'time', self::MODEL_INSERT, 'function'),
        array('updatetime', 'time', self::MODEL_UPDATE, 'function'),
        array('hits', 0, self::MODEL_INSERT, 'string'),
    );


} 