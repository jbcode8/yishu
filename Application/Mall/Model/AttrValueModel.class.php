<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 属性值 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.29
// +----------------------------------------------------------------------

namespace Mall\Model;

class AttrValueModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('attr_id', 'require', '请选择属性名！'),
        array('val_name', 'require', '请填写属性值！'),
        array('val_name', '', '此属性值已经存在！', 0, 'unique'),
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
        $arr = $this->field(array('attr_val_id', 'val_name', 'attr_id'))->select();
        S('AttrValue', $arr);
        if($isReturn){ return $arr; }
    }

    // 获取缓存数据且返回
    function getAttrValueCache(){

        // 获取缓存数据
        $arrCache = S('AttrValue');

        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }
}