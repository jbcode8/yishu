<?php
// +----------------------------------------------------------------------
// | 艺术百科参考资料版本库模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Model;

class EditionMaterialModel extends BaikeModel
{
    /*
    meid	 int(10) unsigned			 NO	 PRI auto_increment	 版本ID 标识
    mid	 mediumint(1) unsigned			 NO	 MUL	 版本资料ID标识
    name	 varchar(200)	 cp850_general_ci		 NO		 资料名称
    url	 varchar(200)	 utf8_general_ci		 NO		 资料访问地址
    authorid	 mediumint(8) unsigned		 0	 NO		 资料版本创建者ID标识
    did	 mediumint(8) unsigned		 0	 NO	 MUL	 治疗所属词条ID标识
    visible	 tinyint(1) unsigned		 0	 NO		 是否通过审核（ 1 通过 ，0 未通过）
    excellent	 tinyint(1) unsigned		 0	 NO		 是否为优秀版本
    time	 int(10) unsigned		 0	 NO		 版本创建时间
    cid	 smallint(6) unsigned		 0	 NO	 MUL	 资料版本所属词条分类
     */

    protected $_validate = array(
        array('name', 'require', '参考资料名称不能为空',),
        array('url', 'require', 'url 路径不能为空',),
        array('url', 'check_url', 'url 格式不正确', 'callback'),
    );

    protected $_auto = array(
        array('time', 'time', self::MODEL_INSERT, 'function'),
        array('excellent', 0, self::MODEL_INSERT, 'string'),
        array('visible', 0, self::MODEL_INSERT, 'string'),
        array('authorid', UID, self::MODEL_INSERT, 'string'),
    );

    //检测url
    public function check_url($data)
    {
        return checkURL($data);
    }

}