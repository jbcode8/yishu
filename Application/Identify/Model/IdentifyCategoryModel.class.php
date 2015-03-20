<?php

// +----------------------------------------------------------------------
// | 鉴定模块_鉴定类别_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Model;
use Think\Model;
class IdentifyCategoryModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(

        array('name', 'require', '请填写鉴定类别的名称！'),
    );
    
    /**
     * 自动完成
     */
    protected $_auto = array(
        array('createtime', 'time', 1, 'function'),
        array('updatetime', 'time', 2, 'function'),
    );
    
    /**
     * 生成鉴定类别缓存
     */
    public function createIdentifyCategoryCache(){
        
        $arr = M('IdentifyCategory')->field('id, name')->select();
        foreach($arr as $v){ $data[$v['id']] = $v; }
        S('IdentifyCategory', $data);
    }
    
    /**
     * 自动执行：在新增、修改、删除后自动重新生成缓存
     */
    public function _after_insert($data, $options) { $this->createIdentifyCategoryCache(); }
    public function _after_update($data, $options) { $this->createIdentifyCategoryCache(); }
    public function _after_delete($data, $options) { $this->createIdentifyCategoryCache(); }
    
}
