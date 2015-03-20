<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午5:45
 *会员管理控制器
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminMemberController extends AdminController{
    /*
    会员列表
    */
    public function index(){
        $tmp=I("action");

    #请求模板
        if(empty($tmp)){
            $this->display();
            exit;
        }

    #请求数据
        $p = I('page',1,'intval');    
        $rows=I('rows');

        //字段
        $member_field=array(
            "*",
        );

        //条件:逻辑：如果只有开始时间则取比开始时间大，如果有结束时间则取比结束时间小的，如果两个都有则取中间的数据    
        $this->starttime=I('starttime');
        $this->endtime=I('endtime');
        $starttime=strtotime(I('starttime'));
        $endtime=strtotime(I('endtime'));
        if(!empty($endtime)){
            $endtime+=86400;    
        }
        $member_where=array();
		//用户名
		$uname = I('uname','');
		if($uname){
		    $member_where['username'] = $uname;
		}else{
			//有开始时间，没有结束时间
			if(!empty($starttime)&&empty($endtime)){
				$member_where['regdate']=array('GT',$starttime);
			//没有开始时间，只有结时间      
			}elseif(empty($starttime)&&!empty($endtime)){
				$member_where['regdate']=array('LT',$endtime);
					//有开始时间和结束时间        
			}elseif(!empty($starttime)&&!empty($endtime)){
				$member_where['regdate']=array('between',array($starttime,$endtime));

			}
			//手机号
			$mobile=I('mobile');
			if(!empty($mobile)){
				$member_where['mobile']=array('IN',$mobile);
			}
		}
        $sort=I('sort');
        $order=I('order');
        $member_order="";
        if(!empty($sort)){
            $member_order=$sort." ".$order.",";
        }
        $member_order.="mid desc";
        #数组
        $this->member=M('member','bsm_','DB_BSM')->field($field)->page($p . ',' . $rows)->where($member_where)->order($member_order)->select();
        $total_num=M('member','bsm_','DB_BSM')->where($member_where)->count();

        //组织数据
        $data=array(
            'total'=>$total_num,
            'rows'=>$this->member
        );
        $this->ajaxReturn($data,"JSON");
    }
    public function downmember(){
	//字段
	$member_field=array(
            "mid"=>'用户ID',
            "username"=>'用户名',
            "from_unixtime(regdate,'%Y-%m-%d %H:%s:$i')"=>'注册时间',
            "loginnum"=>'登录次数',
            "mobile"=>'手机',
            "email"=>'电子邮箱',
            "mobile_status"=>'手机验证状态',
            "email_status"=>'邮箱验状态',
	);
	#条件
	
	$starttime=strtotime(I('starttime'));
	$endtime=strtotime(I('endtime'));
	if(!empty($endtime)){
		$endtime+=86400;	
	}
	$member_where=array();
	//有开始时间，没有结束时间
	if(!empty($starttime)&&empty($endtime)){
		$member_where['regdate']=array('GT',$starttime);
	//没有开始时间，只有结时间	    
	}elseif(empty($starttime)&&!empty($endtime)){
		$member_where['regdate']=array('LT',$endtime);
	//有开始时间和结束时间		
	}elseif(!empty($starttime)&&!empty($endtime)){
		$member_where['regdate']=array('between',array($starttime,$endtime));
	}
    //手机号
    $mobile=I('mobile');
    if(!empty($mobile)){
        $member_where['mobile']=array('IN',$mobile);
    }
	$this->member=M('member','bsm_','DB_BSM')->field($member_field)->where($member_where)->order("mid desc")->select();
	
	#下载
	//第一个数组为二维数组数据,第二个数组$field为一维数组title
        $arr=array(
            'data'=>$this->member,
            'title'=>$member_field,
            );
        Vendor('Csv.Csv');
        $CSV = new \Csv;
        $CSV->export_csv($arr);
    }
    /*
    用户出价记录
    */
    public function bidrecordlist(){
        $bidrecordlist_field=array(
            "*",
            "FROM_UNIXTIME(bidrecord_time,'%Y-%m-%d %H:%i')"=>'bidrecord_time',
            );

    #where
        $bidrecordlist_where=array(
            'bidrecord_uid'=>array('NEQ',0),
            );
        $starttime=I("starttime");
        //p($starttime);
        //时间
        if(!empty($starttime)){
            $starttime=strtotime($starttime);
            $endtime=$starttime+86400;
            $bidrecordlist_where['bidrecord_time']=array('BETWEEN',array($starttime,$endtime));
            $this->starttime=date('Y-m-d',$starttime);
        }
        //状态
        $status=I('status',0,'intval');

        /*if($status==1){
            $bidrecordlist_where['bidrecord_id']=array('EQ','');//已出价
        }elseif($status==2){
            $bidrecordlist_where['order_status']=2;//已经付款
        }*/

    #order
        $bidrecordlist_order="";
        if(empty($starttime)){
            $bidrecordlist_order.="bidrecord_id desc,";
        }
        $price=I('price',0,'intval');
        if($price==1){
            $bidrecordlist_order.="bidrecord_price asc,";
        }elseif($price==2){
            $bidrecordlist_order.="bidrecord_price desc,";
        }
        $bidrecordlist_order=substr($bidrecordlist_order, 0,-1);
        //p($bidrecordlist_order);

    #分页
        $p = I('p',1,'intval');
        //每页显示的数量
        $prePage = 20;
        $bidrecordlist_arr=M('PaimaiBidrecord')->join("left join yishu_paimai_order on bidrecord_goodsid=order_goodsid")->field($bidrecordlist_field)->where($bidrecordlist_where)->order($bidrecordlist_order)->page($p . ',' . $prePage)->select();
        //echo M('PaimaiBidrecord')->getLastSql();exit;
        $status=I("status",0,"intval");
        $temp_arr=array();
        foreach ($bidrecordlist_arr as $k => &$v) {
            //出价状态
            $v['order_status']=$this->bidgoodsstatus($v['bidrecord_goodsid']);
            //出价商品名字通过函数调用
            $v['goods_name']=$this->getgoodsinfo($v['bidrecord_goodsid'])['goods_sn'];
            //用户名通过函数调用
            $v['user_name']=$this->getuserinfo($v['bidrecord_uid'])['username'];
            if($status==1 && $v['order_status']=="已出价"){//已经出价
                $temp_arr[]=$v;
            }elseif($status==2 && $v['order_status']=="已拍得"){
                $temp_arr[]=$v;
            }elseif($status==3 && $v['order_status']=="已付款"){
                $temp_arr[]=$v;
            }
        }
        //p($bidrecordlist_arr);
        //p(temp_arr);
        if(!empty($status)){
            $bidrecordlist_arr=$temp_arr;
        }
        
        $this->bidrecordlist_arr=$bidrecordlist_arr;
        //总数量
        $total_num=M('PaimaiBidrecord')->join("left join yishu_paimai_order on bidrecord_goodsid=order_goodsid")->where($bidrecordlist_where)->count();
        $Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        //格式
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        $show= $Page->show(); // 分页显示输出
        $suffix=$_SERVER['QUERY_STRING'];
        //去除开头两个字符串,并替换&&为?
        $suffix=str_replace("&&","?&",substr($suffix,3));
        //去除分页 p=3中的数字
        $suffix=preg_replace("/&p=(\d+)*/","",$suffix);
        $this->page=preg_replace("/(.*)Paimai\/AdminMember\/bidrecordlist(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);
        $this->display();
    }
    /*
    返回状态
    */
    public function bidgoodsstatus($goods_id){

        $bidgoods_arr=M('PaimaiOrder')->where("order_goodsid=$goods_id")->find();
        //本条记录的状态,默认为0,如果为1,本商品已经提交订单但没有付款,如果为2,则已经付款,3为本订单锁定(到期没有付款本订单失效)
        //return $bidgoods_arr['order_status'];
        if(empty($bidgoods_arr)){
            return "已出价";
        }elseif($bidgoods_arr['order_status']==2){
            return "已付款";
        }elseif($bidgoods_arr['order_status']==3){
            return "已锁定";
        }elseif($bidgoods_arr['order_status']==0){
            return "已拍得";
        }
    }
    /*根据商品id，得到商品其它字段信息*/
    public function getgoodsinfo($goods_id,$field=""){
        if(!empty($field)){
            $goods_field=array($field);
        }
        return M('PaimaiGoods')->field($goods_field)->find($goods_id);
    }
    /*根据用户id，得到用户其它字段信息*/
    public function getuserinfo($user_id,$field=""){
        if(!empty($field)){
            $user_field=array($field);
        }
        return M('member','bsm_','DB_BSM')->field($user_field)->where("mid=$user_id")->find();
        //return M()->db(1, '')->table("yishuv2.yishu_paimai_order_info")->field("mid=$user_id")->find();
    }
    /*用户资金流*/
    public function getmoneybyuid(){

        $p=I('page',1,'intval');
        $rows=I('rows',10,'intval');
        $tag=I('tag');

        if(empty($tag)){
            $this->display();
            exit();
        }
        $uid = I('uid');
        //查询条件
        $recharge_field=array(
            'recharge_id',
            'recharge_sn',
            'recharge_money',
            'recharge_style',
            'recharge_status',
            'recharge_createtime',
            );
        $recharge_where = array(
            'recharge_uid' => $uid,
            );
        
        $recharge_arr = M('paimai_recharge')->field($recharge_field)->where($recharge_where)->page($p . ',' . $rows)->select();
        
        $total_num=M('paimai_recharge')->where($recharge_where)->count();
        
        //组织数据
        $data=array(
            'total'=>$total_num,
            'rows'=>$recharge_arr
        );
        $this->ajaxReturn($data,"JSON");
    }
    
} 
