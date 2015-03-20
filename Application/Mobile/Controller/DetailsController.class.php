<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍品信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Mobile\Controller;

use Mobile\Controller\MobieController;

class DetailsController extends MobileController
{
    public function _initialize()
    {
        parent::_initialize();
		$this->title = "拍品详情";
        $this->keywords = "拍品详情";
        $this->desc = "拍品详情";
    }
    /**
     * 拍品信息
     */
    public function index()
    {
	    $goods_id = I('get.goods_id', 0, 'intval');
		
		if(!$goods_id){
		    $this->error('此信息不存在或已经删除！');
		}
		
		$goods_obj = M('PaimaiGoods');

		//更新点击量
		$goods_obj->where(array('goods_id' => $goods_id))->setInc("goods_hits");

		//商品信息
		$goods_field = 'goods_id, goods_name, goods_sn, goods_intro, goods_nowprice, goods_startprice, goods_needmoney, goods_everyprice, goods_starttime, goods_endtime, third_platform, goods_hits, recordid, goods_sellername, goods_place, goods_weight, goods_bidtimes';
        $goods_where = array(
			'goods_id'=>$goods_id,
			'goods_isshow'=>1,
			'goods_isdelete'=>0
		);
        $goods_info = $goods_obj->field($goods_field)->where($goods_where)->find();
		$goods_info['thumb'] = D('Content/Document')->getPic($goods_info['recordid'], 'thumb', $goods_info['goods_id']);
		if(!$goods_info){
            header("Location: http://www.yishu.com/search/?m=content&c=index&a=error_404&model=paimai"); 
            exit();
        }

        //收藏人数
		$goods_info['collect_num'] = D('MobileGoods')->getCollectedNum($goods_info['goods_id']);
		
		//是否收藏
		$gid = M('paimai_collect')->where(array('collect_goodsid'=>$goods_id,'collect_uid'=>$this->auth['mid']))->getField('collect_id');
		if($gid){
		    $this->assign('is_collect', true);
		}else{
		    $this->assign('is_collect', false);
		}

        //设置提醒人数
		$goods_info['set_remind'] = M('paimai_remind')->where(array('gid' => $goods_info['goods_id']))->count();

		//围观
		$goods_info['circusee'] = $goods_info['goods_bidtimes'] + $goods_info['goods_hits'];

		$this->assign('goods_info', $goods_info);

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

		$this->assign('goods_intro', $goods_intro);

		//用户评论
		$comment_list = M('PaimaiComment')->field('comment_id, comment_uid, comment_createtime, comment_uname, comment_content')->where(array('comment_goodsid' => $goods_info['goods_id'], 'comment_status' => 1, 'comment_isdelete' => 0))->order('comment_id desc')->select();
		$db_bsm = M('bsm.member','bsm_');
        foreach($comment_list as $k => $v){
		    $comment_list[$k]['username'] = getUsername($comment_list[$k]['comment_uid']);
		}
		$this->assign('comment_list', $comment_list);

		//评论数
        $comment_count = count($comment_list);

		$this->assign('comment_count', $comment_count);
		
        
		
		$this->display("Front:details");
    }
    
	/**
	 * 判断用户登录与否
	 */
	public function ajax_remind(){
        if(empty($this->auth['mid'])){
            $data['status'] = 0;
            $data['info'] = "请先登录再操作";
            exit(json_encode($data));
        }else{
            $this->ajaxReturn(array('status' => 1), 'JSON');
        }
	}
    
	/**
	 * 设置提醒
	 */
	public function unlogin_remind() {
    #接受数据
        //设置提醒手机号码
        $phone = I('post.mobile');
        if(empty($phone)) {
            $this->ajaxReturn(array('status' => 0), 'JSON');
        }
        $id=I('id',0,'intval');
    #判断是否设置过提醒
        #where 
        $remind_where = array(
            'phone' => $phone,
            'gid' => $id
            );
        $remind_record = M('paimai_remind')->where($remind_where)->select();
        if(!empty($remind_record)){
            $this->ajaxReturn(array('status'=>2, 'info'=>'您已经设置过提醒！'), 'JSON');
        }

        //商品开始时间
        $startime = I('starttime') - 1800;//提前半小时提醒
        //待定
        $style = I('style');
        //组织数据
        $data = array(
            'phone' => $phone,
            'gid' => $id,
            'uid' => $this->auth['mid'],
            'time' => time(),
            'starttime' => $startime
            );
        //入库
        if(M('paimai_remind')->add($data)){
            $this->ajaxReturn(array('status'=>1), 'JSON');
        } else {
            $this->ajaxReturn(array('status'=>0), 'JSON');
        }
    }

    /**ajax传递过来三个参数:
    1.商品id  id 
    2.用户最新的出价价位 newbidprice,
    3.用户本次的加价幅度 useraddprice
     * 用户点击出价按键
     * 业务逻辑:
     * 从前台传进来1.商品id,2,商品价格,3口号,
     * 1 先验证用户是否登录
     * 2 是否有保证金与当前商品的保证金比较
     * 3 从数据库中得到本商品的最新记录的uid与当前用户的session中的uid进行对比,
     */
    protected $errno=0;
    protected $error=array(
		1=>"立即登录，抢拍宝贝！",
		2=>"此商品不存在",
		3=>"此商品已经被拍走",
		4=>"你的账户余额低于本商品所要求的最低余额,账户余额不足,是否现在去充值?",
		5=>"你已经是出价最高的用户,请稍后再进行尝试",
		6=>"由于现价已经为...保证金已经为...你还需交纳...保证金",
		7=>"出现异常,请刷新页面重新尝试,提示代码:7",//第一次拍扣除表bsm_member保证金失败
		8=>"出现异常,请刷新页面重新尝试,提示代码:8",//第一次拍向表recharge中生成订单失败
		9=>"出价成功",
		10=>"拍品现在的价格已经大于你的出价,请重新出价",
		11=>"本拍品还没开拍,请稍后尝试",
		120=>"网页正在开发修复中请稍后尝试",
		110=>"出价失败,请重新尝试"
    );

    public function bid(){
        if (!IS_AJAX) $this->error("此页面不存在");

        //商品id
        $goods_id = I("get.id", 0, "intval");

        //此用户的出价
        $newbidprice = I("get.newbidprice", 0, "floatval");

        //获得用户的加价幅度
        //$useraddprice = I("get.useraddprice", 0, "floatval");
       
        //调试模式:如果在调试则把$tiaoshi改为1
        $tiaoshi=0;
        if($tiaoshi==1){
            $this->errno=120;
            $data['errno'] =$this->errno;
            $data['errorinfo']=$this->error[$this->errno];
            echo json_encode($data);
            exit;
        }

        //判断传递过来的参数  
        //如果下列情况有一个为0,则返回状态0
        if ($goods_id == 0 || $newbidprice == 0) {
            $this->errno = 110;
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data);
            exit;
        }

        $uid = $this->auth['mid'];
        $where["bidrecord_goodsid"] = $goods_id;

        //判断是否登录如果没有登录返回
        if (!$uid) {
            $this->errno = 1;
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"你还没有登录";
            exit;
        }

        //判断本商品是否存在
        //得到本商品的信息
        $goods_check_where['goods_isshow'] = 1;
        $goods_check_where['goods_id'] = $goods_id;
        $goods_check = M('PaimaiGoods')->field('goods_id,goods_nowprice,goods_needmoney,goods_endtime,goods_bidtimes,goods_everypricestyle,goods_everyprice,goods_starttime')->where($goods_check_where)->find();

        //判断本商品是否开拍
        if($goods_check['goods_starttime'] > time()){
            $this->errno = 11;
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"商品还未开拍";
            exit;
        }

        //对重新出价修改取出的保证金
        $goods_check['goods_needmoney'] = getneedmoney($newbidprice);

        //拍卖次数不为0且不同用户可能停留在同一页面时候进行竞拍
        if($goods_check['goods_bidtimes'] != 0 && $goods_check['goods_nowprice'] >= $newbidprice){
            $this->errno = 10;
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"商品已经有最新出价";
            exit;
        }

        //判断商品是否存在
        if (empty($goods_check)) {
            $this->errno = 2;
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"商品不存在";
            exit;
        }

        //判断商品是否过期或已经被拍走
        if($goods_check['goods_endtime'] < time()){
            $this->errno = 3;
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"商品已经过期或者已经被拍走";
            exit;
        }
    
        

        //得到用户账户金额
        //提现审核冻结
        //$moneys = M("paimai_deposit")->where(array('uid'=>$uid,'status'=>0))->sum('money');
        //$member_cash=M("member","bsm_","DB_BSM")->where(array('mid'=>$uid))->getField('amount');
        //得到用户可用金额+注册返现的
        //注册的返现金额
        $register_amount = return_amount($uid);
        $member_cash = $this->user_amount + $register_amount;

        $bidrecord = M("PaimaiBidrecord");

    #检查用户之前有没有拍过此商品
        $bidcheck_where['bidrecord_goodsid'] = $goods_id;
        $bidcheck_where['bidrecord_uid'] = $uid;
        $userbidcheck = $bidrecord->field("bidrecord_goodsneedmoney")->where($bidcheck_where)->order("bidrecord_id desc")->find();

        if($goods_check['goods_needmoney']>=return_amount($uid)){
            $goods_check['goods_needmoney']=$goods_check['goods_needmoney']-return_amount($uid);
        }
        //p($goods_check['goods_needmoney']);
        //如果用户没有拍过
        if (empty($userbidcheck)) {

            //查看用户的保证金是否大于本商品的保证金
            //如果保证金不足,
            if ($member_cash < $goods_check['goods_needmoney']) {
                $this->errno=4;
                $data['errno'] = $this->errno;
                //商品保证金
                $data['goods_needmoney'] = $goods_check['goods_needmoney'];
                //返回用户的账户余额
                $data['user_amount'] = $member_cash;
                $data['recharge_money']=$data['goods_needmoney']-$data['user_amount'];
                $data['errorinfo']="您的账户余额为".$data['user_amount']."元,小于拍本拍品的最小金额".$data['goods_needmoney']."元,你还需要充值".format_money($data['recharge_money'])."元,现在是否去充值?";
                echo json_encode($data); //"你的保证金不足,请及时充值";
                exit;
            } else {
            //如果保证金充足则扣除保证金
                //用户第一次参拍,扣除的保证金为除了注册优惠券之外的金额
                //$goods_check['goods_needmoney']=$goods_check['goods_needmoney']-return_amount($uid);

                if(!$this->downneedmoney($uid,$goods_id,$goods_check['goods_needmoney'],4)){
                    $data['errno']=$this->errno;
                    $data['errorinfo']=$this->error[$this->errno];
                    echo json_encode($data); //"";
                    exit;
                }
                //跳出,继续走

            }
        }else{
        //如果用户之前拍过此商品再次参拍

            //得到数据库中最新的一条记录的uid和session中的mid进行对比
            $NewbidrecordObj_where=array(
                'bidrecord_goodsid'=>$goods_id,
                );
            $NewbidrecordObj=$bidrecord->field('bidrecord_uid,bidrecord_price')->where($NewbidrecordObj_where)->order("bidrecord_id desc")->find();

    #判断现在的最高出价是不是此用户
            /*
            (注释掉的代码作用是禁止用户连续出价)
            //注释start
            if($uid==$NewbidrecordObj['bidrecord_uid']){
                $this->errno=5;
                $data['errno']=$this->errno;
                $data['errorinfo']=$this->error[$this->errno];
                echo json_encode($data); //"你已经是出价最高的用户,请稍后出价";
                exit;
            //注释end
            }*/
            //如果不是最高出价者则验证出价价格和数据库中的最高价格进行对比,判断在这个期间是否已经有人再次出价了
                //验证现在的商品保证金和用户之前的保证金做对比
                
    #如果保证金与之前拍缴纳的不同则,再次扣除需要的保证金
            $againrecharge=$goods_check['goods_needmoney']-$userbidcheck['bidrecord_goodsneedmoney'];

            if($againrecharge>0){//需要再次充值

    #再次判断账户余额,给出不同提示
                if($member_cash<$againrecharge){//如果用户账户余额不足
                    $this->errno=6;
                    $data['errno']=$this->errno;

                    //商品现价
                    $data['goods_nowprice']=$goods_check['goods_nowprice'];

                    //商品现在需要的保证金
                    $data['goods_needmoney']=$goods_check['goods_needmoney'];

                    //用户之前交纳的保证金
                    $data['bidrecord_goodsneedmoney']=$userbidcheck['bidrecord_goodsneedmoney'];

                    //用户还需要交纳的保证金
                    $data['needmoney']=$againrecharge;
                    $data['errorinfo']="由于本拍品现价已经为".$goods_check['goods_nowprice']."元,你之前交纳的本拍品的保证金为".$userbidcheck['bidrecord_goodsneedmoney']."元小于本拍品现在所需要的保证金".$goods_check['goods_needmoney']."元,你还需要交纳".format_money($data['needmoney'])."元保证金,是否现在去充值?";
                    echo json_encode($data); //"还需要交纳保证金";
                    exit;
                }else{//用户资金充足则扣除

                    if(!$this->downneedmoney($uid,$goods_id,$againrecharge,5)){
                        $data['errno']=$this->errno;
                        $data['errorinfo']=$this->error[$this->errno];
                        echo json_encode($data); //"";
                        exit;
                    }
                }
            }
            //走到这一步则保证金都正常跳出
            
        }


    #到这里说明条件都符合,进行入库操作

        //如果保证金扣除成功,组织数据入yishu_paimai_bidrecord库
        $Bidrecord_data=array(
            'bidrecord_uid'=>$uid,
            'bidrecord_price'=>$newbidprice,
            'bidrecord_goodsid'=>$goods_id,
            'bidrecord_ip'=>get_client_ip(),
            'bidrecord_time'=>time(),
            'bidrecord_goodsneedmoney'=>$goods_check['goods_needmoney'],
            );

        if ($bidrecord->data($Bidrecord_data)->add()) {
            //注册返现后把这个券的状态更改下,为已经使用
            change_status_false($uid);
    #如果成功则重新修改此时商品的保证金,现价,加价幅度


            //得到最新加价幅度然后入库
            if($goods_check['goods_everypricestyle']==1){
                $goods_everyprice=geteveryprice($newbidprice);
            }else{
                $goods_everyprice=$goods_check['goods_everyprice'];
            }
            $ChargeGoodsObj = D("Paimai/PaimaiGoods");

    #组织数据更改商品表
            //where条件
            $ChargeGoodsObj_where=array(
                'goods_id'=>$goods_id,
                );

            //data数组
            $ChargeGoodsObj_data=array(
                'goods_needmoney'=>$goods_check['goods_needmoney'],//保证金
                'goods_everyprice'=>$goods_everyprice,//加价幅度
                'goods_nowprice'=>$newbidprice,//重新更改现价
                'goods_bidtimes'=>$goods_check['goods_bidtimes']+1,//次数+1
                );

            //更改保证金和加价幅度
            $ChargeGoodsObj->where($ChargeGoodsObj_where)->save($ChargeGoodsObj_data);

            $this->errno=9;//成功
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"出价成功";
            exit;
        }else{
            $this->errno=110;
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"出价失败";
            exit;
        }
    }

	//收藏数
	public function cs(){
		if(IS_AJAX){
			$id = I('post.id');
			if(!$this->auth['mid']){
				$data['status'] = 2;
				$data['info'] = '请先登录再操作';
				$this->ajaxReturn($data,'JSON');
			}
			if($id){
				$gid = M('paimai_collect')->where(array('collect_goodsid'=>$id,'collect_uid'=>$this->auth['mid']))->getField('collect_id');
				if($gid){
					$data['status'] = 0;
					$data['info'] = '您已收藏过该拍品';
					$this->ajaxReturn($data,'JSON');
				}else{
					$datas = array(
						'collect_goodsid' => $id,
						'collect_uid' => $this->auth['mid'],
						'collect_time' => time()
					);
					if(M('paimai_collect')->add($datas)){
						$data['status'] = 1;
						$data['info'] = '收藏成功';
						$this->ajaxReturn($data,'JSON');
					}else{
						$data['status'] = 0;
						$data['info'] = '收藏失败';
						$this->ajaxReturn($data,'JSON');
					}
				}
			}else{
				$data['status'] = 0;
				$data['info'] = '收藏失败...';
				$this->ajaxReturn($data,'JSON');
			}
		}
	}

	/*
    扣除保证金生成订单
    */
    public function downneedmoney($uid,$goods_id,$needmoney,$style=4){
        //在保证金充值表中生成一条扣除保证金的记录
        $deduct_needmoney_data=array(
            'recharge_sn'=>D("Paimai/PaimaiGoods")->CreateRechargeSn(),
            'recharge_uid'=>$uid,
            'recharge_money'=>-$needmoney,
            'recharge_createtime'=>time(),
            'recharge_style'=>$style,//4为拍商品时候冻结的保证金,5为随着价格上涨补的保证金
            'recharge_ip'=>get_client_ip(),
            'recharge_returngid'=>$goods_id,
            );

        if ($recharge_id=M("PaimaiRecharge")->data($deduct_needmoney_data)->add()) {
            return M("PaimaiRecharge")->where("recharge_id=$recharge_id")->setField("recharge_status","2");
            /*$sql = "update bsm.bsm_member set amount=amount-$needmoney,frozen=frozen+$needmoney where mid=$uid";
            //如果扣除bsm_member表中的金额,加上冻结的金额
            if (!M()->db(5, 'DB_BSM')->execute($sql)) {
                $this->errno=7;
                return false;
            }else{
            //如果没有出错修改刚才保证金生成订单状态为2,支付成功就向下走
                return M("PaimaiRecharge")->where("recharge_id=$recharge_id")->setField("recharge_status","2");
            }  */
        } else {
            $this->errno=8;
            return false;
        }
    }

	#ajax
    /*
     * 传入商品goods_id
     * 动态得到商品信息:价格,出价次数
     * 商品详情页面动态变化商品价格和出价次数
     */
    public function getGoodsinfo()
    {
        if(!IS_AJAX)$this->error("你请求的页面不存在");
        $goods_id = I("get.id", 0, "intval");
        $field = array(
            'goods_nowprice',
            'goods_bidtimes',
            'goods_everyprice',
            'goods_needmoney',
        );

        $goods = M("PaimaiGoods")->field($field)->where("goods_id=" . $goods_id)->find();
        echo json_encode($goods);
    }

	/*
    记录用户点击状态,用户跟踪,不返回值,也不进行报错判断,即可ajax也可php函数调用
    */
    public function user_record($goods_id=0,$status=0,$uid=0){
        //商品id
        $goods_id=$goods_id?$goods_id:I('gid',0,'intval');
        //状态码
        $status=$status?$status:I('status',0,'intval');
        //用户
        $uid=$uid?$uid:I('uid',0,'intval');

        //如果有一个不为0则返回false
        if($goods_id==0||$status==0 || $uid==0) return false;

        // 商品id 和 状态码 和 用户uid 都不为0,则走下面
        $bidstatus_data=array(
            'bidstatus_status'=>$status,
            'bidstatus_gid'=>$goods_id,
            'bidstatus_uid'=>$uid,
            'bidstatus_time'=>time(),
            );
        v($bidstatus_data);
        M('PaimaiBidstatus')->add($bidstatus_data);
    }
}
