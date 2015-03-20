<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Paimai\Controller;

use Paimai\Controller\PaimaiPublicController;

class IndexController extends PaimaiPublicController
{

    public function _initialize()
    {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
	#seo规则
		//导航
	$this->nav="index";
	$this->keywords="在线竞拍,在线拍卖,网上拍卖,古玩在线拍卖,艺术品拍卖,,中国艺术网在线竞拍";
	$this->title="中国艺术网在线竞拍_最大的古玩玉器钱币书画在线艺术品拍卖网站";
	$this->desc="中国艺术网在线竞拍是中国最大的专业在线网上艺术品拍卖网站，为用户提供一个在线的艺术品拍卖、古玩拍卖、古董拍卖、钱币拍卖、玉器拍卖、紫砂拍卖、书画拍卖等藏品的拍卖网站，用户可以在线竞拍最新藏品，最新艺术品。";
	$this->assign("action",__ACTION__);
		//今天开始时间
		//
    }

    /**
     * 列表信息
     */
    public function index()
    {
    	//p(__ACTION__);
	#拍品开售预告
		$weekarray = array("日", "一", "二", "三", "四", "五", "六");
		$tmp = array(
			strtotime("1 day"),
			strtotime("2 day"),
			strtotime("3 day"),
			strtotime("4 day")
		);
		$day = array();
		for ($i = 0; $i < 4; $i++) {
			//显示页面中的日期
			$day[$i]['day'] = date("m-d", $tmp[$i]);
			//显示页面中的星期	
			if($i == 0){
				$day[$i]['week'] = '明天';
			}elseif($i == 1){
				$day[$i]['week'] = '后天';
			}else{
				$day[$i]['week'] = "星期" . $weekarray[date("w", $tmp[$i])];
			}
			//这一天的开始时间戳
			$day[$i]['starttime'] = strtotime(date("Ymd", $tmp[$i]));
			//这一天的终止时间戳
			$day[$i]['endtime'] = strtotime(date("Ymd", $tmp[$i]) + 1);
		}
		//p($day);
		$this->day = $day;
		//循环取出后四天的数据
		$arr = array();
		foreach($day as $v){
			//查询条件
			$where = array(
				'goods_isshow' => '1',
				'special_isdelete' => 0,
				'goods_starttime' => array('between',array($v['starttime']-2*3600-1, $v['endtime']-10*3600-1)),
				);
			//查询字段
			$field = 'goods_id,goods_name,recordid';
			$arr[] = M('PaimaiGoods')->field($field)->where($where)->limit(12)->select();
		}

		//添加图片
		if($arr){
			foreach($arr as $key=>$value){
				foreach($value as $k=>$v){
					$arr[$key][$k]['url'] = U("/paimai/goods-".$v["goods_id"]);
					$arr[$key][$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
				}
			}
		}
		//分配到模板
		$this->arr1 = $arr[0];
		$this->arr2 = $arr[1];
		$this->arr3 = $arr[2];
		$this->arr4 = $arr[3];
		
	#专场焦点图  推荐专场
		$special_field = 'special_id, special_name, special_starttime, special_endtime, recordid';
		
		if(date('H') > 22 || date('H')==22){//如果当前时间大于晚十点
			$special_where = array(
			'special_isshow' => 1,
			'special_isdelete' => 0,
			'special_starttime' => array('between',array(strtotime(date("Y-m-d", time()))-2*3600-1, strtotime(date("Y-m-d", time()))+24*3600))
			);
		} else {
			$special_where = array(
			'special_isshow' => 1,
			'special_isdelete' => 0,
			'special_starttime' => array('between',array(strtotime(date("Y-m-d", time()))-26*3600-1, strtotime(date("Y-m-d", time()))+22*3600-1))
			);
		}
		//修改为四个图片
		$Special=M("PaimaiSpecial")->field($special_field)->where($special_where)->order("special_id asc")->limit(20)->select();
		
		//如果为空，调用最后四个专场
		if(empty($Special)){
			$Special=M("PaimaiSpecial")->where("special_isshow=1 and special_isdelete=0")->order("special_id desc")->limit(20)->select();
		}
		foreach ($Special as $k => $v) {
			$Special[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
			//点击量
			$Special[$k]['hits'] = M('PaimaiGoods')->where(array('goods_specialid'=>$v['special_id']))->sum('goods_hits');
			//出价次数
			$Special[$k]['bidtimes'] = M('PaimaiGoods')->where(array('goods_specialid'=>$v['special_id']))->sum('goods_bidtimes');
		 }
		 //专场名字修改
		 foreach ($Special as $k => $v) {
		 	$aa = explode('——',$v['special_name']);
		 	if(!$aa[1]){
		 		$aa = explode('一一',$v['special_name']);
		 	}
			$Special[$k]['special_name'] = $aa[1];
		 }

		$this->assign("Special",$Special);

	
	#今日22:00场
		$special_field = 'special_id, special_name, special_starttime, special_endtime, recordid';
		if(date('H') >22 || date('H')==22 ){//如果当前时间大于晚十点
			$special_where = array(
				'special_isshow' => 1,
				'special_isdelete' => 0,
				'special_starttime' => array('between',array(strtotime(date("Y-m-d", time()))+22*3600+1, strtotime(date("Y-m-d", time()))+46*3600+1))
			);
		} else {
			$special_where = array(
				'special_isshow' => 1,
				'special_isdelete' => 0,
				'special_starttime' => array('between',array(strtotime(date("Y-m-d", time()))+22*3600-1, strtotime(date("Y-m-d", time()))+46*3600-1))
			);
		}
		
		//修改为四个图片
		$Speciale=M("PaimaiSpecial")->field($special_field)->where($special_where)->order("special_id asc")->limit(20)->select();
		foreach ($Speciale as $k => $v) {
			$Speciale[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
			//点击量
			$Speciale[$k]['hits'] = M('PaimaiGoods')->where(array('goods_specialid'=>$v['special_id']))->sum('goods_hits');
		 }
		 //专场名字修改世间无限丹青手一一油画精
		 foreach ($Speciale as $k => $v) {
		 	$aa = explode('——',$v['special_name']);
		 	if(!$aa[1]){
		 		$aa = explode('一一',$v['special_name']);
		 	}
			$Speciale[$k]['special_name'] = $aa[1];
		 }

		$this->assign("Speciale",$Speciale);

	#左侧分类
		$topcat_field=array(
			"*",
			"cat_name",
			"cat_id",
			"cat_spell",
		);
		$topcat_where=array(
			"cat_show_in_front"=>1,
			"cat_pid"=>0,
		);
		$this->topcat_arr=M("PaimaiCategory")->field($topcat_field)->where($topcat_where)->select();

	#今日拍卖
							
		//正在进行

		//今天0辰时间
		$today_starttime=strtotime(date("Ymd", time()));
		//今天晚上12点
		$today_endtime=strtotime(date("Ymd", time()+86400));
		$ing_field=array(//字段后期要优化
			"*",
		);
		$ing_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			//'goods_starttime'=>array("GT",$today_starttime),
			//'goods_starttime'=>array("LT",time()),
			'goods_starttime'=>array("BETWEEN",array($today_starttime,time())),
		);

		/*//首页5个填满
		$total = 5;
		$IngCount=M("PaimaiGoods")->where($ing_where)->getField('count(goods_id) as count');
		if($IngCount>=$total){
			$remain_ing = $total;
			$remain_future = 0;
		}else{
			$remain_ing = $IngCount;
			$remain_future = $total - $IngCount;
		}*/


		//$IngGoods=M("PaimaiGoods")->field($ing_field)->where($ing_where)->/*limit("5")->*/order("goods_order desc,goods_nowprice desc")->select();
	#如果正常数据为空
		// if(empty($IngGoods)){
		// 	$ing_where=array(
		// 		'goods_isshow'=>1,
		// 		'goods_isdelete'=>0,
		// 		'goods_starttime'=>array("LT",time()),
		// 		'goods_endtime'=>array("GT",time()),
		// 		//'goods_starttime'=>array("BETWEEN",array($today_starttime-84600,time())),
		// 	);
		// 	$IngGoods=M("PaimaiGoods")->field($ing_field)->where($ing_where)->order("goods_starttime asc,goods_id asc")->order("goods_order desc,goods_nowprice desc")->limit(10)->select();
		// }
		// //p($IngGoods);
		// foreach ($IngGoods as $k => $v) {
  //           //$IngGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
  //           $IngGoods[$k]['thumb']=str_replace("..",'',img($v['index_img']));
		//  }
		//  //p($IngGoods);
		// $this->assign("IngGoods",$IngGoods);
		


		//即将开拍
		$start_field=array(
			"*",
		);
		$start_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			//'goods_starttime'=>array("GT",time()),
			'goods_starttime'=>array("LT",$today_endtime),
			'goods_starttime'=>array("BETWEEN",array(time(),$today_endtime)),
		);
		$StartGoods=M("PaimaiGoods")->field($start_field)->where($start_where)->order("goods_order desc,goods_nowprice asc,goods_id asc")->select();
		 foreach ($StartGoods as $k => $v) {
            //$StartGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
            $StartGoods[$k]['thumb']=str_replace("..",'',img($v['index_img']));
            $StartGoods[$k]['goods_starttime']=date("Y-m-d H:i:s",$v['goods_starttime']);

        }
		$this->assign("StartGoods",$StartGoods);

	#总拍品数
		$allcount_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
		);
		//真实数据
		$this->AllCount=M("PaimaiGoods")->where($allcount_where)->count();
		//[假数据]总拍品数
		$this->AllCount=ceil(5641+$this->AllCount);
		

	#出价总数
		$bidcount_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,	
		);
		//真实数据
		$this->BidCount=M("PaimaiGoods")->where($bidcount_where)->sum("goods_bidtimes");
		//[假数据]出价总数
		//$this->BidCount=(12038+$this->BidCount);
		$this->BidCount=ceil($this->AllCount*0.22*40);


	#参拍人数
		$joinercount_where=array(
			'bidrecord_uid'=>array("NEQ",0),	
		);
		//真实数据
		$this->JoinerCount=count(M("PaimaiBidrecord")->where($joinercount_where)->group("bidrecord_uid")->select());
		//假数据
		//$this->JoinerCount=(5812+$this->JoinerCount)*20;
		$this->JoinerCount=ceil($this->AllCount*0.2*15);

	#成交数
		$finishcount_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			//'goods_successid'=>array('NEQ',0),
			'goods_status'=>3,//2为已经被拍下还未付款的商品,状态3为已经付款的商品
		);
		$this->FinishCount=M("PaimaiGoods")->where($finishcount_where)->count();
		//假数据
		//$this->FinishCount=(1568+$this->FinishCount)*45;
		$this->FinishCount=ceil($this->AllCount*0.3);

	#成交额(条件可以和上面进行合并)
		$finishmoney_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			//'goods_successid'=>array('NEQ',0),
			'goods_status'=>3,//2为已经被拍下还未付款的商品,状态3为已经付款的商品
		);
		$FinishMoney=M("PaimaiGoods")->where($finishmoney_where)->sum("goods_nowprice");
		//调用资金格式化函数,将要显示的资金格式化
		$this->FinishMoney=format_money(empty($FinishMoney)?0:$FinishMoney);
		//$this->FinishMoney=format_money((5185300+$this->FinishMoney)*30);
		$this->FinishMoney=format_money($this->FinishCount*3514);

	#最新成交
		$newsuccess_field=array(
			"goods_successid",
			"goods_name",
			"goods_nowprice",
		);
		$newsuccess_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			'goods_bidcreatetime'=>array('NEQ',0),
			'goods_status'=>3,//2为已经被拍下还未付款的商品,状态3为已经付款的商品
		);
		$this->NewSuccess=M("PaimaiGoods")->field($newsuccess_field)->where($newsuccess_where)->order("goods_bidcreatetime desc")->limit(8)->select();
		
	#热门拍品
		$prePage = 9;
        $p = I('p',1,'intval');
        //field
        $hotgoods_field=array(
        	"recordid",
        	"goods_name",
        	"goods_nowprice",
        	"goods_endtime",
        	"goods_id",
        );
        //where
		$hotgoods_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			//'goods_starttime'=>array("LT",time()),
			//'goods_endtime'=>array("GT",time()),
			'goods_starttime'=>array("BETWEEN",array($today_starttime-86400,$today_starttime-1)),//这个是显示开拍时间在昨天的
		);
		$HotGoods=M("PaimaiGoods")->field($hotgoods_field)->where($hotgoods_where)->limit(9)->order("goods_order desc,goods_nowprice asc,goods_id asc")->/*page($p . ',' . $prePage)->*/select();

		//如果昨天商品为空则把正在拍的显示出来
		if(empty($HotGoods)){
			$hotgoods_where['goods_starttime']=array("LT",time());
			$hotgoods_where['goods_endtime']=array("GT",time());
			$HotGoods=M("PaimaiGoods")->field($hotgoods_field)->where($hotgoods_where)->limit(9)->order("goods_id desc")->/*page($p . ',' . $prePage)->*/select();
		}
		$hot_sum_page=M("PaimaiGoods")->where($hotgoods_where)->count();
		
		foreach ($HotGoods as $k => $v) {
            $HotGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
		}

		$Page = new \Think\Page($hot_sum_page, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page->show(); // 分页显示输出
	#分类
		$right_cat_field=array(
			'cat_name',
			'cat_spell',
			);
		$right_cat_where=array(
			'cat_show_in_front'=>1,
			'cat_pid'=>array('NEQ',0),
			'cat_id'=>array('NEQ',17),//其它
			);
		$this->right_cat_arr=M("PaimaiCategory")->field($right_cat_field)->where($right_cat_where)->select();
		
		$suffix=$_SERVER['QUERY_STRING'];
		//去除开头两个字符串,并替换&&为?
		$suffix=str_replace("&&","?&",substr($suffix,3));
		//去除分页 p=3中的数字
		$suffix=preg_replace("/&p=(\d+)*/","",$suffix);
		$show=preg_replace("/(.*)Paimai\/Index\/index(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);
		$this->assign('page', $show); // 赋值分页输出*/

		$this->assign("HotGoods",$HotGoods);
		
        $this->display('Front:index4');
    }
    /*
	管理员预览首页
    */
    public function adminpreview(){
    	//p("22");
    	//验证管理员uid
        if(empty($_SESSION['admin_auth']['uid']))$this->error("你请求的页面不存在");
    	//接受时间变为时间戳
    	$preview_time=strtotime(I('previewtime'));
    	//$preview_time=strtotime('2014-11-18');
    	//前一天晚上10点
    	$preview_time_start=$preview_time-2*3600;
    	//本天晚上21:59
    	$preview_time_end=$preview_time+22*3600-1;
    	/*v($preview_time);
    	v(date("Y-m-d H:i:s",$preview_time_start));
    	p(date("Y-m-d H:i:s",$preview_time_end));*/
    		#拍品开售预告
		$weekarray = array("日", "一", "二", "三", "四", "五", "六");
		$tmp = array(
			strtotime("1 day"),
			strtotime("2 day"),
			strtotime("3 day"),
			strtotime("4 day")
		);
		$day = array();
		for ($i = 0; $i < 4; $i++) {
			//显示页面中的日期
			$day[$i]['day'] = date("m-d", $tmp[$i]);
			//显示页面中的星期	
			if($i == 0){
				$day[$i]['week'] = '明天';
			}elseif($i == 1){
				$day[$i]['week'] = '后天';
			}else{
				$day[$i]['week'] = "星期" . $weekarray[date("w", $tmp[$i])];
			}
			//这一天的开始时间戳
			$day[$i]['starttime'] = strtotime(date("Ymd", $tmp[$i]));
			//这一天的终止时间戳
			$day[$i]['endtime'] = strtotime(date("Ymd", $tmp[$i]) + 1);
		}
		$this->day = $day;

	#专场焦点图
		$special_field=array(
			"recordid",
		);
		$special_where="special_isdelete=0 and special_starttime BETWEEN $preview_time_start and $preview_time_end";
		//修改为四个图片
		$Special=M("PaimaiSpecial")->field($special_field)->where($special_where)->order("special_id desc")->limit(4)->select();
		//p($Special);
		//p(M("PaimaiSpecial")->getLastSql());
		//如果为空，调用最后四个专场
		if(empty($Special)){
			$Special=M("PaimaiSpecial")->where("special_isshow=1 and special_isdelete=0")->order("special_id desc")->limit(4)->select();
		}
		//echo M("PaimaiSpecial")->getLastSql();
		foreach ($Special as $k => $v) {
			$Special[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
		 }
		 
		$this->assign("Special",$Special);

	#左侧分类
		$topcat_field=array(
			"*",
			"cat_name",
			"cat_id",
			"cat_spell",
		);
		$topcat_where=array(
			"cat_show_in_front"=>1,
			"cat_pid"=>0,
		);
		$this->topcat_arr=M("PaimaiCategory")->field($topcat_field)->where($topcat_where)->select();
		//p($this->topcat_arr);
	#今日拍卖
							
		//正在进行

		//今天0辰时间
		$today_starttime=strtotime(date("Ymd", time()));
		//今天晚上12点
		$today_endtime=strtotime(date("Ymd", time()+86400));

		$ing_field=array(//字段后期要优化
			"*",
		);

		$ing_where=array(
			'goods_isdelete'=>0,
			'goods_starttime'=>array("BETWEEN",array($preview_time_start,$preview_time_end)),
			);
		$IngGoods=M("PaimaiGoods")->field($ing_field)->where($ing_where)->/*limit('0,'.$remain_ing)->*/order("goods_starttime asc,goods_id asc")->order("goods_order desc,goods_nowprice desc")->select();
		//p($IngGoods);
	#如果正常数据为空,则显示前一天的
		if(empty($IngGoods)){
			$ing_where=array(
				'goods_isshow'=>1,
				'goods_isdelete'=>0,
				'goods_starttime'=>array("LT",time()),
				'goods_endtime'=>array("GT",time()),
				'goods_starttime'=>array("BETWEEN",array($preview_time_start-86400,$preview_time_start-1)),
			);
			$IngGoods=M("PaimaiGoods")->field($ing_field)->where($ing_where)->order("goods_starttime asc,goods_id asc")->order("goods_order desc,goods_nowprice desc")->select();
		}
		
		foreach ($IngGoods as $k => $v) {
            //$IngGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
            $IngGoods[$k]['thumb']=str_replace("..",'',img($v['index_img']));
		 }
		$this->assign("IngGoods",$IngGoods);
		
		//即将开拍
		$start_field=array(
			"*",
		);
		$start_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			'goods_starttime'=>array("BETWEEN",array($preview_time_end+1,$preview_time_end+86400)),
		);
		$StartGoods=M("PaimaiGoods")->field($start_field)->where($start_where)/*->limit('0,'.$remain_future)*/->order("goods_order desc,goods_nowprice asc,goods_id asc")->select();
		foreach ($StartGoods as $k => $v) {
            //$StartGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
            $StartGoods[$k]['thumb']=str_replace("..",'',img($v['index_img']));
        }
		$this->assign("StartGoods",$StartGoods);


	
	#总拍品数
		$allcount_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
		);
		//真实数据
		$this->AllCount=M("PaimaiGoods")->where($allcount_where)->count();
		//[假数据]总拍品数
		$this->AllCount=ceil(5641+$this->AllCount);
		

	#出价总数
		$bidcount_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,	
		);
		//真实数据
		$this->BidCount=M("PaimaiGoods")->where($bidcount_where)->sum("goods_bidtimes");
		//[假数据]出价总数
		//$this->BidCount=(12038+$this->BidCount);
		$this->BidCount=ceil($this->AllCount*0.22*40);


	#参拍人数
		$joinercount_where=array(
			'bidrecord_uid'=>array("NEQ",0),	
		);
		//真实数据
		$this->JoinerCount=count(M("PaimaiBidrecord")->where($joinercount_where)->group("bidrecord_uid")->select());
		//假数据
		//$this->JoinerCount=(5812+$this->JoinerCount)*20;
		$this->JoinerCount=ceil($this->AllCount*0.2*15);

	#成交数
		$finishcount_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			//'goods_successid'=>array('NEQ',0),
			'goods_status'=>3,//2为已经被拍下还未付款的商品,状态3为已经付款的商品
		);
		$this->FinishCount=M("PaimaiGoods")->where($finishcount_where)->count();
		//假数据
		//$this->FinishCount=(1568+$this->FinishCount)*45;
		$this->FinishCount=ceil($this->AllCount*0.3);

	#成交额(条件可以和上面进行合并)
		$finishmoney_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			//'goods_successid'=>array('NEQ',0),
			'goods_status'=>3,//2为已经被拍下还未付款的商品,状态3为已经付款的商品
		);
		$FinishMoney=M("PaimaiGoods")->where($finishmoney_where)->sum("goods_nowprice");
		//调用资金格式化函数,将要显示的资金格式化
		$this->FinishMoney=format_money(empty($FinishMoney)?0:$FinishMoney);
		//$this->FinishMoney=format_money((5185300+$this->FinishMoney)*30);
		$this->FinishMoney=format_money($this->FinishCount*3514);

	#最新成交
		$newsuccess_field=array(
			"goods_successid",
			"goods_name",
			"goods_nowprice",
		);
		$newsuccess_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			'goods_bidcreatetime'=>array('NEQ',0),
			'goods_status'=>3,//2为已经被拍下还未付款的商品,状态3为已经付款的商品
		);
		$this->NewSuccess=M("PaimaiGoods")->field($newsuccess_field)->where($newsuccess_where)->order("goods_bidcreatetime desc")->limit(8)->select();
		
	#热门拍品
		$prePage = 9;
        $p = I('p',1,'intval');
        //field
        $hotgoods_field=array(
        	"recordid",
        	"goods_name",
        	"goods_nowprice",
        	"goods_endtime",
        	"goods_id",
        );
        //where
		$hotgoods_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			//'goods_starttime'=>array("LT",time()),
			//'goods_endtime'=>array("GT",time()),
			'goods_starttime'=>array("BETWEEN",array($today_starttime-86400,$today_starttime-1)),//这个是显示开拍时间在昨天的
		);
		$HotGoods=M("PaimaiGoods")->field($hotgoods_field)->where($hotgoods_where)->order("goods_order desc,goods_nowprice asc,goods_id asc")->/*page($p . ',' . $prePage)->*/select();
		//如果昨天商品为空则把正在拍的显示出来
		if(empty($HotGoods)){
			$hotgoods_where['goods_starttime']=array("LT",time());
			$hotgoods_where['goods_endtime']=array("GT",time());
			$HotGoods=M("PaimaiGoods")->field($hotgoods_field)->where($hotgoods_where)->order("goods_id desc")->/*page($p . ',' . $prePage)->*/select();
		}
		$hot_sum_page=M("PaimaiGoods")->where($hotgoods_where)->count();
		
		foreach ($HotGoods as $k => $v) {
            $HotGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
		}
		$Page = new \Think\Page($hot_sum_page, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page->show(); // 分页显示输出


		//p($show);
		$suffix=$_SERVER['QUERY_STRING'];
		//去除开头两个字符串,并替换&&为?
		$suffix=str_replace("&&","?&",substr($suffix,3));
		//去除分页 p=3中的数字
		$suffix=preg_replace("/&p=(\d+)*/","",$suffix);
		$show=preg_replace("/(.*)Paimai\/Index\/index(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);
		$this->assign('page', $show); // 赋值分页输出*/

		$this->assign("HotGoods",$HotGoods);
		
        $this->display('Front:indexpreview');
    }
    /*ajax 加载更多*/
    public function ajax_loadmore(){
    	if(!IS_AJAX)$this->error("此页面不存在");
    	$p=I('p',0,'intval');
    	//今天0辰时间
		$today_starttime=strtotime(date("Ymd", time()));
		//今天晚上12点
		$today_endtime=strtotime(date("Ymd", time()+86400));

    	$ing_field=array(//字段后期要优化
			"recordid",
			"goods_id",
			"goods_name",
			"goods_sn",
			"goods_nowprice",
			"goods_endtime",
			"goods_bidtimes",
			"index_img",
		);
		$ing_where=array(
			'goods_isshow'=>1,
			'goods_isdelete'=>0,
			
			'goods_starttime'=>array("BETWEEN",array($today_starttime,time())),
			//'goods_starttime'=>array("LT",time()),
			//'goods_starttime'=>array("BETWEEN",array($today_starttime,time())),
		);
		$ing_limit=$p.",5";
    	$IngGoods=M("PaimaiGoods")->field($ing_field)->where($ing_where)->limit($ing_limit)->order("goods_nowprice desc")->select();
    	if(empty($IngGoods)){
    		$data['status']=0;
    		echo json_encode($data);
    		exit;
    	}
		foreach ($IngGoods as $k => $v) {
	       $IngGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
	       $IngGoods[$k]['index_img']=img($v['index_img'])?img($v['index_img']):$IngGoods[$k]['thumb'];
	       $IngGoods[$k]['leave_time']=$IngGoods[$k]['goods_endtime']-time();
		}
		echo json_encode($IngGoods);
		//p($IngGoods);
    }
    /*ajax进行商品分类*/
    public function getsubitem(){
    	$catid=I("id",0,"intval");
    	/*

					<dl>
					    <dt>全部</dt>
						<dd></dd>
					  </dl>
					  <dl>
					    <dt>题材</dt>
						<dd><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a></dd>
					  </dl>
					  <dl>
					    <dt>材质</dt>
						<dd><a href="#">布面</a><a href="#">模板</a><a href="#">国画</a></dd>
					  </dl>
					  <dl>
					    <dt>题材</dt>
						<dd><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a></dd>
					  </dl>
					  <dl>
					    <dt>题材</dt>
						<dd><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a><a href="#">国画</a></dd>
					  </dl>

    	*/
		$attribute_field=array(
			'attr_name',
			'attr_id',
			);
		$attribute_where=array(
			'cat_attr_catid'=>$catid,
			'attr_isshow'=>1,
			'attr_isdelete'=>0,
			);
		$attribute_arr=M("PaimaiAttribute")->join("yishu_paimai_cat_attr on cat_attr_attrid=attr_id")->field($attribute_field)->where($attribute_where)->select();
	    $str="";
	    $str.="<dl><dt>全部</dt><dd></dd></dl>";
	    foreach($attribute_arr as $k=>$v){
	    	
			$goodsattr_arr=$this->getgoodsattrval($v['attr_id']);
	    	$str.="<dl>";
	    	$str.="<dt>".$v['attr_name']."</dt>";
	    	$str.="<dd>";
	    	foreach ($goodsattr_arr as $p => $q) {
	    		$str.="<a href='#'>".$q['goodsattr_value']."</a>";
	    	}
	    	$str.="</dd>";
	    	$str.="</dl>";
	    }
    	echo $str;
    }
    /*
	通过id找对应的属性值
    */
    public function getgoodsattrval($id){
    	$goodsattr_field=array(
			"*",
			);
		$goodsattr_where=array(
			'goodsattr_attrid'=>$id,
			'goodsattr_id'=>array('LT',"150"),
			);
		return $goodsattr_arr=M("PaimaiGoodsattr")->field($goodsattr_field)->where($goodsattr_where)->select();
    }


}
