<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 属性类型模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.29
// +----------------------------------------------------------------------

namespace Mall\Model;

class AttributeModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('attr_name', 'require', '请填写属性类型！'),
        array('attr_name', '', '此属性类型已经存在！', 0, 'unique'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
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
        $arr = $this->field(array('attr_id', 'attr_name', 'cate_id'))->select();
        $data = array();
        foreach($arr as $rs){
            $data[$rs['attr_id']] = $rs;
        }
        S('Attribute', $data);
        if($isReturn){ return $data; }
    }

    // 获取缓存数据且返回
    function getAttributeCache(){

        // 获取缓存数据
        $arrCache = S('Attribute');

        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }
}