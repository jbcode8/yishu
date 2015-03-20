<?php

// +----------------------------------------------------------------------
// | 鉴定模块_鉴定评论_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Model;
use Think\Model;
class IdentifyCommentModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(

        array('username', 'require', '请填写评论者昵称！'),
        array('content', 'require', '请填写评论内容！'),
        array('identifyid', 'require', '请选择评论对应的鉴定主题！'),
    );
    
    /**
     * 自动完成
     */
    protected $_auto = array(
        array('createtime', 'time', 1, 'function'),
    );
    
}
