<?php
// +----------------------------------------------------------------------
// | 画廊类别 模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.22
// +----------------------------------------------------------------------

namespace Gallery\Model;

class GalleryCategoryModel extends BaseModel{

    // 自动验证
    protected $_validate = array(
        array('name', 'require', '请填写类别名！'),
        array('name', '', '此类别名已存在！', 1, 'unique'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time','time',1,'function'),
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
        $arr = $this->where(array('status'=>1))->field(array('id', 'name'))->order('listorder ASC')->select();
        if(!empty($arr)){
            foreach($arr as $rs){$cache[$rs['id']] = $rs;}
        }
        S('GalleryCategory', $cache);
        if($isReturn){ return $cache; }
    }

    // 获取缓存数据且返回
    function getCaches(){

        // 获取缓存数据
        $arrCache = S('GalleryCategory');

        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }

    public function getCateName($catid){
        return $this->where(array('id'=>$catid))->getField('name');
    }
}