<?php
// +----------------------------------------------------------------------
// | 画廊访谈 模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.02.07
// +----------------------------------------------------------------------

namespace Gallery\Model;
use Home\Model\DocumentModel;

class GalleryVisitModel extends BaseModel{

    // 自动验证
    protected $_validate = array(
        array('gid', 'require', '请选择画廊！'),
        array('title', 'require', '请填写标题！'),
        array('cate_id', 'require', '请选择类别！'),
        array('brief', 'require', '请填写简介！'),
        array('thumb', 'require', '请选择缩略图！'),
        array('video', 'require', '请填写访谈视频地址！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time','time',1,'function'),
        array('update_time','time',2,'function'),
    );

    /**
     * 获取访谈
     * @param  array $map 条件
     * @param  integer $limit 访谈数
     * @return array
     */
    public function getVisit($limit, $map)
    {
        $gallery_visit = $this->field('id,title,recordid,brief,gid')->limit($limit)->order('update_time DESC')->where($map)->select();
        if($gallery_visit){
            foreach($gallery_visit as $k=>$v) {
                $gallery_visit[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            }
            return $gallery_visit;
        }else{
            return null;
        }
    }

}