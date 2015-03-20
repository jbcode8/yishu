<?php

/**
 * 手机APP 拍卖接口
 * @author Usagi
 */

namespace Api\Controller;

Class AuctionController extends BaseController {
	//填写方法  get传一个参数：verify=1
	/**
	 * 推荐专场列表
	 * @return array
	 */
	public function hotAuctionList(){
	    $ingspecial_field=array(
            'special_id',
            'special_name',
            'special_endtime',
            'special_starttime',
            'special_hits',
			'special_praise',
            'recordid',
        );

        $ingspecial_where=array(
            'special_isshow'=>1,
            'special_isdelete'=>0,
        );
        $ingspecial_where['special_starttime']=array('LT',time());//开拍时间比现在时间大
        $ingspecial_where['special_endtime']=array('GT',time());//结束时间比现在时间大

        $ingspecial_limit=20;

        $ingspecial_arr=M('PaimaiSpecial')->field($ingspecial_field)->where($ingspecial_where)->limit($ingspecial_limit)->order('special_order desc,special_id asc')->select();
        foreach ($ingspecial_arr as $k => &$v) {
            //得到本专场下面商品的竞拍次数
            $v['bidtimes']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_bidtimes");
            //点击量
            $v['hits']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_hits");
			//点赞数
			$goods_id_arr = M('PaimaiGoods')->field('goods_id')->where('goods_specialid=' . $v['special_id'])->select();
			$goods_praise_sum = 0;
			foreach($goods_id_arr as $gk => $gv){
			    $goods_praise_sum += D('Mobile/MobileGoods')->getCollectedNum($gv['goods_id']);
			}
			$v['special_praise'] += $goods_praise_sum;
            //得到图片
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
			//得到拍品数
			$where_goods = array('goods_specialid' => $v['special_id'], 'goods_isshow' => 1, 'goods_isdelete' => 0);
			$v['goods_count'] = M('PaimaiGoods')->where($where_goods)->count();
        }
		$return = array('result' => true, 'data' => $ingspecial_arr, 'code' => 1);
        $this->ajaxReturn($return);
	}

	/**
	 * 今日22:00专场列表
	 * @return array
	 */
	public function today22AuctionList(){
	    $startspecial_field=array(
            'special_id',
            'special_name',
            'special_endtime',
            'special_starttime',
            'special_hits',
			'special_praise',
            'recordid',
        );
        $startspecial_where=array(
            'special_isshow'=>1,
            'special_isdelete'=>0,
        );
		if(date('H') >22 || date('H')==22 ){
		    $startspecial_where['special_starttime'] = array('between',array(strtotime(date("Y-m-d", time()))+22*3600+1, strtotime(date("Y-m-d", time()))+46*3600+1));
		}else{
		    $startspecial_where['special_starttime'] = array('between',array(strtotime(date("Y-m-d", time()))+22*3600-1, strtotime(date("Y-m-d", time()))+46*3600-1));
		}
        
        $startspecial_limit=10;
        $startspecial_arr=M('PaimaiSpecial')->field($startspecial_field)->where($startspecial_where)->order('special_order desc,special_id asc')->limit($startspecial_limit)->select();
        
        foreach ($startspecial_arr as $k => &$v) {
            //得到本专场下面商品的竞拍次数
            $v['bidtimes']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_bidtimes");
            //点击量
            $v['hits']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_hits");
			//点赞数
			$goods_id_arr = M('PaimaiGoods')->field('goods_id')->where('goods_specialid=' . $v['special_id'])->select();
			$goods_praise_sum = 0;
			foreach($goods_id_arr as $gk => $gv){
			    $goods_praise_sum += D('Mobile/MobileGoods')->getCollectedNum($gv['goods_id']);
			}
			$v['special_praise'] += $goods_praise_sum;
			//图片
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
			//得到拍品数
			$where_goods = array('goods_specialid' => $v['special_id'], 'goods_isshow' => 1, 'goods_isdelete' => 0);
			$v['goods_count'] = M('PaimaiGoods')->where($where_goods)->count();
        }
		$return = array('result' => true, 'data' => $startspecial_arr, 'code' => 1);
        $this->ajaxReturn($return);
	}

	/**
	 * 专场点赞
	 * @param  int $special_id 专场id
	 * @return array
	 */
	public function specialPraise($special_id = 0){
	    if(!$special_id){
			$return = array('result' => false, 'data' => '缺少专场id', 'code' => 2);
		    $this->ajaxReturn($return);
		}
		if(M('PaimaiSpecial')->where("special_id = $special_id")->setInc('special_praise')){
		    $return = array('result' => true, 'data' => '点赞成功', 'code' => 1);
		}else{
		    $return = array('result' => false, 'data' => '点赞失败', 'code' => 3);
		}
		$this->ajaxReturn($return);
	}

	/**
	 * 专场拍品列表
	 * @param  int $special_id   专场id
	 * @param  int $order_type   排序方式 0 - 默认 1 - 出价次数 2 - 价格
	 * @param  int $p            页码 
	 * @param  int $count        每页记录数
	 * @return array
	 */
	public function specialGoodsList($special_id = 0, $order_type = 0, $p = 1, $count = 10){
	    if(!$special_id){
			$return = array('result' => false, 'data' => '缺少专场id', 'code' => 2);
		    $this->ajaxReturn($return);
		}
		//拍品
		$field_goods = 'goods_id, goods_name, goods_nowprice, goods_bidtimes, goods_starttime, goods_endtime, recordid';
		$where_goods = array('goods_specialid' => $special_id, 'goods_isshow' => 1, 'goods_isdelete' => 0);
		switch($order_type){
		    case 0: $order_goods = 'goods_id desc'; break;
			case 1: $order_goods = 'goods_bidtimes desc'; break;
			case 2: $order_goods = 'goods_nowprice desc'; break;
			default: $order_goods = 'goods_id desc';
		}
		$goods_list = M('PaimaiGoods')->field($field_goods)->where($where_goods)->order($order_goods)->limit(($p-1)*$count . ',' . $count)->select();

		if(!$goods_list){
		    $return = array('result' => false, 'data' => '查无记录', 'code' => 3);
		}else{
			foreach($goods_list as $k => $v){
			    $goods_list[$k]['thumb'] = $this->getPic($v['recordid']);
			}
			$return = array('result' => true, 'data' => $goods_list, 'code' => 1);
		}
		$this->ajaxReturn($return);
	}

	/**
	 * 拍品详情
	 * @param  int $goods_id 拍品id
	 * @param  int $uid      用户id 用于获得收藏与否 及 设置的代理价信息
	 * @return array
	 */
	public function goodsDetail($goods_id = 0, $uid = 0){
	    if(!$goods_id){
		    $return = array('result' => false, 'data' => '缺少拍品id', 'code' => 2);
		    $this->ajaxReturn($return);
		}
		
		//商品信息
		$goods_field = 'goods_id, goods_name, goods_sn, goods_brief, goods_intro, goods_nowprice, goods_startprice, goods_needmoney, goods_everyprice, goods_starttime, goods_endtime, third_platform, goods_hits, recordid, goods_sellername, goods_size, goods_place, goods_weight, goods_bidtimes, goods_specialid';
        $goods_where = array(
			'goods_id'=>$goods_id,
			'goods_isshow'=>1,
			'goods_isdelete'=>0
		);
		$goods_obj = M('PaimaiGoods');
        $goods_info = $goods_obj->field($goods_field)->where($goods_where)->find();
		if(!$goods_info){
		    $return = array('result' => false, 'data' => '拍品不存在', 'code' => 3);
		    $this->ajaxReturn($return);
		}
		
		//拍品图
		$goods_info['thumb'] = D('Content/Document')->getPic($goods_info['recordid'], 'thumb', $goods_info['goods_id']);
		$third = $goods_info['third_platform'] ? 1 : 0;
		$goods_info['pics'] = D('Content/Document')->getPic($goods_info['recordid'], 'image', $goods_info["goods_id"], 5, $third);
		
		//收藏人数
		$goods_info['collect_num'] = D('Mobile/MobileGoods')->getCollectedNum($goods_info['goods_id']);
		
		if($uid){
			//是否已被当前用户收藏
		    $gid = M('paimai_collect')->where(array('collect_goodsid'=>$goods_id,'collect_uid'=>$uid))->getField('collect_id');
			if($gid){
				$goods_info['is_collect'] = true;
			}else{
				$goods_info['is_collect'] = false;
			}
			//用户设置的代理价
			$agent_price = M('PaimaiAutoagent')->where(array('autoagent_uid' => $uid, 'autoagent_goodsid' => $goods_id))->getField('autoagent_price');
			if($agent_price){
			    $goods_info['agent_price'] = $agent_price;
			}else{
			    $goods_info['agent_price'] = 0;
			}
		}else{
		    $goods_info['is_collect'] = false;
			$goods_info['agent_price'] = 0;
		}
		
		//设置提醒人数
		$goods_info['set_remind'] = M('paimai_remind')->where(array('gid' => $goods_info['goods_id']))->count();
		//围观
		$goods_info['circusee'] = $goods_info['goods_bidtimes'] + $goods_info['goods_hits'];
		//拍品介绍
		if(!$goods_info['third_platform']){ 
			$goods_intro = html_entity_decode($goods_info['goods_intro'], ENT_QUOTES, 'UTF-8');
		}else{
			$third = $goods_info['third_platform'] ? 1 : 0;
			$pic_arr = D('Content/Document')->getPic($goods_info['recordid'], 'image',$goods_info["goods_id"],100,$third);
			$pic_str = '';
			foreach($pic_arr as $val){
				$pic_str .= '<img alt="" src="'.$val.'" data-bd-imgshare-binded="1">';
			}
			$goods_intro = strip_tags(html_entity_decode($goods_info['goods_intro'], ENT_QUOTES, 'UTF-8')).$pic_str;
		}
		
		//同场推荐
		$other_field = 'goods_id, goods_name, goods_nowprice, recordid, goods_bidtimes';
		$other_where = array(
		    'goods_specialid' => $goods_info['goods_specialid'],
			'goods_id' => array('not in', $goods_info['goods_id']),
			'goods_isshow' => 1
		);
		$other_goods_list = $goods_obj->field($other_field)->where($other_where)->order('goods_id desc')->limit(2)->select();
		foreach($other_goods_list as $k => $v){
			$other_goods_list[$k]['thumb'] = $this->getPic($v['recordid']);
		}
		$data = array('goods_info' => $goods_info, 'other_goods_list' => $other_goods_list);
		
		$return = array('result' => true, 'data' => $data, 'code' => 1);
		$this->ajaxReturn($return);
	}

	/**
	 * 拍品出价记录
	 * @param  int $goods_id 拍品id
	 * @param  int $p        页码
	 * @param  int $count    每页记录数
	 * @return array   
	 */
	public function recordLog($goods_id = 0, $p = 1, $count = 10){
	    if(!$goods_id){
		    $return = array('result' => false, 'data' => '缺少拍品id', 'code' => 2);
		    $this->ajaxReturn($return);
		}
		$field = array(
            "bidrecord_id",
            'bidrecord_uname',
            'bidrecord_uid',
            'bidrecord_price',
            'bidrecord_time',
			'bidrecord_type'
        );
		$log = M("PaimaiBidrecord")->field($field)->where("bidrecord_goodsid=" . $goods_id)->order("bidrecord_id desc")->limit(($p-1)*$count . ',' . $count)->select();
		if(!$log){
		    $return = array('result' => false, 'data' => '暂无出价记录', 'code' => 3);
			$this->ajaxReturn($return);
		}

		//隐藏ip,换算时间
        foreach ($log as $k => $v) {
            if($v['bidrecord_uid'] == 0){
                $username = hideusername($v['bidrecord_uname']);
            }else{
                $username = getUsername($v['bidrecord_uid']);
            }
            $log[$k]['bidrecord_uname'] = $username;
        }
		$return = array('result' => true, 'data' => $log, 'code' => 1);
		$this->ajaxReturn($return);
	}

	/**
	 * 点赞（即pc端收藏功能）
	 * @param  int $goods_id  拍品id
	 * @param  int $uid       用户id
	 * @return array 
	 */
	public function setPraise($goods_id = 0, $uid = 0){
	    $params = array('goods_id' => $goods_id, 'uid' => $uid);
		if(!$this->verifyNeceParam($params)){
		    $return = array('result' => false, 'data' => '缺少必要参数', 'code' => 2);
			$this->ajaxReturn($return);
		}

		//是否已收藏
		$gid = M('paimai_collect')->where(array('collect_goodsid'=>$goods_id,'collect_uid'=>$uid))->getField('collect_id');
		if($gid){
		    $return = array('result' => false, 'data' => '已收藏过该拍品', 'code' => 3);
			$this->ajaxReturn($return);
		}

		$data = array(
			'collect_goodsid' => $goods_id,
			'collect_uid' => $uid,
			'collect_time' => time()
		);
		if(M('paimai_collect')->add($data)){
			$return = array('result' => true, 'data' => '收藏成功', 'code' => 1);
			$this->ajaxReturn($return);
		}else{
			$return = array('result' => false, 'data' => '收藏失败', 'code' => 4);
			$this->ajaxReturn($return);
		}
	}

	/**
	 * 用户正在进行的拍品列表
	 * @param  int $uid   用户id
	 * @param  int $p     页码
	 * @param  int $count 记录数
	 */
	public function bidingGoodsList($uid = 0, $p = 1, $count = 10){
	    if(!$uid){
		    $return = array('result' => false, 'data' => '缺少必要参数', 'code' => 2);
			$this->ajaxReturn($return);
		}
		$biding_field = 'goods_id, goods_sn, goods_name, goods_starttime, goods_endtime, goods_nowprice, recordid, bidrecord_id, bidrecord_price, bidrecord_time, bidrecord_goodsid';
		$biding_where = array('bidrecord_uid' => $uid, 'goods_successid' => 0, 'goods_endtime' => array('GT', time()));
		$biding_list = M('PaimaiGoods')->join('yishu_paimai_bidrecord on bidrecord_goodsid = goods_id')->field($biding_field)->where($biding_where)->order('bidrecord_id desc')->limit(($p-1)*$count . ',' . $count)->select();
		if(!$biding_list){
		    $return = array('result' => false, 'data' => '暂无记录', 'code' => 3);
			$this->ajaxReturn($return);
		}
		foreach($biding_list as &$v){
		    $v['thumb'] = $this->getPic($v['recordid']);
		}
		$return = array('result' => true, 'data' => $biding_list, 'code' => 1);
		$this->ajaxReturn($return);
	}

	/**
	 * 用户成功拍到的订单列表信息
	 * @param  int $uid    用户id
	 * @param  int $p      页码
	 * @param  int $count  每页记录数
	 * @param  int $status 订单支付状态 0 - 未付款 1 - 已付款
	 * @return array
	 */
	public function auctionGoodsList($uid = 0, $p = 1, $count = 10, $status = 0){
	    if(!$uid){
		    $return = array('result' => false, 'data' => '缺少必要参数', 'code' => 2);
			$this->ajaxReturn($return);
		}

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
		if($status == 0){
			$order_where = array(
				'order_uid' => $uid,
				'order_status' => array('NEQ', 2)
			);
		}else{
			$order_where = array(
				'order_uid' => $uid,
				'order_status' => array('EQ', 2)
			);
		}
		
		$order_list = M('paimai_order')->field($order_field)->where($order_where)->order('order_id desc')->limit(($p-1)*$count . ',' . $count)->select();
		foreach($order_list as $k => $v){
			$order_list[$k]['thumb'] = $this->getPic($v['order_goodsrecordid'], 'thumb');
		}
		if($order_list){
			$return = array('result' => true, 'data' => $order_list, 'code' => 1);
		}else{
		    $return = array('result' => false, 'data' => '暂无数据', 'code' => 3);
		}
		$this->ajaxReturn($return);
	}

	/**
	 * 用户参与过的拍品列表
	 * @param  int $uid   用户id
	 * @param  int $p     当前页码
	 * @param  int $count 记录数
	 * @return array
	 */
	public function joinedGoodsList($uid = 0, $p = 1, $count = 10){
		if(!$uid){
		    $return = array('result' => false, 'data' => '缺少必要参数', 'code' => 2);
			$this->ajaxReturn($return);
		}
		$joined_field = 'goods_id, goods_name, goods_starttime, goods_endtime, recordid, bidrecord_id, bidrecord_goodsid, bidrecord_price, bidrecord_time';
		$joined_where = array('bidrecord_uid' => $uid, 'goods_endtime' => array('LT', time()));
		$mod_bidrecord = M('PaimaiBidrecord');
		$sub_query = $mod_bidrecord->join('yishu_paimai_goods on bidrecord_goodsid = goods_id')->field($joined_field)->where($joined_where)->order('bidrecord_id desc')->buildSql();
		$list = $mod_bidrecord->table($sub_query . ' r')->limit(($p-1)*$count . ',' . $count)->group('goods_id')->order('bidrecord_id desc')->select();
		if(!$list){
		    $return = array('result' => false, 'data' => '暂无参与过的拍品', 'code' => 3);
			$this->ajaxReturn($return);
		}
		foreach($list as &$v){
		    $v['thumb'] = $this->getPic($v['recordid']);
		}
		$return = array('result' => true, 'data' => $list, 'code' => 1);
		$this->ajaxReturn($return);
	}


	/**
	 * 订单详情
	 * @param  int $order_id  订单id
	 * @return array
	 */
	public function orderDetail($order_id = 0){
	    if(!$order_id){
		    $return = array('result' => false, 'data' => '缺少必要参数', 'code' => 2);
			$this->ajaxReturn($return);
		}

		$order_field = array(
		    'order_id',
			'order_orderinfoid',
			'order_goodsid',
			'order_goodsname',
			'order_goodsnowprice',
			'order_createtime',
			'order_goodsrecordid'
		);
		$order_info = M('paimai_order')->field($order_field)->where(array('order_id' => $order_id))->find();
		$order_info['thumb'] = $this->getPic($order_info['order_goodsrecordid'], 'thumb');
		if($order_info['order_orderinfoid']){
		    $info_field = 'orderinfo_sn, orderinfo_provincename, orderinfo_cityname, orderinfo_address, orderinfo_mobile, orderinfo_paystyle, orderinfo_status, orderinfo_tobuyer, orderinfo_paystatus, orderinfo_kd';
			$other_info = M('PaimaiOrderInfo')->field($info_field)->where('orderinfo_id =' . $order_info['order_orderinfoid'])->find();
			$order_info['other_info'] = $other_info;
		}
		if($order_info){
		    $return = array('result' => true, 'data' => $order_info, 'code' => 1);
		}else{
		    $return = array('result' => false, 'data' => '查无此单', 'code' => 3);
		}
		$this->ajaxReturn($return);
	}

	/**
	 * 物流信息
	 * @param  int $express_num 快递单号
	 * @return array
	 */
	public function expressInfo($express_num = 0){
	    if(!$express_num){
		    $return = array('result' => false, 'data' => '缺少快递单号', 'code' => 2);
			$this->ajaxReturn($return);
		}
		$express = new \Org\Util\Express;
        $res = $express->getorder($express_num);
		if($res){
			$return = array('result' => true, 'data' => $res, 'code' => 1);
		}else{
		    $return = array('result' => false, 'data' => '单号有误', 'code' => 4);
		}
		$this->ajaxReturn($return);
	}

	/**
	 * 提交订单
	 * @param  int   $order_id   订单id
	 * @param  int   $pay_style  支付方式
	 * @param  int   $uid        用户id
	 * @param  int   $addr_id    收货地址id
	 * @param  float $amount     订单金额
	 * @return bool
	 */
	public function submitOrder($order_id = 0, $pay_style = 0, $uid = 0, $addr_id = 0, $amount = 0){
		$params = array('order_id' => $order_id, 'pay_style' => $pay_style, 'uid' => $uid, 'addr_id' => $addr_id, 'amount' => $amount);
		if(!$this->verifyNeceParam($params)){
		    $return = array('result' => false, 'data' => '缺少必要参数', 'code' => 2);
			$this->ajaxReturn($return);
		}
		$order_obj = M('PaimaiOrder');
		$order_uid = $order_obj->where(array('order_id' => $order_id))->getField('order_uid');
		if($order_uid != $uid){
		    $return = array('result' => false, 'data' => '用户id有误', 'code' => 3);
			$this->ajaxReturn($return);
		}
		$address = M('Address', 'bsm_', 'DB_BSM')->find($addr_id);
		$amount = floatval($amount);
		$orderinfo_data = array(
		    'orderinfo_province' => $address['address_provinceid'],//省id
			'orderinfo_provincename' => $address['address_provincename'],
			'orderinfo_city' => $address['address_cityid'],//市id
			'orderinfo_cityname' => $address['address_cityname'],
			'orderinfo_address' => $address['address_address'],//详细地址
			'orderinfo_mobile' => $address['address_mobile'],//手机号
			'orderinfo_zipcode' => $address['address_zipcode'],//邮编
			'orderinfo_reciver' => $address['address_receiver'],//收货人名字
			'orderinfo_createtime' => time(),//订单信息表创建时间
			'orderinfo_status' => 5,//5为已经提交订单
			'orderinfo_paystyle' => $pay_style,//付款方式,1为支付宝
			'orderinfo_sn' => $this->createOrderSn(),//订单号
			'orderinfo_amount' => format_money($amount),//订单金额
			'orderinfo_uid' => $uid,//订单属主id
		);
		if($order_info_id = M('PaimaiOrderInfo')->add($orderinfo_data)){
			if($order_obj->where(array("order_id" => $order_id))->data(array('order_orderinfoid' => $order_info_id, 'order_status' => 1))->save()){
			    $return = array('result' => true, 'data' => '订单提交成功', 'code' => 1);
			}else{
			    $return = array('result' => false, 'data' => '订单提交成功, 但订单状态修改失败', 'code' => 4);
			}
		}else{
		    $return = array('result' => false, 'data' => '订单提交失败', 'code' => 5);
		}
		$this->ajaxReturn($return);
	}

	/**
	 * 获取八大顶级分类及其子分类
	 * @return array
	 */
	public function auctionCate(){
		$field = array('cat_id','cat_name','cat_pid','cat_spell');
		$where = array(
		    'cat_pid' => 0,
			'cat_show_in_front' => 1,
			'cat_isdelete' => 0,
			'cat_id' => array('NEQ', 17)
		);
	    $top_cate_list = M('PaimaiCategory')->field($field)->where($where)->select();
		foreach($top_cate_list as $k => $v){
			$c_where = array(
				'cat_pid' => array('EQ', $v['cat_id']),
				'cat_show_in_front' => 1,
				'cat_isdelete' => 0
			);
		    $top_cate_list[$k]['children'] = M('PaimaiCategory')->field($field)->where($c_where)->select();
		}
		$return = array('result' => true, 'data' => $top_cate_list, 'code' => 1);
		$this->ajaxReturn($return);
	}

	/**
	 * 分类拍品列表
	 * @param  int $cat_id 分类id
	 * @param  int $status 筛选状态 0 - 正在进行 1 - 即将开始 2 - 已结束
	 * @param  int $p      页码     默认1
	 * @param  int $count  记录数   默认10
	 * @return array
	 */
	public function cateGoodsList($cat_id = 0, $status = 0, $p = 1, $count = 10){
	    if(!$cat_id){
		    $return = array('result' => false, 'data' => '缺少分类id', 'code' => 2);
			$this->ajaxReturn($return);
		}
		$where = array(
		    'goods_isshow' => 1
		);
		$where['goods_catid'] = $this->screenCateCondition($cat_id);
		
		switch($status){
		    case 0:
				$where['goods_starttime'] = array('LT', time());
				$where['goods_endtime'] = array('GT', time());
				break;
			case 1:
				$where['goods_starttime'] = array('GT', time());
				break;
			case 2:
				$where['goods_endtime'] = array('LT', time());
				break;
			default:;
		}
		$goods_field = array('goods_id, goods_name, goods_sn, goods_nowprice, goods_endtime, goods_starttime, goods_bidtimes, recordid');
		$goods_list = M('PaimaiGoods')->field($goods_field)->where($where)->order('goods_id desc')->limit(($p-1)*$count . ',' . $count)->select();
		foreach($goods_list as $k => $v){
		    $goods_list[$k]['thumb'] = $this->getPic($v['recordid']);
		}
		$return = array('result' => true, 'data' => $goods_list, 'code' => 1);
		$this->ajaxReturn($return);
	}
	
	/**
	 * 预拍拍品列表
	 * @param  int $p      页码   默认1 
	 * @param  int $count  记录数 默认10
	 * @param  int $cat_id 分类id 
	 * @return array
	 */
	public function prepareGoodsList($p = 1, $count = 10, $cat_id = 0){
	    $week_arr = array('日','一','二','三','四','五','六');
		$time_tmp = array(
		    strtotime('1 day'),
			strtotime('2 day'),
			strtotime('3 day'),
			strtotime('4 day'),
			strtotime('5 day'),
		);
		$day = array();
		for($i = 0; $i < 5; $i++){
			//日期
			$day[$i]['day'] = date('m-d', $time_tmp[$i]);
			//周几
			if($i == 0){
			    $day[$i]['weekday'] = '明天';
			}else if($i == 1){
			    $day[$i]['weekday'] = '后天';
			}else{
			    $day[$i]['weekday'] = '周' . $week_arr[date('w', $time_tmp[$i])];
			}
			//当日开始与结束时间戳
			$day[$i]['start_timestamp'] = strtotime(date('Ymd', $time_tmp[$i]));
			$day[$i]['end_timestamp'] = strtotime(date('Ymd', $time_tmp[$i]) + 1);
		}
		$where = array(
			'goods_isshow' => 1,
			'special_isdelete' => 0
		);
		
		//若有分类id 则添加分类筛选条件
		if($cat_id){
		    $where['goods_catid'] = $this->screenCateCondition($cat_id);
		}
		
		//后5天拍品数据
		foreach($day as $k => $v){
			$where['goods_starttime'] = array('between', array($v['start_timestamp'] - 2*3600 - 1, $v['end_timestamp'] - 10*3600 - 1));  
			$day[$k]['goods_list'] = M('PaimaiGoods')->field('goods_id, goods_name, goods_sn, goods_nowprice, goods_starttime, goods_endtime, goods_hits, recordid')->where($where)->limit(($p-1)*$count . ',' . $count)->select();
			foreach($day[$k]['goods_list'] as $key => $value){
			    $day[$k]['goods_list'][$key]['thumb'] = $this->getPic($value['recordid']);
			}
		}
		$return = array('result' => true, 'data' => $day, 'code' => 1);
		$this->ajaxReturn($return);
	}

	/**
	 * 设置代理价
	 * @param  int   $uid            用户id
	 * @param  float $agent_price    设置的代理价格
	 * @param  int   $goods_id       拍品id
	 * @return array
	 */
	public function setAgentPrice($uid = 0, $agent_price = 0, $goods_id = 0){
	    $params = array('uid' => $uid, 'goods_id' => $goods_id, 'agent_price' => $agent_price);
		if(!$this->verifyNeceParam($params)){
		    $return = array('result' => false, 'data' => '缺少必要参数', 'code' => 2);
			$this->ajaxReturn($return);
		}
		$goods_where = array('goods_id' => $goods_id, 'goods_isshow' => 1);
		$goods_field = 'goods_id, goods_nowprice, goods_starttime, goods_endtime, goods_bidtimes';
		$goods_info = M('PaimaiGoods')->field($goods_field)->where($goods_where)->find();
		if(!$goods_info){
		    $return = array('result' => false, 'data' => '商品不存在', 'code' => 3);
			$this->ajaxReturn($return);
		}
		if($goods_info['goods_starttime'] > time()){
			$return = array('result' => false, 'data' => '商品未开拍', 'code' => 4);
			$this->ajaxReturn($return);
		}
		if($goods_info['goods_endtime'] < time()){
			$return = array('result' => false, 'data' => '拍卖已结束', 'code' => 5);
			$this->ajaxReturn($return);
		}
		if($goods_info['goods_bidtimes'] != 0 && $goods_info['goods_nowprice'] >= $agent_price){
			$return = array('result' => false, 'data' => '代理价不得低于商品最新出价', 'code' => 6);
			$this->ajaxReturn($return);
        }
		$agent_data = array(
		    'autoagent_price' => $agent_price,
			'autoagent_goodsid' => $goods_id,
			'autoagent_uid' => $uid,
			'autoagent_createtime' => time()
		);
		$m_agent = M('PaimaiAutoagent');
		$agent_id = $m_agent->where(array('autoagent_uid' => $uid, 'autoagent_goodsid' => $goods_id))->getField('autoagent_id');
		if($agent_id){
		    $agent_data['autoagent_id'] = $agent_id;
			if($m_agent->save($agent_data)){
				$return = array('result' => true, 'data' => '代理价设置成功', 'code' => 1);
			}else{
			    $return = array('result' => false, 'data' => '代理价设置失败', 'code' => 7);
			}
		}else{
		    if($m_agent->create($agent_data)){
				if($m_agent->add()){
					$return = array('result' => true, 'data' => '代理价设置成功', 'code' => 1);
				}else{
					$return = array('result' => false, 'data' => '代理价设置失败', 'code' => 7);
				}
			}else{
				$return = array('result' => false, 'data' => '代理价设置失败', 'code' => 7);
			}
		}
		$this->ajaxReturn($return);
	}

	/**
	 * 查看用户设置的代理价
	 * @param  int   $uid      用户id
	 * @param  int   $goods_id 拍品id
	 * @return array
	 */
	public function userAgentPrice($uid = 0, $goods_id = 0){
	    $params = array('uid' => $uid, 'goods_id' => $goods_id);
		if(!$this->verifyNeceParam($params)){
		    $return = array('result' => false, 'data' => '缺少必要参数', 'code' => 2);
			$this->ajaxReturn($return);
		}
		$agent_price = M('PaimaiAutoagent')->where(array('autoagent_uid' => $uid, 'autoagent_goodsid' => $goods_id))->getField('autoagent_price');
		if($agent_price){
		    $return = array('result' => true, 'data' => $agent_price, 'code' => 1);
		}else{
		    $return = array('result' => false, 'data' => '查无记录', 'code' => 3);
		}
		$this->ajaxReturn($return);
	}
	
	/**
	 * 拍品出价
	 * @param  int $uid      出价者id
	 * @param  int $goods_id 拍品id
	 * @param  float $price  出价金额
	 * @return array
	 */
	public function bid($uid = 0, $goods_id = 0, $price = 0){
	    $params = array('uid' => $uid, 'goods_id' => $goods_id, 'price' => $price);
		if(!$this->verifyNeceParam($params)){
		    $return = array('result' => false, 'data' => '缺少必要参数', 'code' => 2);
			$this->ajaxReturn($return);
		}
		$new_bid_price = floatval($price);
		
		//拍品信息
		$goods_where = array(
		    'goods_isshow' => 1,
			'goods_id' => $goods_id
		);
		$goods_field = 'goods_id, goods_nowprice, goods_needmoney, goods_endtime, goods_bidtimes, goods_everypricestyle, goods_everyprice, goods_starttime, goods_cost';
		$goods = M('PaimaiGoods')->field($goods_field)->where($goods_where)->find();
		
		if(!$goods){
		    $return = array('result' => false, 'data' => '商品不存在', 'code' => 3);
			$this->ajaxReturn($return);
		}
		
		if($goods['goods_starttime'] > time()){
		    $return = array('result' => false, 'data' => '商品未开拍', 'code' => 4);
			$this->ajaxReturn($return);
		}
		
		if($goods['goods_endtime'] < time()){
		    $return = array('result' => false, 'data' => '商品已过期', 'code' => 5);
			$this->ajaxReturn($return);
		}

		//检查此次出价有效性
		if($goods['goods_bidtimes'] != 0 && $goods['goods_nowprice'] >= $new_bid_price){
		    $return = array('result' => false, 'data' => '商品最新出价已超越此次出价', 'code' => 6);
			$this->ajaxReturn($return);
		}
		
		//计算保证金
		$goods['goods_needmoney'] = getneedmoney($new_bid_price);
		//注册返现 (注册返现已作为优惠券发放，可直接用于付款，故注释之)
		/*$regist_amount = return_amount($uid);
		if($goods['goods_needmoney'] >= $regist_amount){
            $goods['goods_needmoney'] = $goods['goods_needmoney'] - $regist_amount;
        }*/

		//用户账户金额
		$user_amount = $this->getUserBalance($uid);
		//可用金额 = 账户余额 + 注册返现 (注册返现已作为优惠券发放，可直接用于付款，故注释之)
		//$can_use_amount = $user_amount + $regist_amount;
		
		//检查用户之前是否拍过此商品
		$bidrecord = M('PaimaiBidrecord');
		$bid_check_where = array(
		    'bidrecord_goodsid' => $goods_id,
			'bidrecord_uid' => $uid
		);
		$user_bid_check = $bidrecord->field('bidrecord_goodsneedmoney')->where($bid_check_where)->order('bidrecord_id desc')->find();
		if(empty($user_bid_check)){
		    //没拍过
			if($user_amount < $goods['goods_needmoney']){
			    $return = array('result' => false, 'data' => '余额不足', 'code' => 7);
				$this->ajaxReturn($return);
			}else{
				//如果扣除值 <= 0 就不执行插入
				if($goods['goods_needmoney'] > 0){
					if(!$this->deductDeposit($uid, $goods_id, $goods['goods_needmoney'], 4)){
						$return = array('result' => false, 'data' => '保证金扣除失败', 'code' => 8);
						$this->ajaxReturn($return);
					}
				}
			}
		}else{
		    //拍过
			$again_deposit = $goods['goods_needmoney'] - $user_bid_check['bidrecord_goodsneedmoney'];
			if($again_deposit > 0){
			    //需再次充值
				if($user_amount < $again_deposit){
				    $return = array('result' => false, 'data' => '余额不足', 'code' => 7);
					$this->ajaxReturn($return);
				}else{
					//如果扣除值 <= 0 就不执行插入
					if($goods['goods_needmoney'] > 0){
						if(!$this->deductDeposit($uid, $goods_id, $again_deposit, 5)){
							$return = array('result' => false, 'data' => '保证金扣除失败', 'code' => 8);
							$this->ajaxReturn($return);
						}
					}
				}
			}
		}
		//所有条件符合 入库
		$bidrecord_data = array(
		    'bidrecord_uid' => $uid,
			'bidrecord_price' => $new_bid_price,
			'bidrecord_goodsid' => $goods_id,
			'bidrecord_time' => time(),
			'bidrecord_goodsneedmoney' => $goods['goods_needmoney']
		);

		if($bidrecord->data($bidrecord_data)->add()){
		    $goods_everyprice = ($goods['goods_everypricestyle'] == 1) ? geteveryprice($new_bid_price) : $goods['goods_everyprice'];
			$mod_goods = D('Paimai/PaimaiGoods');
			$goods_where = array('goods_id' => $goods_id);
			$goods_data = array(
			    'goods_needmoney' => $goods['goods_needmoney'],
				'goods_everyprice' => $goods_everyprice,
				'goods_nowprice' => $new_bid_price,
				'goods_bidtimes' => $goods['goods_bidtimes'] + 1
			);
			//更新保证金和加价幅度
			$mod_goods->where($goods_where)->save($goods_data);

			//代理出价
			$agent_info = M('PaimaiAutoagent')->field('autoagent_price, autoagent_uid, autoagent_createtime')->order('autoagent_price desc, autoagent_createtime asc')->where(array('autoagent_uid' => array('NEQ', $uid), 'autoagent_goodsid' => $goods_id, 'autoagent_price' => array('EGT', $new_bid_price + $goods_everyprice)))->select();
			//成本价
			$g_cost = $goods['goods_cost'];
			$rnd_uname = '';
			$agent_biding_price = 0;
			if(count($agent_info) > 0){
				if(count($agent_info) == 1){
					if($g_cost > $agent_info[0]['autoagent_price']){
					    $tmp_price = $g_cost;
						$rnd_uname = $this->getAutoBidUname($goods_id);
					}else{
					    $tmp_price = $agent_info[0]['autoagent_price'];
					}
					//只有一人设置代理价
					$agent_biding_price = $tmp_price <= ($new_bid_price + $goods_everyprice) ? $tmp_price : ($new_bid_price + $goods_everyprice);
				}else{
					//多人设置代理价
					$price_arr = array($g_cost, $agent_info[0]['autoagent_price'], $agent_info[1]['autoagent_price']);
					rsort($price_arr, SORT_NUMERIC);
					$tmp_1st_price = $price_arr[0];
					$tmp_2nd_price = $price_arr[1];
					if($g_cost > $agent_info[0]['autoagent_price']){
					    $rnd_uname = $this->getAutoBidUname($goods_id);
					}
					if(($tmp_1st_price <= ($new_bid_price + $goods_everyprice)) || ($tmp_1st_price <= ($tmp_2nd_price + $goods_everyprice))){
					    $agent_biding_price = $tmp_1st_price;
					}else{
					    $agent_biding_price = $tmp_2nd_price + $goods_everyprice;
					}
				}
			}else{
				if($g_cost > $new_bid_price){
					$agent_biding_price = $new_bid_price + $goods_everyprice;
					$rnd_uname = $this->getAutoBidUname($goods_id);
				}
			}
			if($agent_biding_price){
				$agent_bidrecord_data = array(
					'bidrecord_uid' => $agent_info[0]['autoagent_uid'],
					'bidrecord_price' => $agent_biding_price,
					'bidrecord_goodsid' => $goods_id,
					'bidrecord_time' => time(),
					'bidrecord_goodsneedmoney' => getneedmoney($agent_biding_price),
					'bidrecord_type' => 1
				);
				if($rnd_uname){
				    $agent_bidrecord_data['bidrecord_uid'] = 0;
					$agent_bidrecord_data['bidrecord_uname'] = $rnd_uname;
				}
				if($bidrecord->data($agent_bidrecord_data)->add()){
					$goods_everyprice = $goods['goods_everypricestyle'] == 1 ? geteveryprice($agent_biding_price) : $goods['goods_everyprice'];
					$goods_data = array(
						'goods_needmoney' => $agent_bidrecord_data['bidrecord_goodsneedmoney'],
						'goods_everyprice' => $goods_everyprice,
						'goods_nowprice' => $agent_biding_price,
						'goods_bidtimes' => $goods['goods_bidtimes'] + 2
					);
					$mod_goods->where(array('goods_id' => $goods_id))->save($goods_data);
				}
			}

			$return = array('result' => true, 'data' => '出价成功', 'code' => 1);
			$this->ajaxReturn($return);
		}else{
		    $return = array('result' => false, 'data' => '出价失败', 'code' => 9);
			$this->ajaxReturn($return);
		}
	}

	//得到拍品的保底代理出价用户名
	public function getAutoBidUname($goods_id = 0){
	    if(!$goods_id){
		    return false;
		}
		$record_where = array(
		    'bidrecord_uid' => 0,
			'bidrecord_type' => 1,
			'bidrecord_goodsid' => $goods_id
		);
		$uname = M('PaimaiBidrecord')->where($record_where)->getField('bidrecord_uname');
		if(!$uname){
		    return 'YS_'.sprintf("%06d", rand(1,999999));
		}else{
		    return $uname;
		}
	}

	//扣除保证金
	private function deductDeposit($uid, $goods_id, $deposit, $style = 4){
		$deduct_data = array(
		    'recharge_sn' => D('Paimai/PaimaiGoods')->CreateRechargeSn(),
			'recharge_uid' => $uid,
			'recharge_money' => -$deposit,
			'recharge_createtime' => time(),
			'recharge_style' => $style,
			'recharge_returngid' => $goods_id
		);
		
		$mod_recharge = M('PaimaiRecharge');
		if($recharge_id = $mod_recharge->data($deduct_data)->add()){
			return $mod_recharge->where("recharge_id = $recharge_id")->setField('recharge_status', '2');
		}else{
		    return false;
		}
	}
	
	//根据分类id获取拍品筛选分类条件
	private function screenCateCondition($cat_id){
	    $cate_obj = M('PaimaiCategory');
		//是否为顶级分类
		$cat_pid = $cate_obj->where("cat_id = $cat_id")->getField('cat_pid');
		if($cat_pid == 0){
			//顶级分类 则查找其子分类
		    $sub_cat_list = $cate_obj->field('cat_id')->where("cat_pid = $cat_id")->select();
		}
		//子分类条件拼接字符串
		$sub_cat = '';
		foreach($sub_cat_list as $k => $v){
			$sub_cat .= $v['cat_id'] . ',';
		}
		$sub_cat = substr($sub_cat, 0, -1);
		if(empty($sub_cat)){
		    if($cat_id == 17){
				return 0;
			}else if(!empty($cat_id)){
			    return $cat_id;
			}
		}else{
		    return array('IN', $sub_cat);
		}
	}

	//验证必要参数
	private function verifyNeceParam($nece_params){
	    foreach($nece_params as $v){
		    if(!$v){
			    return false;
			}
		}
		return true;
	}

	//创建唯一商品订单号
    public function createOrderSn()
    {
        //创建唯一订单号
        $orderinfo = M('PaimaiOrderInfo');
        $info_sn = 'OI' . date("Ymd") . mt_rand(10000, 99999);
        return $orderinfo->where("info_sn = $info_sn")->count() ? $this->createOrderSn() : $info_sn;
    }
}