<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Model;
use Think\Model;
class AuctionGoodsModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(

        array('name', 'require', '请填写产品名称！'),
        array('category', 'require', '请选择产品分类！'),
        array('meetingid', 'require', '请选择专场！'),
        array('size', 'require', '请填写产品尺寸！'),
        array('weight', 'require', '请填写产品重量！'),
        array('price', 'require', '请填写产品价格！'),
        array('stock', 'require', '请填写产品库存！'),
        array('keywords', 'require', '请填写产品关键字！'),
        array('brief', 'require', '请填写产品简述！'),
        array('content', 'require', '请填写产品详细信息！'),
        array('thumb', 'require', '请选择产品缩略图！'),
    );

    /**
     * 自动完成
     */
    protected $_auto = array(
        array('addtime', 'time', 1, 'function'),
        array('updatetime', 'time', 2, 'function'),
        array('sn', 'getSn', 1, 'callback'), // 回调函数获取ID
    );
    
    /**
     * 货号(随机生成6位数)
     * @param type $data
     */
    public function getSn(){
        return 'GN'.rand(111111,999999);
    }
}
