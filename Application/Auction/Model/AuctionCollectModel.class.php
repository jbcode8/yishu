<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Model;
use Think\Model;
class AuctionCollectModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(
        array('agencyid', 'require', '请选择拍卖机构！'),
        array('title', 'require', '请填写标题！'),
        array('endtime', 'require', '请选择截止日期！'),
        array('range[]', 'require', '请选择征集范围！'),
        array('process', 'require', '请填写征集流程！'),
        array('relation', 'require', '请填写联系人！'),
        array('contact', 'require', '请填写联系方式！'),
       // array('images', 'require', '测试测试！！'),
    );
}
