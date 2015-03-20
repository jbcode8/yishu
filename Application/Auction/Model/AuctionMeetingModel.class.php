<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Model;
use Think\Model;
class AuctionMeetingModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(

        array('name', 'require', '请填写名称！'),
        array('agencyid', 'require', '请选择机构！'),
        //array('meetingid', 'require', '请选择专场！'),
        array('areaid', 'require', '请选择城市！'),
        array('thumb', 'require', '请上传图片！'),
        //array('video', 'require', '请上传视屏！'),
        array('pre_address', 'require', '请填写预展地址！'),
        array('starttime', 'require', '请选择拍卖开始时间！'),
        array('endtime', 'require', '请选择拍卖结束时间！'),
        array('address', 'require', '请填写拍卖地址！'),
        array('pid', 'require', '请选择拍卖会！'),
    );
	
	/**
     * 自动完成
     */
    protected $_auto = array(
        array('pre_starttime', 'str2time', 3, 'callback'),
        array('pre_endtime', 'str2time', 3, 'callback'),
       
    );
    
    /**
     * 处理字符串时间为数值
     */
    public function str2time($data){
        return strtotime($data.':00');
    }


}
