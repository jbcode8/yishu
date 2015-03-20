<?php

// +----------------------------------------------------------------------
// | 画廊管理 模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.21
// +----------------------------------------------------------------------

namespace Gallery\Model;
use Home\Model\DocumentModel;

class GalleryListModel extends BaseModel{

    // 自动验证
    protected $_validate = array(
        array('uid', 'require', '请填写画廊关联的ID！'),
        array('name', 'require', '请填写画廊名称！'),
        array('name', '', '此画廊名称已存在！', 1, 'unique'),
        array('cate_id', 'require', '请选择画廊类别！'),
        array('region_id', 'require', '请选择画廊地区！'),
        array('address', 'require', '请填写详细地址！'),
        array('thumb', 'require', '请选择缩略图！'),
        array('found', 'require', '请填写成立时间！'),
        array('keywords', 'require', '请填写关键字！'),
        array('desc', 'require', '请填写画廊简介！'),
        array('phone', 'require', '请填写成联系电话！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time','time',1,'function'),
        array('update_time','time',2,'function'),
    );

    // 添加之后更新缓存
    public function _after_insert($data, $options) {
        $this->caches();
    }

    // 修改之后更新缓存
    public function _after_update($data, $options) {
        $this->caches();
    }

    // 删除之后更新缓存
    public function _after_delete($data, $options){
        $this->caches();
    }

    // 创建缓存数据
    function caches($isReturn = false){
        $cache = array();
        $arr = $this->where('`status` > 0')->field(array('id', 'uid', 'name'))->order('listorder ASC')->select();
        if(!empty($arr)){
            foreach($arr as $rs){$cache[$rs['id']] = $rs;}
        }
        S('GalleryList', $cache);
        if($isReturn){ return $cache; }
    }

    // 获取缓存数据且返回
    function getCaches(){

        // 获取缓存数据
        $arrCache = S('GalleryList');

        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }
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
        $gallery_list = $this->field('id,name,recordid,address,create_time,desc,found')->limit($limit)->order('update_time DESC')->where($map)->select();
        if($gallery_list){
            foreach($gallery_list as $k=>$v) {
                $gallery_list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            }
            return $gallery_list;
        }else{
            return null;
        }
    }
}