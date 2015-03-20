<?php
// +----------------------------------------------------------------------
// | 广告模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------
namespace Home\Model;
use Think\Model;

class GalleryArtistModel extends GalleryModel
{
    protected $tableName = 'artist';
    /**
     * 通过艺术家id获取作家姓名
     * @param  integer $id 艺术家id
     * @return array
     */
    public function getOneArtist($id) {
        return $this->where(array('id'=>$id))->getField('name');
    }
    /**
     * 获取艺术家信息
     * @param  array $map 条件
     * @param  integer $limit 大师数
     * @return array
     */
    public function getArtist($limit, $map)
    {
        $gallery_artist= $this->field('id,recordid,name')->limit($limit)->order('create_time DESC')->where($map)->select();
        //$gallery_artist= $this->field('id,recordid,name')->limit($limit)->order('create_time DESC')->where(array('status'=>array('neq',0)))->select();
        foreach($gallery_artist as $k=>$v) {
            $gallery_artist[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        return $gallery_artist;
    }


}