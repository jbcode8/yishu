<?php
// +----------------------------------------------------------------------
// | 画廊作品 模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.22
// +----------------------------------------------------------------------

namespace Gallery\Model;
use Home\Model\DocumentModel;

class GalleryWorksModel extends BaseModel{

    // 自动验证
    protected $_validate = array(
        array('gid', 'require', '请选择画廊！'),
        array('name', 'require', '请作品名称！'),
        array('cate_id', 'require', '请选择作品类别！'),
        array('aid', 'require', '请选择艺术家！'),
        array('brief', 'require', '请填写作品简介！'),
        array('thumb', 'require', '请选择作品缩略图！'),
        array('creation', 'require', '请填写创作年代！'),
        array('material', 'require', '请填写作品材质！'),
        array('size', 'require', '请填写作品尺寸！'),
		array('price', 'require', '请填写作品价格！'),
		array('phone', 'require', '请填写作品联系电话！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time','time',1,'function'),
        array('update_time','time',2,'function'),
    );

    /**
     * 获取作品
     * @param  integer $map 条件
     * @param  integer $limit 作品数
     * @return array
     */
    public function getWorks($limit, $map)
    {
        $gallery_works = $this->field('id,recordid,name,aid,gid,size,price,phone,create_time')->limit($limit)->order('update_time DESC')->where($map)->select();
        if($gallery_works){
            foreach($gallery_works as $k=>$v) {
                $gallery_works[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
                $gallery_works[$k]['gname'] = getGname($v['gid']);
                $gallery_works[$k]['aname'] = getName($v['aid']);
            }
            return $gallery_works;
        }else{
            return null;
        }
    }

}