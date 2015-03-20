<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Mobile\Controller;

use Mobile\Controller\MobieController;

class SpecialController extends MobileController
{
    public function _initialize()
    {
        parent::_initialize();
		$this->title="专场";
        $this->keywords="专场";
        $this->desc="专场";
    }
    /**
     * 拍卖专场
     */
    public function index()
    {
	    $special_id = I("get.special_id", 0, "intval");
        $this->special_id=$special_id;
		
		$where = array('special_id' => $special_id, 'special_isshow' => 1);
		$field = 'special_name, special_endtime, recordid';
		
		//获取专场
		$special = M("PaimaiSpecial")->field($field)->where($where)->find();
		
		$this->title = $special['special_name']."_拍卖专场_中国艺术网在线竞拍";
		$this->keywords = $special['special_name'].",拍卖专场";
    	$this->desc = "中国艺术网在线竞拍隆重上线".$special['special_name']."拍卖专场活动，玩家可以在".$special['special_name']."拍卖专场上拍卖到最新最有价值的藏品，数量有限，先到先得，中国艺术网在线竞拍安全交易，值得信赖。";
		
		//图片
		$special['thumb'] = D('Content/Document')->getPic($special['recordid'], 'thumb');
        if (empty($special)) {
            $this->error("此拍卖会不存在");
            exit;
        }
		$this->assign("special", $special);
		
		//拍品
		$field_goods = 'goods_id, goods_name, goods_nowprice, goods_bidtimes, recordid';
		$where_goods = array('goods_specialid' => $special_id, 'goods_isshow' => 1, 'goods_isdelete' => 0);
		$order_goods = 'goods_id desc';
		$goods_list = M('PaimaiGoods')->field($field_goods)->where($where_goods)->order($order_goods)->select();
		$goods_count = M('PaimaiGoods')->where($where_goods)->count();
        $this->assign("goods_list", $goods_list);
		$this->assign("goods_count", $goods_count);
		foreach($goods_list as $k => $v){
		    $goods_list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
		}
		$this->assign("goods_list", $goods_list);
		$this->display("Front:special");
    }
}
