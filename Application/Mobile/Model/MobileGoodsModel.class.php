<?php

namespace Mobile\Model;

use Think\Model;

/**
 * wap拍卖专场模型
 */
class MobileGoodsModel extends MobileModel{
	//拍品收藏数
	public function getCollectedNum($goods_id){
	    return M('paimai_collect')->where(array('collect_goodsid'=>$goods_id))->count();
	}

	//收藏列表
	public function getCollections($mid = 0, $p = 1, $page_num = 10){
	    if(!$mid){
			return false;
		}
		$collect_field = array(
			'pc.collect_id',
			'pg.goods_name',
			'pg.goods_id',
			'pg.goods_nowprice',
			'pg.goods_bidtimes',
			'pg.recordid'
		);
		$collect_list = M('paimai_collect')->alias('pc')->join('yishu_paimai_goods pg on pc.collect_goodsid = pg.goods_id')->field($collect_field)->order('pc.collect_id desc')->where(array('collect_uid' => $mid))->limit(($p-1)*$page_num . ',' . $page_num)->select();
		foreach ($collect_list as $k => $v) {
			$collect_list[$k]['goods_name'] = substr_CN($collect_list[$k]['goods_name'], 20);
            $collect_list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
		return $collect_list;
	}

	//进行中的拍品
	public function getBidingGoods($mid = 0, $p = 1, $page_num = 10){
		if(!$mid){
			return false;
		}
		$bid_field = array(
			'pg.goods_id',
		    'pg.goods_name',
			'pg.goods_nowprice',
			'pb.bidrecord_time',
			'pg.recordid'
		);
		$bid_where = array(
		    'bidrecord_uid' => $mid,
			'goods_successid' => 0,
			'goods_endtime' => array('GT', time())
		);

		$biding_list = M('paimai_goods')->alias('pg')->join('yishu_paimai_bidrecord pb on pb.bidrecord_goodsid = pg.goods_id')->field($bid_field)->where($bid_where)->order('bidrecord_id desc')->limit(($p-1)*$page_num . ',' . $page_num)->select();
		foreach($biding_list as &$val){
			$val['goods_name'] = substr_CN($val['goods_name'], 20);
			$val['bidrecord_time'] = date('Y-m-d H:i', $val['bidrecord_time']);
			$val['thumb'] = $this->getPic($val['recordid'], 'thumb');
		}
		return $biding_list;
	}

	//拍到的拍品
    public function getAuctionedGoods($mid = 0, $p = 1, $page_num = 10){
		if(!$mid){
		    return false;
		}
		$order_obj = M('paimai_order');
		$order_field = array(
			'order_id',
			'order_goodsid',
			'order_goodsbidtime',
			'order_goodsname',
			'order_goodssn',
			'order_goodsrecordid',
			'order_goodsnowprice',
			'order_status'
		);
		$order_where = array(
			'order_uid' => $mid,
			'order_status' => array('NEQ', 2)
		);
		$order_list = $order_obj->field($order_field)->where($order_where)->order('order_id desc')->limit(($p-1)*$page_num . ',' . $page_num)->select();
		foreach($order_list as $k => $v){
			switch($v['order_status']){
			    case 0 : $order_list[$k]['order_status'] = '订单未提交';break;
				case 1 : $order_list[$k]['order_status'] = '订单未付款';break;
				case 3 : $order_list[$k]['order_status'] = '订单已过期';break;
				default : $order_list[$k]['order_status'] = '';
			}
				
			$order_list[$k]['order_goodsbidtime'] = date('Y-m-d H:i',$order_list[$k]['order_goodsbidtime']);
			$order_list[$k]['order_goodsname'] = substr_CN($order_list[$k]['order_goodsname'],20);
			$order_list[$k]['thumb'] = $this->getPic($v['order_goodsrecordid'], 'thumb');
		}
        return $order_list;
	}

	//购买到的拍品
	public function getBuyedGoods($mid = 0, $p = 1, $page_num = 10){
	    if(!$mid){
		    return false;
		}
		$order_field = array(
		    'order_goodsid',
			'order_goodsbidtime',
			'order_goodsname',
			'order_goodsrecordid',
			'order_goodsnowprice'
		);
		$order_where = array(
		    'order_uid' => $mid,
			'order_status' => 2
		);
		$buyed_list = M('paimai_order')->field($order_field)->where($order_where)->order('order_orderinfoid desc')->limit(($p-1)*$page_num . ',' . $page_num)->select();
		foreach($buyed_list as $k => $v){
			$buyed_list[$k]['order_goodsbidtime'] = date('Y-m-d H:i',$buyed_list[$k]['order_goodsbidtime']);
			$buyed_list[$k]['order_goodsname'] = substr_CN($buyed_list[$k]['order_goodsname'],20);
			$buyed_list[$k]['thumb'] = $this->getPic($v['order_goodsrecordid'], 'thumb');
		}
		return $buyed_list;
	}

	//参拍过的拍品
	public function getJoinedGoods($mid = 0, $p = 1, $page_num = 10){
	    if(!$mid){
		    return false;
		}
		$joined_filed = array(
		    'r.bidrecord_price',
			'r.bidrecord_goodsid',
			'r.bidrecord_time',
			'g.goods_name',
			'g.recordid'
		);
		$joined_where = array(
		    'r.bidrecord_uid' => $mid,
			'g.goods_endtime' => array('LT', time())
		);
		$joined_list = M('paimai_bidrecord')->alias('r')->join('yishu_paimai_goods g on r.bidrecord_goodsid = g.goods_id')->field($joined_filed)->where($joined_where)->limit(($p-1)*$page_num . ',' . $page_num)->group('r.bidrecord_goodsid')->order('bidrecord_id desc')->select();

		foreach($joined_list as $k => &$v){
			$v['goods_name'] = substr_CN($v['goods_name'], 20);
			$v['bidrecord_time'] = date('Y-m-d H:i', $v['bidrecord_time']);
		    $v['thumb'] = $this->getPic($v['recordid'], 'thumb');
		}
		return $joined_list;
	}

	//订单信息
	public function getOrderInfo($order_id){
	    $order_field = array(
		    'order_id',
			'order_goodsid',
			'order_goodsname',
			'order_goodsnowprice',
			'order_createtime',
			'order_goodsrecordid'
		);
		$order_info = M('paimai_order')->field($order_field)->where(array('order_id' => $order_id))->find();
		$order_info['order_goodsname'] = substr_CN($order_info['order_goodsname'], 20);
		$order_info['order_createtime'] = date('Y-m-d H:i', $order_info['order_createtime']);
		$order_info['thumb'] = D('Mobile')->getPic($order_info['order_goodsrecordid'], 'thumb');

		return $order_info;
	}
} 