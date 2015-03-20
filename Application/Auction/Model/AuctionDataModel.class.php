<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Model;
use Think\Model;
class AuctionDataModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(

        array('title', 'require', '请填写拍卖名称！'),
        array('desc', 'require', '请填写拍卖简述！'),
        array('gid', 'require', '请选择拍卖产品！'),
        array('startprice', 'require', '请填写起拍价格！'),
        array('eachprice', 'require', '请填写加价幅度！'),
        array('needmoney', 'require', '请填写保证金！'),
        array('needintegral', 'require', '请填写所需积分！'),
    );

    /**
     * 自动完成
     */
    protected $_auto = array(
        array('starttime', 'str2time', 3, 'callback'),
        array('endtime', 'str2time', 3, 'callback'),
        array('addtime', 'time', 1, 'function'),
        array('updatetime', 'time', 2, 'function'),
    );
    
    /**
     * 处理字符串时间为数值
     */
    public function str2time($data){
        return strtotime($data.':00');
    }
    
    /**
     * 处理竞拍口号
     */
    public function arrSlogan(){
        return array(
            1 => '钞票在手，竞价我有',
            2 => '不得宝贝，誓不罢休',
            3 => '放开那个宝贝，让我来',
            4 => '此宝贝，跟进很久，承让',
        );
    }
}
