<?php
// +----------------------------------------------------------------------
// | 广告模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------
namespace Home\Model;
use Think\Model;

class GalleryListModel extends GalleryModel
{
    protected $tableName = 'list';

    /**
     * 获取画廊详细信息
     * @param  integer $id 画廊id
     * @param  array $field 查询画廊字段
     * @return array
     */
    public function getGalleryInfo($id, $field)
    {
        $map['id'] = $id;
        $map['status']  = array('neq',0);
        return $this->field($field)->where($map)->find();
    }
    /**
     * 获取画廊
     * @param  string $map 画廊数
     * @param  integer $limit 画廊数
     * @return array
     */
    public function getGallery($limit, $map)
    {
        $gallery_list = $this->field('id,name,recordid')->limit($limit)->order('update_time DESC')->where($map)->select();
        foreach($gallery_list as $k=>$v) {
            $gallery_list[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        return $gallery_list;
    }
}