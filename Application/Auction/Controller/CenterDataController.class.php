<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[会员中心]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;

use Member\Controller\MemberController;

class CenterDataController extends MemberController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionData');
    }

    /**
     * 列表信息
     */
    public function index() {
        
        // 条件语句
        $where['mid'] = session('mid');
        
        // 先获取出价记录表含有此会员ID的出价记录，返回拍卖信息ID，且清除重复
        $arrAid = M('AuctionPriceRecord')->field('DISTINCT aid')->where($where)->select();
        
        // 先判断是否有参与的拍卖信息
        if(is_array($arrAid) && !empty($arrAid)){
            
            // 将数组解析为字符串
            $aids = $this->parseAid($arrAid);
            
            // 条件语句
            $where['id'] = array('IN',$aids);
            
            // 分页信息
            $this->page = $this->pages($where);
            
            // 列表列表
            $this->list = $this->Model->where($where)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();
        }

        $this->display('Center:data_index');
    }
    
    /**
     * 将二维数组的拍卖信息ID组合为字符串，用于IN语句
     */
    public function parseAid($arr){
        foreach($arr as $rs){
            $str .= ','.$rs['aid'];
        }
        return trim($str, ',');
    }
}
