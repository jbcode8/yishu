<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-16
 * Time: 上午10:33
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminOrderController extends AdminController
{
    public function index(){

		//p($_GET);
        $orderinfo_where=array();
        //开始时间
        $starttime=I('starttime');
        $endtime=I('endtime');
        if(!empty($starttime)&&empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('GT',strtotime($starttime));
        }elseif(empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('LT',strtotime($endtime)+86400);
        }elseif(!empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('BETWEEN',array(strtotime($starttime),strtotime($endtime)+86400));
        }
        $this->starttime=$starttime;
        $this->endtime=$endtime;
        //付款状态
        $paystatus=I('paystatus');
        if($paystatus==1){//未付款
            $orderinfo_where['orderinfo_paystatus']=0;

        }elseif($paystatus==2){//已经付款
            $orderinfo_where['orderinfo_paystatus']=2;
        }
        $this->paystatus=$paystatus;
        //收货状态
        $status=I('status');
        if($status==1){//未确认
            $orderinfo_where['orderinfo_status']=5;
        }elseif($status==2){//已发货
            $orderinfo_where['orderinfo_status']=6;
        }elseif($status==3){//已收货,交易完成
            $orderinfo_where['orderinfo_status']=7;
        }
        $this->status=$status;


		//分页
		$p = I('p',1,'intval');
		//每页显示的数量
        $prePage = 5;
		//分页商品
		$this->orderinfo = M("PaimaiOrderInfo")->field($field)->where($orderinfo_where)->page($p . ',' . $prePage)->order('orderinfo_id desc')->select();
        
		//分页商品总数
		$total_num=M("PaimaiOrderInfo")->where($orderinfo_where)->count();
        //p($total_num);
		$Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        
        $this->assign('page', $show); // 赋值分页输出
        $this->display();

    }
    /*
    编辑订单
    */
    public function edit(){
        $orderinfo_id=I('orderinfo_id',0,'intval');
        if($orderinfo_id==0)$this->error('你请求的页面不存在');
        $this->orderinfo=M('PaimaiOrderInfo')->join("yishu_paimai_order on order_orderinfoid=orderinfo_id")->find($orderinfo_id);
        $this->display();
    }
    /*
    修改订单
    */
    public function update(){
        //p($_POST);
        $orderinfo_id=I('orderinfo_id',0,'intval');
        $OrderInfo=D('PaimaiOrderInfo');
        $orderinfo_status=$OrderInfo->where()->getField('orderinfo_status');
        //防止错误
        if($orderinfo_status==6 || $orderinfo_status==7){
            $this->error("本商品已经发货,不能进行修改");
        }
        if($OrderInfo->create()){
            $OrderInfo->orderinfo_updatetime=time();
            if($OrderInfo->where('orderinfo_id='.$orderinfo_id)->save()){
                $this->success("订单修改成功");
            }
        }else{
            $this->error($OrderInfo->getError());
        }
    }
    /*后台查看自动生成的订单*/
    public function autoorder(){
        $this->display();
    }

	//订单导出 by Usagi 2015-2-12
	public function download_order(){
	    $order_field = array(
		    'o.order_orderinfoid',// => 'ID',
			'o.order_uid',//=> '用户ID',
			'o.order_goodsname',// => '商品名称',
			'o.order_goodssn',// => '商品货号',
			'from_unixtime(o.order_createtime,"%Y-%m-%d %H:%i:%s")',// => '订单时间',
			'o.order_goodsnowprice',// => '拍下价格',
			'oi.orderinfo_kdname',// => '快递公司',
			'oi.orderinfo_kd',// => '快递单号',
			'oi.orderinfo_provincename',// => '省名',
			'oi.orderinfo_cityname',// => '城市名',
			'oi.orderinfo_address',// => '详细地址',
			'oi.orderinfo_note',// => '备注'
		);
		$o_status = I('o_status', 0, 'intval');
		switch($o_status){
		    case 0: 
				$order_field = array(
				    'order_id',
					'order_uid',
					'order_goodsname',
					'order_goodssn',
					'from_unixtime(order_createtime,"%Y-%m-%d %H:%i:%s")',
					'order_goodsnowprice'
			    );
				//根据用户名和手机号搜索
				$auto_goodssn = I('auto_goodssn', '');//商品货号
				$auto_mobile = I('auto_mobile', '');//手机
				$auto_username = I('auto_username', '');//用户名
				$order_where = array(
					'order_orderinfoid' => 0,
				);
				if(!empty($auto_goodssn)){
					$order_where = array_merge($order_where, array('order_goodssn' => $auto_goodssn));
				}
				if(!empty($auto_mobile)){
					//根据用户mobile查询出用户uid
					$auto_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('mobile' => $auto_mobile))->getField('mid');
					$order_where = array_merge($order_where, array('order_uid' => $auto_order_uid));
				} 
				if(!empty($auto_username)){
					$auto_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('username' => $auto_username))->getField('mid');
					$order_where = array_merge($order_where, array('order_uid' => $auto_order_uid));
				}
				$order_info = M('PaimaiOrder')->field($order_field)->where($order_where)->order("order_id desc")->select();
				$title = array('ID','用户ID','商品名称','商品编号','下单时间','拍下价格','用户名','手机号');
				break;
			case 1:
				//根据用户名和手机号搜索
				$nopay_goodssn = I('nopay_goodssn', '');//商品货号
				$nopay_orderinfosn = I('nopay_orderinfosn', '');//订单号
				$nopay_mobile = I('nopay_mobile','');//手机
				$nopay_username = I('nopay_username');//用户名
				$order_where = "orderinfo_paystatus<>2";
				if(!empty($nopay_goodssn)){
					$order_where = $order_where." and order_goodssn="."'".$nopay_goodssn."'";
				}
				if(!empty($nopay_orderinfosn)){
					$order_where = $order_where." and orderinfo_sn="."'".$nopay_orderinfosn."'";
				}
				if(!empty($nopay_mobile)){
					//根据用户mobile查询出用户uid
					$nopay_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('mobile' => $nopay_mobile))->getField('mid');
					//echo $nopay_order_uid;die;
					$order_where = $order_where." and orderinfo_uid=".$nopay_order_uid;
				} 
				if(!empty($nopay_username)){
					$nopay_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('username' => $nopay_username))->getField('mid');
					$order_where = $order_where." and orderinfo_uid=".$nopay_order_uid;
				}
				$order_info = M('PaimaiOrder')->alias('o')->join('LEFT JOIN yishu_paimai_order_info oi on o.order_orderinfoid = oi.orderinfo_id')->field($order_field)->where($order_where)->order('o.order_orderinfoid desc')->select();
				$title = array('ID','用户ID','商品名称','商品编号','下单时间','拍下价格','快递公司','快递单号','省','市','详细地址','备注','用户名','手机号');
				break;
			case 2:
				$starttime = strtotime(I('starttime'));
				$endtime = strtotime(I('endtime'));
				if(!empty($endtime)){
					$endtime += 86400;	
				}
				$order_where = array(
					'order_status' => 2
				);
				//有开始时间，没有结束时间
				if(!empty($starttime) && empty($endtime)){
					$order_where['o.order_createtime'] = array('GT',$starttime);
				//没有开始时间，只有结束时间	    
				}elseif(empty($starttime) && !empty($endtime)){
					$order_where['o.order_createtime'] = array('LT',$endtime);
				//有开始时间和结束时间		
				}elseif(!empty($starttime) && !empty($endtime)){
					$order_where['o.order_createtime'] = array('between',array($starttime,$endtime));
				}
				$mobile = I('mobile');
				if(!empty($mobile)){
					$order_where['oi.orderinfo_mobile'] = array('IN',$mobile);
				}
				$order_info = M('PaimaiOrder')->alias('o')->join('LEFT JOIN yishu_paimai_order_info oi on o.order_orderinfoid = oi.orderinfo_id')->field($order_field)->where($order_where)->order('o.order_orderinfoid desc')->select();
				$title = array('ID','用户ID','商品名称','商品编号','下单时间','拍下价格','快递公司','快递单号','省','市','详细地址','备注','用户名','手机号');
				break;
			case 3:
				//根据用户名和手机号搜索
				$return_goodssn = I('return_goodssn', '');//商品货号
				$return_orderinfosn = I('return_orderinfosn', '');//商品订单号
				$return_mobile = I('return_mobile','');//手机
				$return_username = I('return_username');//用户名
				$order_where = "orderinfo_status in (4,8)";

				if(!empty($return_mobile)){
					//根据用户mobile查询出用户uid
					$return_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('mobile' => $return_mobile))->getField('mid');
					//echo $nopay_order_uid;die;
					$order_where = $order_where." and orderinfo_uid=".$return_order_uid;
				} 
				if(!empty($return_username)){
					$return_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('username' => $return_username))->getField('mid');
					$order_where = $order_where." and orderinfo_uid=".$return_order_uid;
				}
				if(!empty($return_goodssn)){
					$order_where = $order_where." and order_goodssn="."'".$return_goodssn."'";
				}
				if(!empty($return_orderinfosn)){
					$order_where = $order_where." and orderinfo_sn="."'".$return_orderinfosn."'";
				}
				$order_info = M('PaimaiOrder')->alias('o')->join('LEFT JOIN yishu_paimai_order_info oi on o.order_orderinfoid = oi.orderinfo_id')->field($order_field)->where($order_where)->order('o.order_orderinfoid desc')->select();
				$title = array('ID','用户ID','商品名称','商品编号','下单时间','拍下价格','快递公司','快递单号','省','市','详细地址','备注','用户名','手机号');
				break;
			default:;
		}
		
		foreach($order_info as $k => $v){
			$order_info[$k]['uname'] = getUsername($order_info[$k]['order_uid'], 0);
			$order_info[$k]['mobile'] = getUsername($order_info[$k]['order_uid'], 0, 'mobile');
		    foreach($order_info[$k] as $key => $value){
			    $order_info[$k][$key] = trim($value);
			}
		}
		#下载
	    //第一个数组为二维数组数据,第二个数组$field为一维数组title
        $arr = array(
            'data' => $order_info,
            'title' => $title
        );
        Vendor('Csv.Csv');
        $CSV = new \Csv;
        $CSV->export_csv($arr);
	}

    /*
    jquery-easyui中请求数据页面
    */
    public function autoorderdata(){

        $p=I('page',1,'intval');
        $rows=I('rows',10,'intval');
        $tag=I('tag');
        //p($_POST);
		$order_field=array(
			"*",
		);
        if($tag=='autoorder'){//自动生成的订单

			//根据用户名和手机号搜索
			$auto_goodssn = I('auto_goodssn', '');//商品货号
			$auto_mobile = I('auto_mobile', '');//手机
			$auto_username = I('auto_username', '');//用户名
			$auto_where = array(
				'order_orderinfoid' => 0,
				);
			if(!empty($auto_goodssn)){
				$auto_where = array_merge($auto_where, array('order_goodssn' => $auto_goodssn));
			}
			if(!empty($auto_mobile)){
				//根据用户mobile查询出用户uid
				$auto_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('mobile' => $auto_mobile))->getField('mid');
				$auto_where = array_merge($auto_where, array('order_uid' => $auto_order_uid));
			} 
			if(!empty($auto_username)){
				$auto_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('username' => $auto_username))->getField('mid');
				$auto_where = array_merge($auto_where, array('order_uid' => $auto_order_uid));
			}
			$order_arr=M('PaimaiOrder')->where($auto_where)->order("order_id desc")->page($p . ',' . $rows)->select();
            //$order_arr=M('PaimaiOrder')->where("order_orderinfoid=0")->order("order_id desc")->page($p . ',' . $rows)->select();
            //分页商品总数
            $total_num=M("PaimaiOrder")->where($auto_where)->count();
        }elseif($tag=="no_pay_order"){//提交过订单但是没有付款的
			
			//根据用户名和手机号搜索
			$nopay_goodssn = I('nopay_goodssn', '');//商品货号
			$nopay_orderinfosn = I('nopay_orderinfosn', '');//订单号
			$nopay_mobile = I('nopay_mobile','');//手机
			$nopay_username = I('nopay_username');//用户名
			$nopay_where = "orderinfo_paystatus<>2";
			if(!empty($nopay_goodssn)){
				$nopay_where = $nopay_where." and order_goodssn="."'".$nopay_goodssn."'";
			}
			if(!empty($nopay_orderinfosn)){
				$nopay_where = $nopay_where." and orderinfo_sn="."'".$nopay_orderinfosn."'";
			}
			if(!empty($nopay_mobile)){
				//根据用户mobile查询出用户uid
				$nopay_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('mobile' => $nopay_mobile))->getField('mid');
				//echo $nopay_order_uid;die;
				$nopay_where = $nopay_where." and orderinfo_uid=".$nopay_order_uid;
			} 
			if(!empty($nopay_username)){
				$nopay_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('username' => $nopay_username))->getField('mid');
				$nopay_where = $nopay_where." and orderinfo_uid=".$nopay_order_uid;
			}
            $order_arr=M('PaimaiOrder')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where($nopay_where)->order("orderinfo_id desc")->page($p . ',' . $rows)->select();
	
            //分页商品总数
            $total_num=M('PaimaiOrder')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where($nopay_where)->count();
        }elseif($tag=="finish_order"){//已经付过款的订单
            //时间
            $starttime=strtotime(I('starttime'));
            $endtime=strtotime(I('endtime'));
            if(!empty($endtime)){
                $endtime+=86400;    
            }
            if(!empty($starttime)&&empty($endtime)){//有开始时间，没有结束时间
                $order_where['order_createtime']=array('GT',$starttime);
                 
            }elseif(empty($starttime)&&!empty($endtime)){//没有开始时间，只有结时间 
                $order_where['order_createtime']=array('LT',$endtime);
                            
            }elseif(!empty($starttime)&&!empty($endtime)){//有开始时间和结束时间
                $order_where['order_createtime']=array('between',array($starttime,$endtime));
            }
            //订单ID筛选
            $orderinfo_id=I('orderinfo_id');
            if(!empty($orderinfo_id)){
                $order_where['orderinfo_id']=array('IN',$orderinfo_id);
            }
            //收货人手机号
            $orderinfo_mobile=I('orderinfo_mobile');
            if(!empty($orderinfo_mobile)){
                $order_where['orderinfo_mobile']=$orderinfo_mobile;
            }
			//收货人用户名 (chen)
			$orderinfo_username=I('orderinfo_username');
			if(!empty($orderinfo_username)){
				//根据用户名获取用户的手机号码
				$mobile = M('Member', 'bsm_', 'DB_BSM')->where(array('username' => $orderinfo_username))->getField('mobile');

                $order_where['orderinfo_mobile']=$mobile;
            }
			//商品货号
			$orderinfo__goodssn = I('orderinfo__goodssn', '');
			if(!empty($orderinfo__goodssn)){
                $order_where['order_goodssn']=$orderinfo__goodssn;
            }
			//商品订单号
			$orderinfo_orderinfosn = I('orderinfo_orderinfosn', '');
			if(!empty($orderinfo_orderinfosn)){
                $order_where['orderinfo_sn']=$orderinfo_orderinfosn;
            }
            $finish_order_where=$order_where;
            $finish_order_where['orderinfo_paystatus']=2;
            //p($finish_order_where);
            $order_arr=M('PaimaiOrder')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where($finish_order_where)->order("order_createtime desc")->page($p . ',' . $rows)->select();
            //分页商品总数
            $total_num=M('PaimaiOrder')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where($finish_order_where)->count();
        }elseif($tag=="returnorder"){//申请退换货的订单

			//根据用户名和手机号搜索
			$return_goodssn = I('return_goodssn', '');//商品货号
			$return_orderinfosn = I('return_orderinfosn', '');//商品订单号
			$return_mobile = I('return_mobile','');//手机
			$return_username = I('return_username');//用户名
			$return_where = "orderinfo_status in (4,8)";

			if(!empty($return_mobile)){
				//根据用户mobile查询出用户uid
				$return_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('mobile' => $return_mobile))->getField('mid');
				//echo $nopay_order_uid;die;
				$return_where = $return_where." and orderinfo_uid=".$return_order_uid;
			} 
			if(!empty($return_username)){
				$return_order_uid = M('Member', 'bsm_', 'DB_BSM')->where(array('username' => $return_username))->getField('mid');
				$return_where = $return_where." and orderinfo_uid=".$return_order_uid;
			}
			if(!empty($return_goodssn)){
				$return_where = $return_where." and order_goodssn="."'".$return_goodssn."'";
			}
			if(!empty($return_orderinfosn)){
				$return_where = $return_where." and orderinfo_sn="."'".$return_orderinfosn."'";
			}


			$order_arr=M('PaimaiOrder')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where($return_where)->order("orderinfo_returntime desc")->page($p . ',' . $rows)->select();
            //分页商品总数
            $total_num=M('PaimaiOrder')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where($return_where)->count();
		}
        //对数组进行处理
	foreach ($order_arr as $k => &$v) {
	    //处理图片	
        $v['thumb'] = D('Content/Document')->getPic($v['order_goodsrecordid'], 'thumb');
		//订单生成时间
		$v['order_createtime']=date('Y-m-d H:i:s',$v['order_createtime']);
		//申请时间
		if($v['orderinfo_returntime']!=0){
		    $v['orderinfo_returntime']=date('Y-m-d H:i:s',$v['orderinfo_returntime']);
		 }
		//用户名
		$v['uname']=getUsername($v['order_uid'],0);//获得用户名
		//手机
		$v['uphone']=getUsername($v['order_uid'],0,'mobile');//获得用户手机
                /*$order_arr[$k]['thumb'] = D('Content/Document')->getPic($v['order_goodsrecordid'], 'thumb');
                $order_arr[$k]['order_createtime']=date('Y-m-d H:i:s',$v['order_createtime']);
                $order_arr[$k]['orderinfo_returntime']=date('Y-m-d H:i:s',$v['orderinfo_returntime']);
                $order_arr[$k]['uname']=getUsername($v['order_uid'],0);//获得用户名
                $order_arr[$k]['uphone']=getUsername($v['order_uid'],0,'mobile');//获得用户手机*/
        }
        //组织数据
        $data=array(
            'total'=>$total_num,
            'rows'=>$order_arr
        );
        $this->ajaxReturn($data,"JSON");
    }
        /*后台查看自动生成的订单*/
    public function autoorderv2(){
        $p = I('p',1,'intval');
        //每页显示的数量
        $prePage = 8;
        $order_arr=M('PaimaiOrder')->where("order_orderinfoid=0")->order("order_id desc")->page($p . ',' . $prePage)->select();
        foreach ($order_arr as $k => $v) {
            $order_arr[$k]['thumb'] = D('Content/Document')->getPic($v['order_goodsrecordid'], 'thumb');
         }
        $this->order_arr=$order_arr;
        //分页商品总数
        $total_num=M("PaimaiOrder")->where("order_orderinfoid=0")->count();
        $Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        
        $this->assign('page', $show); // 赋值分页输出
        $this->display('autoorderv2');
    }
    /*自动生成订单页面，鼠标放上显示用户信息*/
    public function ajax_userorderinfo(){
        $uid=I('uid');
        if(empty($uid)){
            $data['status']=1;
            $data['info']='系统繁忙加载失败：提示代码：1';
            exit(json_encode($data));
        }
        $data['info']=getUsername($uid,0,'mobile');
        if(empty($data['info'])){
            $data['status']=0;
            $data['info']='系统繁忙加载失败：提示代码：0';
            exit(json_encode($data));
        }
        $data['status']=2;
        exit(json_encode($data));
    }
    /*AJAX发货*/
    public function ajax_chargesendstatus()
    {
        if(!IS_AJAX){
            $this->error("你请求的页面不存在");
        }
        //订单ID
        $orderinfo_id = I("get.id", 0, "intval");
        //快递单号
        $kd_sn=I('kd_sn');
        //快递公司名称
        $kd_name=I('kd_name');

        //数据
        $sendgoods_data=array(
            'orderinfo_sendtime'=>time(),//发货时间
            'orderinfo_status'=>6,//状态
            'orderinfo_kdname'=>$kd_name,//快递名字
            'orderinfo_kd'=>$kd_sn,//快递单号
        );
        //p($sendgoods_data);
        if (M("PaimaiOrderInfo")->where("orderinfo_id=" . $orderinfo_id)->setField($sendgoods_data)) {
            exit("1");
        } else {
            exit("0"); //发货失败
        }
    }
	//提现 
	public function manage_tx(){

		//分页
		$p = I('p',1,'intval');//每页显示的数量
        $prePage = 15;


		//搜索
		$s_phone = I('post.s_phone') ? I('post.s_phone') : '';//搜索手机号码
		$s_username = I('post.s_username') ? I('post.s_username') : '';//搜索用户名
		if(!empty($s_phone) || !empty($s_username)){
			if(!empty($s_phone)){//手机号查询
				//获取用户id
				$uid = M('member', 'bsm_', 'DB_BSM')->where(array('mobile' => $s_phone))->getField('mid');
				$where = array('uid' => $uid);
			} else {//用户名查询
				$uid = M('member', 'bsm_', 'DB_BSM')->where(array('username' => $s_username))->getField('mid');
				$where = array('uid' => $uid);
			}
			//查询该用户的提现信息
			$txs = M('paimai_deposit')->where($where)->select();
		} else {
			$txs=M('paimai_deposit')->order("id desc")->page($p . ',' . $prePage)->select();
		}


        //分页商品总数
        $total_num=M("paimai_deposit")->count();
        $Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        
        $this->assign('page', $show); // 赋值分页输出
		//$txs = M('paimai_deposit')->order('id DESC')->select();
        foreach($txs as $k=>$v) {
            $temp = $this->getMemberInfo($v['uid']);//获取用户信息
            $txs[$k]['realname'] = $temp['0']['realname'];
            $txs[$k]['mobile'] = $temp['0']['mobile'];
            $txs[$k]['all_amount'] = $this->getUserAmountByUid($v['uid']);//用户充值金额
            $txs[$k]['tx_money'] = $this->tx_money($v['uid']);//已经成功提现的金额

            $frozen = $this->getFrozenmoneyByUid($v['uid']);//参拍扣除总金额
            $txs[$k]['cuted_money'] = $this->cuted_money($v['uid']);//没按时付款扣除金额
            $return = $this->getReturnMoneyByUid($v['uid']);//没拍到返回金额
            //冻结的保证金
            $frozen_money = $frozen + $return - $txs[$k]['cuted_money'];//冻结的保证金=参拍扣除总金额-没拍到返回金额-没按时付款扣除金额
            //可提现金额
            $txs[$k]['ky_money'] = $txs[$k]['all_amount'] + $txs[$k]['cuted_money'] + $frozen_money + $txs[$k]['tx_money'];
            //可提现金额（剩余可用金额）=充值金额-没按时付款扣除金额-冻结的保证金-已经成功提现的金额
        }
		$this->txs = $txs;
		$this->display();
	}
    //提现用户资金明细
    public function manage_tx_detail() {
        //接收信息
        $uid = I('id');
        //查询条件
        $where = array(
            'recharge_uid' => $uid
            );
        //查询字段
        $field = 'recharge_sn, recharge_money, recharge_style, recharge_status, recharge_createtime';
        $this->list = M('paimai_recharge')->field($field)->where($where)->select();

        $this->display();
    }
    //获得用户真实姓名和手机号码
    public function getMemberInfo($uid = ''){
        $aa = M('member', 'bsm_', 'DB_BSM')->field('realname, mobile')->where(array('mid'=>$uid))->select();
        //print_r($aa);
        return $aa;
    }
	//
	public function ty(){
		$deposit = M('paimai_deposit')->where(array('id'=>I('get.id',0,intval)))->find();
		if(M('paimai_deposit')->where(array('id'=>I('get.id',0,intval)))->setField('status',1)){
			$data=array('recharge_uid'=>I('get.uid'),'recharge_money'=>-($deposit['money']),'recharge_createtime'=>time(),'recharge_style'=>7,'recharge_status'=>2,'recharge_sn'=>$this->createordersn());
			M('paimai_recharge')->add($data);
			M()->db(2,'DB_BSM')->table('bsm_member')->where('mid='.I('get.uid'))->setField('amount',array('exp','amount-'.$deposit['money']));
			$this->success('提现成功');
		}else{
			$this->error('提现失败，请重新操作');
		}
	}
	public function by(){
		if(M('paimai_deposit')->where(array('id'=>I('get.id',0,intval)))->setField('status',2)){
			$this->success('提现成功');
		}else{
			$this->error('提现失败，请重新操作');
		}
	}

	/*
     * 创建唯一充值订单号
     */
    public function createordersn()
    {
	#思想:先拼接一个订单号,然后去数据库中查找,如果在数据库中有,则递归
        //创建唯一订单号
		$recharge_obj=M("paimai_recharge");
        $info_sn = 'CZ' . date("Ymd") . mt_rand(10000, 99999);

        return $recharge_obj->where("info_sn=$info_sn")->count() ? $this->createordersn() : $info_sn;
    }

	//资金流明细
	public function recharge_detail(){
		$p = I('p',1,'intval');
		$prePage = 50;
        $recharge_detail=M('paimai_recharge')->order("recharge_id desc")->page($p . ',' . $prePage)->select();
		foreach($recharge_detail as &$val){
			$val['username'] = getUsername($val['recharge_uid'],0);
			$val['recharge_createtime'] = date('Y-m-d H:i:s',$val['recharge_createtime']);
			$val['recharge_style'] = get_recharge_style($val['recharge_style']);
			$val['recharge_paytime'] = date('Y-m-d H:i:s',$val['recharge_paytime']);
			$val['recharge_status'] = ($val['recharge_status'] == 2)?'成功':'失败';
		}
		$this->recharge_detail=$recharge_detail;
        //分页商品总数
        $total_num=M("paimai_recharge")->count();
        $Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        
        $this->assign('page', $show); // 赋值分页输出
		for($i=0;$i<=7;$i++){
			$sum = 0;
			$sum_r = M('paimai_recharge')->where(array('recharge_style'=>$i))->sum('recharge_money');
			if(!empty($sum_r)){
				$sum = $sum_r;
			}
			$this->assign('sum'.$i, '￥'.$sum); 
		}
		$this->display();
	}

	//用户资金流
	public function recharge_user(){
		$p = I('p',1,'intval');
		$prePage = 50;
        //查询条件
        $where = array(
            'recharge_uid' => array('GT', 0)
            );
        $recharge_user=M('paimai_recharge')->field('recharge_uid')->where($where)->page($p . ',' . $prePage)->group("recharge_uid")->order("recharge_uid desc")->select();

		foreach($recharge_user as &$val){
			$val['username'] = getUsername($val['recharge_uid'],0);
			$val['money1'] = $this->getUserAmountByUid($val['recharge_uid']);//充值总金额
			$val['money2'] = $this->getFrozenmoneyByUid($val['recharge_uid']);//参拍冻结金额
			$val['money3'] = $this->cuted_money($val['recharge_uid']);//没按时付款扣除金额
			$val['money4'] = $this->getReturnMoneyByUid($val['recharge_uid']);//没拍到返回金额
			$val['money5'] = $this->tx_money($val['recharge_uid']);//提现金额
			if(empty($val['money1'])){
				$val['money1'] = 0;
			}
			if(!empty($val['money2'])){
                //if(empty($val['money1']){
                    $val['money2'] = $val['money2']-$val['money3']+$val['money4'];//冻结的保证金=参拍扣除总金额-没拍到返回金额-没按时付款扣除金额
               // } else {
                    //$val['money2'] = 0-($val['money2']-$val['money3']+$val['money4']);//冻结的保证金=参拍扣除总金额-没拍到返回金额-没按时付款扣除金额
                //}
				
			} else {
                $val['money2'] = 0;
            }
			if(empty($val['money3'])){
				$val['money3'] = 0;
			}
			if(empty($val['money4'])){
				$val['money4'] = 0;
			}
			if(empty($val['money5'])){
				$val['money5'] = 0;
			}
			//$val['money6'] = $val['money1']+$val['money2']+$val['money3']+$val['money4']+$val['money5'];
            $val['money6'] = $val['money1'] + $val['money2'] + $val['money5'] + $val['money3'];
		}
		$this->recharge_user=$recharge_user;
        //分页商品总数
        $total_num=M("paimai_recharge")->field('recharge_uid')->where($where)->group('recharge_uid')->select();
        $total_num = count($total_num);

        $Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        
        $this->assign('page', $show); // 赋值分页输出
		$this->display();
	}


	/*
        用户总金额
    */
    private function getUserAmountByUid($uid){
    
        $useramount_where=array(
            'recharge_uid'=>$uid,
            'recharge_status'=>2,//状态2为充值成功的
            // 'recharge_trade_no'=>array('NEQ',''),
            // 'recharge_buyeremail'=>array('NEQ',''),
            'recharge_style'=>array('IN','1, 6'),//1为支付宝充值
        );
        return M("paimai_recharge")->where($useramount_where)->sum("recharge_money");
    }
    /*
        得到冻结
    */
    private function getFrozenmoneyByUid($uid){
        $frozen_where=array(
            'recharge_uid'=>$uid,
            'recharge_returngid'=>array('NEQ','0'),
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>array('IN','4,5'),//4为拍商品时扣除的保证金,5为补扣的 7提现
        );
        return M("paimai_recharge")->where($frozen_where)->sum("recharge_money");
    }
    /*
        这个是没有按时付款永远扣除的资金
    */
    private function cuted_money($uid){
        $frozen_where=array(
            'recharge_uid'=>$uid,
            'recharge_returngid'=>array('NEQ','0'),
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>3,//3为商品到期没有付款扣除的保证金
        );
        return M("paimai_recharge")->where($frozen_where)->sum("recharge_money");
    }

	/*
        提现
    */
    private function tx_money($uid){
        $tx_where=array(
            'recharge_uid'=>$uid,
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>7,//3为商品到期没有付款扣除的保证金
        );
        return M("paimai_recharge")->where($tx_where)->sum("recharge_money");
    }
    /*
        这个是没有按时付款永远扣除的资金
    */
    private function getReturnMoneyByUid($uid){
        $return_where=array(
            'recharge_uid'=>$uid,
            'recharge_returngid'=>array('NEQ','0'),
            'recharge_status'=>2,//状态2为操作成功的
            'recharge_style'=>2,//2为拍商品没拍到返回的保证金
        );
        return M("paimai_recharge")->where($return_where)->sum("recharge_money");
    }
    /*
        退货审核
    */
    public function refund_goods(){
        if(!IS_AJAX) {
            echo '提交方式不正确';
            return false;
        }
        //接收提交的订单id
        $id = I('post.id');
       //入库条件
        $where = array(
            'orderinfo_id' => $id, //订单id
            'orderinfo_paystatus' => 2,//支付状态为付款
            );
        //入库操作
        $orderstatus = M("paimai_order_info","yishu_","yishu")->where($where)->setField('orderinfo_status', 4);
        //修改字段失败!$orderstatus
        if(!$orderstatus){
            $this->ajaxReturn(array('status'=>0),'JSON');
        }
        $this->ajaxReturn(array('status'=>1),'JSON');
    }
    /*
        展示给财务
    */
    public function finance(){
        //p($_GET);
        $orderinfo_where=array();
        //开始时间
        $starttime=I('starttime');
        $endtime=I('endtime');
        if(!empty($starttime)&&empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('GT',strtotime($starttime));
        }elseif(empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('LT',strtotime($endtime)+86400);
        }elseif(!empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('BETWEEN',array(strtotime($starttime),strtotime($endtime)+86400));
        }
        $this->starttime=$starttime;
        $this->endtime=$endtime;
        //付款状态
        $paystatus=I('paystatus');
        if($paystatus==1){//未付款
            $orderinfo_where['orderinfo_paystatus']=0;
        }elseif($paystatus==2){//已经付款
            $orderinfo_where['orderinfo_paystatus']=2;
        }

        $this->paystatus=$paystatus;
        
        //分类
        //商品分类筛选
        $cat_where=array();
        $cat_id=I('cat',0,'intval');
        //商品分类列表
        $this->category = M('PaimaiCategory')->field("cat_id,cat_name")->where("cat_isshow=1 and cat_pid<>0")->order("cat_id asc")->select();
        $cat_str="";
        foreach($this->category as $k=>&$v){
            if($cat_id==$v['cat_id']){
                $cat_str.="<option selected='selected' value=".$v['cat_id'].">".$v['cat_name']."</option>";
            }else{
                $cat_str.="<option value=".$v['cat_id'].">".$v['cat_name']."</option>";
            }
        }
        $this->cat_str=$cat_str;

        //v($orderinfo_where);
        //分页
        $p = I('p',1,'intval');
        //每页显示的数量
        $prePage = 5; 
        //分页商品
        //$this->orderinfo = M("PaimaiOrderInfo")->field($field)->where($orderinfo_where)->page($p . ',' . $prePage)->order('orderinfo_id desc')->select();
        $orderinfo_field=array(
            "yishu_paimai_order_info.*",
            "goods_catid",
            "goods_cost"
            );
        if(!empty($cat_id)){
            $orderinfo_where['goods_catid']=$cat_id;
        }
        $this->orderinfo = M("PaimaiOrderInfo")
        ->join("yishu_paimai_order on orderinfo_id=order_orderinfoid")
        ->join("yishu_paimai_goods on order_goodsid=goods_id")
        ->field($field)->where($orderinfo_where)->page($p . ',' . $prePage)->order('orderinfo_id desc')->select();
        

         //exit(M("PaimaiOrderInfo")->getLastSql());
        //分页商品总数
        $total_num=M("PaimaiOrderInfo")
        ->join("yishu_paimai_order on orderinfo_id=order_orderinfoid")
        ->join("yishu_paimai_goods on order_goodsid=goods_id")->where($orderinfo_where)->count();
        $Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出

        $suffix=$_SERVER['QUERY_STRING'];
        //去除开头两个字符串,并替换&&为?
        $suffix=str_replace("&&","?&",substr($suffix,3));
        //去除分页 p=3中的数字
        $suffix=preg_replace("/&p=(\d+)*/","",$suffix);
        $show=preg_replace("/(.*)Paimai\/AdminOrder\/finance(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);

        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }
    /*财务下载*/
    public function caiwudown(){
        
        $cat_id=I('cat');
        
        $action=I('action');
        //开始时间
        $starttime=I('starttime');
        $endtime=I('endtime');
        if(!empty($starttime)&&empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('GT',strtotime($starttime));
        }elseif(empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('LT',strtotime($endtime));
        }elseif(!empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('BETWEEN',array(strtotime($starttime),strtotime($endtime)));
        }
        if(!empty($cat_id)){
            $orderinfo_where['goods_catid']=$cat_id;
        }
        //p($_GET['cat']);
        
        $this->starttime=$starttime;
        $this->endtime=$endtime;
        //付款状态
        $paystatus=I('paystatus');

        if($paystatus==1){//未付款
            $orderinfo_where['orderinfo_paystatus']=0;
        }elseif($paystatus==2){//已经付款
            $orderinfo_where['orderinfo_paystatus']=2;
        }
        $field=array(
           /* "case
                when goods_isshow=0 then '不显示'
                else '显示' end"
            => "goods_isshow",*/
            'orderinfo_id'=>"订单ID",
            'orderinfo_sn'=>"订单编号",
            'orderinfo_amount'=>"订单总金额",
             "case
                when orderinfo_paystatus=0 then '未付款'
                when orderinfo_paystatus=2 then '已经付款' end
                "=>'付款状态',
            "if(orderinfo_paytime<=0,0,from_unixtime(orderinfo_paytime))"=>"付款时间",
            "case
                when orderinfo_paystyle=1 then '支付宝'
                when orderinfo_paystyle=6 then '网银在线'
                when orderinfo_paystyle=0 then '未支付'
                else '其它' end
                "=>"付款方式",
            'order_goodsname'=>"商品名称",
            'order_goodssn'=>"商品货号",
            'orderinfo_provincename'=>"收货人省",
            'orderinfo_cityname'=>"收货人市",
            'orderinfo_address'=>"收货人详细地址",
            'orderinfo_reciver'=>"收货人姓名",
            'orderinfo_mobile'=>"收货人手机",
			'(select recruiter_name from yishu_paimai_recruiter where recruiter_id=goods_recruiterid)'=>"征集人姓名",
			'(select seller_name from yishu_paimai_seller where seller_id=goods_sellerid)'=>"被征集商家",
			'goods_note'=>"商家备注",
            "from_unixtime(orderinfo_createtime,'%Y-%m-%d')"=>"订单生成时间",
            /*'goods_cost'=>'成本价',
            'orderinfo_amount-goods_cost'=>'利润'*/
            );
        $this->orderinfo = M("PaimaiOrderInfo")
        ->join("yishu_paimai_order on orderinfo_id=order_orderinfoid")
        ->join("yishu_paimai_goods on order_goodsid=goods_id")
        ->field($field)->where($orderinfo_where)->order('orderinfo_id asc')->select();
        $this->allamount=M("PaimaiOrderInfo")
        ->join("yishu_paimai_order on orderinfo_id=order_orderinfoid")
        ->join("yishu_paimai_goods on order_goodsid=goods_id")
        ->field($field)->where($orderinfo_where)->order('orderinfo_id asc')->sum("orderinfo_amount");
        //p($this->allamount);
        //p($this->orderinfo);
        //第一个数组为二维数组数据,第二个数组$field为一维数组title
        $arr=array(
            'data'=>$this->orderinfo,
            'title'=>$field,
            'allamount'=>$this->allamount,
            );
        Vendor('Csv.Csv');
        $CSV = new \Csv;
        //$CSV->export_csv($this->orderinfo,$field);
        $CSV->export_csv($arr);
    }
    /*
        财务打印
    */
    public function caiwuprint(){
        $orderinfo_where=array();
        //开始时间
        $starttime=I('starttime');
        $endtime=I('endtime');
        if(!empty($starttime)&&empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('GT',strtotime($starttime));
        }elseif(empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('LT',strtotime($endtime));
        }elseif(!empty($starttime)&&!empty($endtime)){
            $orderinfo_where['orderinfo_createtime']=array('BETWEEN',array(strtotime($starttime),strtotime($endtime)));
        }
        //打印条件
        if(!empty($starttime)){
            $this->starttime=$starttime;
        }else{
            $this->starttime="(空)";
        }
        if(!empty($endtime)){
            $this->endtime=$endtime;
        }else{
            $this->endtime="(空)";
        }

        //$this->endtime=$endtime;
        //付款状态
        $paystatus=I('paystatus');

        if($paystatus==1){//未付款
            $orderinfo_where['orderinfo_paystatus']=0;
        }elseif($paystatus==2){//已经付款
            $orderinfo_where['orderinfo_paystatus']=2;
        }
        if(!empty($paystatus)){
            if($paystatus==0){
                $this->paystatus="全部";
            }elseif($paystatus==1){
                $this->paystatus="未付款";
            }elseif($paystatus==2){
                $this->paystatus="已经付款";
            }
        }else{
            $this->paystatus="(空)";
        }
        //分类
        $cat_id=I("cat");

        if(!empty($cat_id)){
            $orderinfo_where['goods_catid']=$cat_id;
            $this->cat_name=M('PaimaiCategory')->where("cat_id=".$cat_id)->getField("cat_name");
        }else{
            $this->cat_name="(全部)";
        }
        $field=array(
           /* "case
                when goods_isshow=0 then '不显示'
                else '显示' end"
            => "goods_isshow",*/
            'orderinfo_paystatus',
            'orderinfo_id',
            'orderinfo_sn',
            'orderinfo_amount',
             "case
                when orderinfo_paystatus=0 then '未付款'
                when orderinfo_paystatus=2 then '已经付款' end
                "=>'orderinfo_paystatus',
            "if(orderinfo_paytime<=0,0,from_unixtime(orderinfo_paytime))"=>"orderinfo_paytime",
            "case
                when orderinfo_paystyle=1 then '支付宝'
                when orderinfo_paystyle=6 then '网银在线'
                when orderinfo_paystyle=0 then '未支付'
                else '其它' end
                "=>"orderinfo_paystyle",
            'order_goodsname',
            'order_goodssn',
            "from_unixtime(orderinfo_createtime,'%Y-%m-%d')"=>"orderinfo_createtime",
           'orderinfo_uid',
           'order_goodssn',
           'order_goodsname',
           'goods_cost'
            );
        $this->orderinfo = M("PaimaiOrderInfo")
        ->join("yishu_paimai_order on orderinfo_id=order_orderinfoid")
        ->join("yishu_paimai_goods on order_goodsid=goods_id")
        ->field($field)->where($orderinfo_where)->order('orderinfo_id desc')->select();
        //总金额
        $this->allamount=M("PaimaiOrderInfo")
        ->join("yishu_paimai_order on orderinfo_id=order_orderinfoid")
        ->join("yishu_paimai_goods on order_goodsid=goods_id")->field($field)->where($orderinfo_where)->sum("orderinfo_amount");
        $this->allcount=M("PaimaiOrderInfo")
        ->join("yishu_paimai_order on orderinfo_id=order_orderinfoid")
        ->join("yishu_paimai_goods on order_goodsid=goods_id")->field($field)->where($orderinfo_where)->count();
        //第一个数组为二维数组数据,第二个数组$field为一维数组title
        $this->display("caiwuprint");
    }
  
  // 提现打印  JS调用方法
   public function manageprint(){
    $ids=I('ids');
    $where=array(
        "id"=>array('IN',$ids),
        );

    $txs=M('paimai_deposit')->where($where)->order("id desc")->select();
	foreach($txs as $k=>$v) {
            $temp = $this->getMemberInfo($v['uid']);
            $txs[$k]['realname'] = $temp['0']['realname'];
            $txs[$k]['mobile'] = $temp['0']['mobile'];
            $txs[$k]['all_amount'] = $this->getUserAmountByUid($v['uid']);//用户充值金额
            $txs[$k]['tx_money'] = $this->tx_money($v['uid']);//已经成功提现的金额

            $frozen = $this->getFrozenmoneyByUid($v['uid']);//参拍扣除总金额
            $txs[$k]['cuted_money'] = $this->cuted_money($v['uid']);//没按时付款扣除金额
            $return = $this->getReturnMoneyByUid($v['uid']);//没拍到返回金额
            //冻结的保证金
            $frozen_money = $frozen + $return - $txs[$k]['cuted_money'];//冻结的保证金=参拍扣除总金额-没拍到返回金额-没按时付款扣除金额
            //可提现金额
            $txs[$k]['ky_money'] = $txs[$k]['all_amount'] + $txs[$k]['cuted_money'] + $frozen_money + $txs[$k]['tx_money'];
            //可提现金额（剩余可用金额）=充值金额-没按时付款扣除金额-冻结的保证金-已经成功提现的金额
        }


	$this->txs = $txs;
	//p($txs);

    $this->display("manageprint");

   }
   
   /*
    修改订单备注
   */
   public function updateordernote(){
		$orderinfo_id=I('id');
		$orderinfo_note=I('note');
		M('PaimaiOrderInfo')->where("orderinfo_id=$orderinfo_id")->setField('orderinfo_note',$orderinfo_note);
		echo '修改成功';
   }

   /*
    征集人员订单
   */
   public function zhengjiorder(){
        //显示模板
        if(empty($_GET)){
            $recruiter_where=array(
                'recruiter_isshow'=>0,
                'recruiter_isdelete'=>0
            );
            $this->recruiter_arr=M('PaimaiRecruiter')->where($recruiter_where)->order('recruiter_id desc')->select();
            $this->display();
            exit;
        }
        $p=I('page',1,'intval');
        $rows=I('rows',10,'intval');
        $action=I('action');

        //征集人ID筛选
        
        //p($_POST);
        $order_field=array(
            "*",
        );

    #条件
        $order_where=array();
        //征集人
        $recruiterid=I('recruiter_id');
        if(!empty($recruiterid)){
            $order_where['goods_recruiterid']=$recruiterid;
        }
        //时间
        $starttime=strtotime(I('starttime'));
        $endtime=strtotime(I('endtime'));
        if(!empty($endtime)){
            $endtime+=86400;    
        }
        if(!empty($starttime)&&empty($endtime)){//有开始时间，没有结束时间
            $order_where['order_createtime']=array('GT',$starttime);
             
        }elseif(empty($starttime)&&!empty($endtime)){//没有开始时间，只有结时间 
            $order_where['order_createtime']=array('LT',$endtime);
                        
        }elseif(!empty($starttime)&&!empty($endtime)){//有开始时间和结束时间
            $order_where['order_createtime']=array('between',array($starttime,$endtime));
        }

        //订单ID筛选
        $orderinfo_id=I('orderinfo_id');
        if(!empty($orderinfo_id)){
            $order_where['orderinfo_id']=array('IN',$orderinfo_id);
        }

        if($action=="finish_order"){//已经付过款的订单
            $finish_order_where=$order_where;
            $finish_order_where['orderinfo_paystatus']=2;
            $order_arr=M('PaimaiOrder')
                        ->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')
                        ->join('yishu_paimai_goods on order_goodsid=goods_id')
                        ->where($finish_order_where)->order("order_createtime desc")->page($p . ',' . $rows)->select();
            //分页商品总数
            $total_num=M('PaimaiOrder')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where($finish_order_where)->count();
        }elseif($action=="return_order"){//申请退换货的订单
            $order_arr=M('PaimaiOrder')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where("orderinfo_status in (4,8)")->order("orderinfo_returntime desc")->page($p . ',' . $rows)->select();
            //分页商品总数
            $total_num=M('PaimaiOrder')->join('yishu_paimai_order_info on order_orderinfoid=orderinfo_id')->where("orderinfo_status in (4,8)")->count();
        }
        //对数组进行处理
        foreach ($order_arr as $k => &$v) {
            //处理图片  
            $v['thumb'] = D('Content/Document')->getPic($v['order_goodsrecordid'], 'thumb');
            //订单生成时间
            $v['order_createtime']=date('Y-m-d H:i:s',$v['order_createtime']);
            //申请时间
            if($v['orderinfo_returntime']!=0){
                $v['orderinfo_returntime']=date('Y-m-d H:i:s',$v['orderinfo_returntime']);
             }
             //征集人
             $v['recruiter_name']=getrecruiter($v['goods_recruiterid']);
             //商家
             $v['seller_name']=getseller($v['goods_sellerid']);
            //用户名
            $v['uname']=getUsername($v['order_uid'],0);//获得用户名
            //手机
            $v['uphone']=getUsername($v['order_uid'],0,'mobile');//获得用户手机
        }
        //组织数据
        $data=array(
            'total'=>$total_num,
            'rows'=>$order_arr
        );
        $this->ajaxReturn($data,"JSON");
   }
   /*传入订单号显示物流信息*/
   public function wuliupath(){

        $num = I('id', 0, intval); 
        if(empty($num)){
            echo "暂无信息";
            exit;
        }
        $express=new \Org\Util\Express;
        
        $result  = $express->getorder($num);

        $str="";
        foreach ($result['data'] as $k => &$v) {
            $str.=$v['time']."--".$v['context']."<br/>";
        }
        echo $str;
   }
  

}
