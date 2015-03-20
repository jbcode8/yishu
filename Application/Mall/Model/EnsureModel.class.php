<?php

// +---------------------
// | 保障信息 模型
// +---------------------
// | Author: Rain.Zen
// +---------------------

namespace Mall\Model;

class EnsureModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('name', 'require', '请填写名称！'),
        array('name', '', '此名称已存在！', 0, 'unique'),
        array('logo', 'require', '请选择LOGO！'),
        array('desc', 'require', '请填写描述！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function')
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
        $arr = $this->field(array('id', 'name', 'logo'))->where(array('status' => '1'))->order('listorder ASC')->select();
        $ary = array();
        foreach($arr as $rs){
            $ary[$rs['id']] = $rs;
        }
        S('MallEnsure', $ary);
        if($isReturn){ return $ary; }
    }

    // 获取缓存数据且返回
    function getEntrueCache(){

        // 获取缓存数据
        $arrCache = S('MallEnsure');

        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }
}