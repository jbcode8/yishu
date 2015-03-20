<?php
// +----------------------------------------------------------------------
// | 广告模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------
namespace Home\Model;
use Think\Model;

class GalleryVisitModel extends GalleryModel
{
    protected $tableName = 'visit';

    /**
     * 获取访谈
     * @param  array $map 条件
     * @param  integer $limit 访谈数
     * @return array
     */
    public function getVisit($limit, $map)
    {
        $gallery_visit = M('GalleryVisit')->field('id,title,recordid,brief')->limit($limit)->order('update_time DESC')->where($map)->select();
        foreach($gallery_visit as $k=>$v) {
            $gallery_visit[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        return $gallery_visit;
    }
}