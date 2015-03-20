<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Mobile\Controller;

use Mobile\Controller\MobileController;

class IndexController extends MobileController
{
    public function _initialize()
    {
        parent::_initialize();
		$this->title="拍卖专场";
        $this->keywords="拍卖专场";
        $this->desc="拍卖专场";
    }
    /**
     * wap首页
     */
    public function index()
    {
        //今天晚上22点时间戳
        $today_fixtime=strtotime(date("Y-m-d", time()))+3600*22;
        //今天早上0点时间戳
        $today_starttime=strtotime(date("Y-m-d", time()));
        //今天晚上23点59分时间戳
        $today_endtime=strtotime(date("Y-m-d", time()))+84600-1;
        //如果现在时间比固定时间小，则time_tag=1,如果比固定时间大则time_tag=2
        if(time()<$today_fixtime){
            $time_tag=1;
        }else{
            $time_tag=2;
        }
        //p(date("Y-m-d H:i:s",$today_endtime));

    #首页专场焦点图
        $specialfocus_field=array(
            'special_id',
            'special_name',
            'special_endtime',
            'special_starttime',
            'recordid',
            );
        //where
        $specialfocus_where=array(
            'special_isshow'=>1,
            'special_isdelete'=>0,
        );
        //对where进行修改
        if($time_tag==1){//如果现在时间比晚上10点小则显示昨天22点到今天晚上22点之前开拍的
            $specialfocus_where['special_starttime']=array('LT',$today_fixtime-84600);
            $specialfocus_where['special_endtime']=array('GT',$today_fixtime);
        }else{//如果现在时间在22点到24点之间，则显示在今天22点到明天晚上22点之前正在进行的
            $specialfocus_where['special_starttime']=array('LT',$today_fixtime);
            $specialfocus_where['special_endtime']=array('GT',$today_endtime);
        }
        //limit
        $specialfocus_limit=5;
        $specialfocus_arr=M('PaimaiSpecial')->field($specialfocus_field)->where($specialfocus_where)->limit($specialfocus_limit)->order("special_order desc,special_id desc")->select();
        //得到图片
        foreach ($specialfocus_arr as $k => &$v) {
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
         }
         $this->specialfocus=$specialfocus_arr;
    

    #正在进行
        $ingspecial_field=array(
            'special_id',
            'special_name',
            'special_endtime',
            'special_starttime',
            'special_hits',
            'recordid',
            );

        $ingspecial_where=array(
            'special_isshow'=>1,
            'special_isdelete'=>0,
            );
        $ingspecial_where['special_starttime']=array('LT',time());//开拍时间比现在时间大
        $ingspecial_where['special_endtime']=array('GT',time());//结束时间比现在时间大

        $ingspecial_limit=10;

        $ingspecial_arr=M('PaimaiSpecial')->field($ingspecial_field)->where($ingspecial_where)->limit($ingspecial_limit)->order('special_order desc,special_id asc')->select();
        foreach ($ingspecial_arr as $k => &$v) {
            //得到本专场下面商品的竞拍次数
            $v['bidtimes']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_bidtimes");
            //点击量
            $v['hits']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_hits");
            //得到图片
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
         }
        $this->IngSpecial=$ingspecial_arr;
        //p($ingspecial_arr);

    #即将开始
        $startspecial_field=array(
            'special_id',
            'special_name',
            'special_endtime',
            'special_starttime',
            'special_hits',
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
        //得到图片
        foreach ($startspecial_arr as $k => &$v) {
            //得到本专场下面商品的竞拍次数
            $v['bidtimes']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_bidtimes");
            //点击量
            $v['hits']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_hits");
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
         }
        $this->StartSpecial=$startspecial_arr;
        //p($startspecial_arr);

		$this->display("Front:index_auction");
    }

	//拍卖首页
	public function auction(){
	    //今天晚上22点时间戳
        $today_fixtime=strtotime(date("Y-m-d", time()))+3600*22;
        //今天早上0点时间戳
        $today_starttime=strtotime(date("Y-m-d", time()));
        //今天晚上23点59分时间戳
        $today_endtime=strtotime(date("Y-m-d", time()))+84600-1;
        //如果现在时间比固定时间小，则time_tag=1,如果比固定时间大则time_tag=2
        if(time()<$today_fixtime){
            $time_tag=1;
        }else{
            $time_tag=2;
        }
        //p(date("Y-m-d H:i:s",$today_endtime));

    #首页专场焦点图
        $specialfocus_field=array(
            'special_id',
            'special_name',
            'special_endtime',
            'special_starttime',
            'recordid',
            );
        //where
        $specialfocus_where=array(
            'special_isshow'=>1,
            'special_isdelete'=>0,
        );
        //对where进行修改
        if($time_tag==1){//如果现在时间比晚上10点小则显示昨天22点到今天晚上22点之前开拍的
            $specialfocus_where['special_starttime']=array('LT',$today_fixtime-84600);
            $specialfocus_where['special_endtime']=array('GT',$today_fixtime);
        }else{//如果现在时间在22点到24点之间，则显示在今天22点到明天晚上22点之前正在进行的
            $specialfocus_where['special_starttime']=array('LT',$today_fixtime);
            $specialfocus_where['special_endtime']=array('GT',$today_endtime);
        }
        //limit
        $specialfocus_limit=5;
        $specialfocus_arr=M('PaimaiSpecial')->field($specialfocus_field)->where($specialfocus_where)->limit($specialfocus_limit)->order("special_order desc,special_id desc")->select();
        //得到图片
        foreach ($specialfocus_arr as $k => &$v) {
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
         }
         $this->specialfocus=$specialfocus_arr;
    

    #正在进行
        $ingspecial_field=array(
            'special_id',
            'special_name',
            'special_endtime',
            'special_starttime',
            'special_hits',
            'recordid',
            );

        $ingspecial_where=array(
            'special_isshow'=>1,
            'special_isdelete'=>0,
            );
        $ingspecial_where['special_starttime']=array('LT',time());//开拍时间比现在时间大
        $ingspecial_where['special_endtime']=array('GT',time());//结束时间比现在时间大

        $ingspecial_limit=10;

        $ingspecial_arr=M('PaimaiSpecial')->field($ingspecial_field)->where($ingspecial_where)->limit($ingspecial_limit)->order('special_order desc,special_id asc')->select();
        foreach ($ingspecial_arr as $k => &$v) {
            //得到本专场下面商品的竞拍次数
            $v['bidtimes']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_bidtimes");
            //点击量
            $v['hits']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_hits");
            //得到图片
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
         }
        $this->IngSpecial=$ingspecial_arr;
        //p($ingspecial_arr);

    #即将开始
        $startspecial_field=array(
            'special_id',
            'special_name',
            'special_endtime',
            'special_starttime',
            'special_hits',
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
        //得到图片
        foreach ($startspecial_arr as $k => &$v) {
            //得到本专场下面商品的竞拍次数
            $v['bidtimes']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_bidtimes");
            //点击量
            $v['hits']=M('PaimaiGoods')->where("goods_specialid=".$v['special_id'])->sum("goods_hits");
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
         }
        $this->StartSpecial=$startspecial_arr;
        //p($startspecial_arr);

		$this->display("Front:index");
	}
    
	//用户登录页面
	public function login(){
	    $this->display('Admin:login');
	}

	//用户注册页面
	public function register(){
	    $time = session('verify');
	    $dif = time() - $time;
	    if ($dif > 120)
	    {
	        session('verify', null);
	        $send = true;
	    }else
	    {
	        $send = false;
	    }
	    $this->assign('send', $send);
	    $this->assign('test', true);
	    $this->display('Admin:register');
	}
	
	//测试手机端用户注册页面
	public function abc()
	{
	    $time = session('verify');
	    $dif = time() - $time;
        if ($dif > 120)
        {
        	session('verify', null);
        	$send = true;
        }else 
        {
        	$send = false;
        }
	    $this->assign('test', true);
	    $this->assign('send', $send);
	    $this->display('Admin:register');
	}

	//注册成功页面
	public function reg_succ(){
	    $this->display('Admin:reg_succ');
	}

	//登录成功/失败页面
	public function login_status(){
	    $this->is_succ = I('status', 0, 'intval');
		if($this->is_succ){
		    $this->page_title = '登录成功';
		}else{
		    $this->page_title = '登录失败';
		}
		$this->display('Admin:login_status');
	}

	//登出成功/失败页面
	public function logout(){
	    $this->is_succ = I('status', 0, 'intval');
		if($this->is_succ){
			$this->page_title = '退出成功';
		}else{
			$this->page_title = '退出失败';
		}
		$this->display('Admin:logout');
	}
}
