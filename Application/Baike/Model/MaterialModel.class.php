<?php
// +----------------------------------------------------------------------
// | 艺术百科参考资料模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Model;


class MaterialModel extends BaikeModel
{
    /*
    表名:	yishu_baike_material	    艺术百科参考资料表
    mid	 int(10) unsigned			 NO	 PRI auto_increment	 参考资料ID标识
    did	 mediumint(8) unsigned		 0	 NO	 MUL	 所属词条ID标识
    authorid	 int(10) unsigned			 NO	 MUL
    name	 varchar(200)	 utf8_general_ci		 NO		 资料名称
    url	 varchar(200)	 utf8_general_ci		 NO		 资料的url路径
    time	 int(10) unsigned		 0	 NO		 添加时间
    edits	 mediumint(8) unsigned		 0	 NO		 编辑次数
    edtions	 mediumint(8) unsigned		 1	 NO		 版本个数
    lasttime	 int(10) unsigned		 0	 NO		 最后修改时间
    locked	 tinyint(1) unsigned		 0	 NO		 是否被锁定（ 1 锁定 ，0 未被锁定 ）
    visible	 tinyint(1) unsigned		 0	 NO		 是否通过审核
    cid	 smallint(6) unsigned		 0	 NO	 MUL	 资料所属词条分类
     */
    protected $_validate = array(
        array('name', 'require', '参考资料名称不能为空',),
        array('url', 'require', 'url 路径不能为空',),
        array('url', 'check_url', 'url 格式不正确', 'callback'),
    );

    protected $_auto = array(
        array('time', 'time', self::MODEL_INSERT, 'function'),
        array('lasttime', 'time', self::MODEL_BOTH, 'function'),
        array('edits', 0, self::MODEL_INSERT, 'string'),
        array('edtions', 0, self::MODEL_INSERT, 'string'),
        array('locked', 0, self::MODEL_INSERT, 'string'),
        array('visible', 0, self::MODEL_INSERT, 'string'),
        array('authorid', UID, self::MODEL_INSERT, 'string'),
    );

    //检测url
    public function check_url($data)
    {
        return checkURL($data);
    }
}