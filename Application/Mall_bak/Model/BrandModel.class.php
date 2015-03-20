<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 品牌模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.23
// +----------------------------------------------------------------------

namespace Mall\Model;

class BrandModel extends MallBaseModel{
    
    // 自动验证
    protected $_validate = array(
        array('cate_id', 'require', '请选择类别！'),
        array('brand_name', 'require', '请填写品牌名称！'),
        array('brand_name', '', '此品牌名称已存在！', 0, 'unique'),
        array('listorder', 'number', '排列顺序必须大于零(0)！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 2, 'function'),
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
        $arr = $this->field(array('cate_id', 'brand_id', 'brand_name', 'brand_logo', 'listorder'))->where(array('status' => '1'))->order('listorder ASC')->select();
        S('BrandCategory', $arr);
        if($isReturn){ return $arr; }
    }
    
    // 获取缓存数据且返回
    function getBrandCache(){
        
        // 获取缓存数据
        $arrCache = S('BrandCategory');
        
        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }
}