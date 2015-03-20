<?php

// +----------------------------------------------------------------------
// | 鉴定模块_鉴定专家_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Model;
use Think\Model;
class IdentifyExpertModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(

        array('username', 'require', '请填写鉴定专家的姓名！'),
        array('category', 'require', '请选择鉴定专家的分类！'),
        array('thumb', 'require', '请选择鉴定专家的头像！'),
        array('brief', 'require', '请填写鉴定专家的简介！'),
    );
    
    /**
     * 自动完成
     */
    protected $_auto = array(
        array('createtime', 'time', 1, 'function'),
    );
    
    /**
     * 生成鉴定类别缓存
     */
    public function createIdentifyExpertCache(){
        
        $arr = M('IdentifyExpert')->field('id, username')->select();
        foreach($arr as $v){ $data[$v['id']] = $v; }
        S('IdentifyExpert', $data);
    }
    
    /**
     * 自动执行：在新增、修改、删除后自动重新生成缓存
     */
    public function _after_insert($data, $options) { $this->createIdentifyExpertCache(); }
    public function _after_update($data, $options) { $this->createIdentifyExpertCache(); }
    public function _after_delete($data, $options) { $this->createIdentifyExpertCache(); }
    
}
