<?php
// +----------------------------------------------------------------------
// | 拍卖评论模型
// +----------------------------------------------------------------------
// | Author: mahongbing
// +----------------------------------------------------------------------
namespace Auction\Model;
use Think\Model;
class AuctionComment extends Model {

    /**
     * 自动验证
     */
    protected $_validate = array(
        array('content', 'require', '请填写评论内容！',1),
        array('auctionid', 'require', '请选择评论对应的拍卖主题！',1),
        array('mid', 'getmid', '请先登录！',1,'funciton')
    );


    /**
     * 自动完成
     */
    protected $_auto = array(
        array('createtime', 'time', 1, 'function'),
        array('isopen', '0', 1, 'string')
    );


    protected $_map = array(
        'aid' =>'auctionid',
    );

   public function getmid(){
       return session('mid') || flase;
   }




}
