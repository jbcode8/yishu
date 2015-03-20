<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Model;
use Think\Model;
class AuctionDeliveryModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(
    	array('name', 'require', '请填写藏品名称！'),
    	array('category', 'require', '请选择藏品分类！'),
    	array('size', 'require', '请填写藏品尺寸！'),
    	array('intact', 'require', '请填写藏品品相！'),
    	array('price', 'require', '请填写保留价！'),
    	array('remark', 'require', '请填写备注！'),
        array('images', 'require', '请上传头像'),
        array('uploader', 'require', '请填写您的姓名！'),
        array('city', 'require', '请选择您的城市！'),
    );
}
