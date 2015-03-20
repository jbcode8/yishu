<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Model;
use Think\Model;
class AuctionClientModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(

        array('name', 'require', '请填写名称！'),
        array('thumb', 'require', '请上传图片！'),
        array('website', 'require', '请填写网址！'),
    );
}
