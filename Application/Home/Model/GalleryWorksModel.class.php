<?php
// +----------------------------------------------------------------------
// | 广告模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------
namespace Home\Model;
use Think\Model;

class GalleryWorksModel extends GalleryModel
{
    protected $tableName = 'works';
    /**
     * 获取作品
     * @param  integer $map 条件
     * @param  integer $limit 大师数
     * @return array
     */
    public function getWorks($limit, $map)
    {
        $gallery_works = $this->field('id,recordid,name')->limit($limit)->order('update_time DESC')->where($map)->select();
        foreach($gallery_works as $k=>$v) {
            $gallery_works[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        return $gallery_works;
    }
}