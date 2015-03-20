<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Model;
use Think\Model;

class AuctionAgencyModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(

        array('name', 'require', '请填写机构名称！'),
        array('thumb', 'require', '请上传机构图片！'),
        array('areaid', 'require', '请选择区域！'),
		array('address', 'require', '请填写地址！'),
		array('keywords', 'require', '请填写关键字！'),
		array('description', 'require', '请填写描述！'),
		array('content', 'require', '请填写详细介绍！'),
        array('tel', 'require', '请填写电话！'),
        array('post', 'require', '请填写邮编！'),
        array('email', 'require', '请填写邮箱！'),
        array('website', 'require', '请填写网址！'),
    );

	/**
     * 自动完成
     */
    protected $_auto = array(
        array('createtime', 'time', 1, 'function'),
        array('updatetime', 'time', 2, 'function'),
    );
}
