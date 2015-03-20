<?php

// +----------------------------------------------------------------------
// | 鉴定模块_鉴定信息_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Model;

class IdentifyDataModel extends BaseModel {
    
    /**
     * 自动验证
     */
    protected $_validate = array(

        array('name', 'require', '请填写鉴定物品的名称！', 0),
        array('category', 'require', '请选择鉴定物品的分类！', 0),
        array('size', 'require', '请填写鉴定物品的尺寸！', 0),
        array('keywords', 'require', '请填写鉴定物品的关键字！', 0),
        array('thumb', 'require', '请选择鉴定物品的缩略图！', 0),
//        array('pics', 'require', '请上传鉴定物品的图片！', 1),
        array('question', 'require', '请填写对鉴定物品提出的问题！', 0),
        array('answer', 'require', '请填写对鉴定物品的鉴定内容！', 0),
    );

    /**
     * 自动完成
     */
    protected $_auto = array(
        array('createtime', 'time', 1, 'function'),
        array('isok', 'getIsok', 2, 'callback'),
        array('mid', 'getMid', 1, 'callback'), // 回调函数获取ID
    );
    
    /**
     * 获取 登录用户的ID
     */
    public function getMid(){
        
        return session('mid');
    }
    
    /**
     * 获取 鉴定信息的鉴定状态
     */
    public function getIsok(){
        return $isok = MODULE_NAME === 'AdminData' ? 1 : 0;
    }
    
}
