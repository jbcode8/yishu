<?php
// +----------------------------------------------------------------------
// | 作品模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Model;


class WorksModel extends ArtistModel{

    protected $_validate = array(
        array('aid', 'require', '艺术家姓名不能为空', self::MUST_VALIDATE, '',self::MODEL_INSERT ),
        array('aid', 'number', '艺术家姓名不存在', self::VALUE_VALIDATE, 'regex',self::MODEL_BOTH ),
        array('name', 'require', '作品名称不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('thumb', 'require', '没有头像', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('cid', 'require', '类别不能为空', self::MUST_VALIDATE, '',self::MODEL_BOTH ),
        array('years', '/^[1-9][0-9]{3}$/', '创作年代日期格式有误，只能为4为数字', self::VALUE_VALIDATE, 'regex',self::MODEL_BOTH ),
        array('award', 'number', '获奖名称有误', self::VALUE_VALIDATE, 'regex',self::MODEL_BOTH ),
        array('description', 'require', '描述不能为空', self::VALUE_VALIDATE, '',self::MODEL_BOTH ),
    );

    protected $_auto = array(
       // array('pictureurls', 'serializeImg', self::MODEL_BOTH, 'callback'),
        array('createtime', 'time', self::MODEL_INSERT, 'function'),
        array('updatetime', 'time', self::MODEL_BOTH, 'function'),
        //array('hits', 0, self::MODEL_INSERT, 'string'),
    );

    /**
     * 获取作品
     * @param  integer $limit 大师数
     * @param  array $map 查询条件
     * @return array
     */
    public function getWorks($limit, $map)
    {
        $works_list = $this->field('id,name,recordid,aid,size,description')->where($map)->order('createtime DESC')->limit($limit)->select();
        foreach($works_list as $k=>$v) {
            $works_list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        return $works_list;
    }

    /**
     * 根据分类获取作品数
     * @param  integer $cid 分类ID
     * @return array
     */
    public function getWorksNumByCid($cid){
        $works_num = $this->where('cid='.$cid)->getField('count(id) as count');
        return $works_num;
    }



}