<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖类别_[后台管理]_模型(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Model;
use Think\Model;

class AuctionCategoryModel extends Model {
    
    /**
     * 自动验证
     */
    protected $_validate = array(
        array('name', 'require', '请填写类别的名称！'),
    );
    
    /**
     * 自动完成
     */
    protected $_auto = array(
        array('createtime', 'time', 1, 'function'),
        array('updatetime', 'time', 2, 'function'),
    );
    
    /**
     * 生成鉴定类别缓存
     */
    public function createAuctionCategoryCache(){
        
        $arr = M('AuctionCategory')->field('id, name')->select();
        foreach($arr as $v){ $data[$v['id']] = $v; }
        S('AuctionCategory', $data);
    }
    
    /**
     * 自动执行：在新增、修改、删除后自动重新生成缓存
     */
    public function _after_insert($data, $options) { $this->createAuctionCategoryCache(); }
    public function _after_update($data, $options) { $this->createAuctionCategoryCache(); }
    public function _after_delete($data, $options) { $this->createAuctionCategoryCache(); }
    
}
