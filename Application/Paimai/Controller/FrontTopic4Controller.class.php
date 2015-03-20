<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Paimai\Controller;

use Paimai\Controller\PaimaiPublicController;

class FrontTopic4Controller extends PaimaiPublicController
{

    /**
     * 初始化
     */
    public function _initialize()
    {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
       // $this->assign('title', '中国艺术网-拍卖');
    }

    /**
     * 列表信息
     */
    public function index()
    {
        $special_id = I("get.id", 0, "intval");
        $this->special_id=$special_id;

        $where['special_id'] = $special_id;
        $where['special_isshow'] = 1;
        //查找专场
        $special = M("PaimaiSpecial")->where($where)->find();

    	//p($special);
    	$this->title=$special['special_name']."_拍卖专场_中国艺术网在线竞拍";
    	//$this->keywords=$special['special_keywords'];
    	//$this->desc=$special['special_intro'];
    	$this->keywords=$special['special_name'].",拍卖专场";
    	$this->desc="中国艺术网在线竞拍隆重上线".$special['special_name']."拍卖专场活动，玩家可以在".$special['special_name']."拍卖专场上拍卖到最新最有价值的藏品，数量有限，先到先得，中国艺术网在线竞拍安全交易，值得信赖。";

        $special['thumb'] = D('Content/Document')->getPic($special['recordid'], 'thumb');
        if (empty($special)) {
            $this->error("此拍卖会不存在");
            exit;
        }


        $this->assign("special", $special);
        //p($special);
        //分配当前时间
        $this->assign("shijian", time());
		
        $where['goods_id'] = array("not in", $special_id);
        $this->otherspecial = M("PaimaiSpecial")->where($where)->find();
        //p($otherspecial);


        /*******************order开始**************************/
        //按数量排序
        $order = "";
        $count = $_GET['count'];
        $this->count = $count;
		$this->filter_count = $count;
        if (isset($count) && $count == 1) { //降序
            $order .= "goods_bidtimes desc,";
        }
        if (isset($count) && $count == 2) { //升序
            $order .= "goods_bidtimes asc,";
        }
        //按价格排序
        $price = $_GET['price'];
        $this->price = $price;
		$this->filter_price = $price;
        if (isset($price) && $price == 1) {
            $order .= "goods_nowprice desc,";
        }
        if (isset($price) && $price == 2) {
            $order .= "goods_nowprice asc,";
        }
        $order .= "goods_id desc ";
        /* echo "where条件".$where."<br/>";
         echo "order条件:".$order."<br/>";*/

        /*************order结束***************/
        //查找本专场下的商品

        //$this->goods=M('PaimaiGoods')->where($where2)->order($order)->select();

        $p = isset($_GET['p']) ? $_GET['p'] : 1;
        //每页显示的数量
        $prePage = 9;
        $where2['goods_specialid'] = $special_id;
        $where2['goods_isshow'] = 1;
        //$list = M('PaimaiGoods')->where($where2)->page($p . ',' . $prePage)->order($order)->select();
		//不要分页
		$list = M('PaimaiGoods')->where($where2)->order($order)->select();
		//不要分页结束
        foreach ($list as $k => $v) {
            $list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
        }
        $page_total_count=M('PaimaiGoods')->where($where2)->count();
        $this->assign("lists", $list);
        $Page = new \Think\Page($page_total_count, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        //p($show);
		$suffix=$_SERVER['QUERY_STRING'];
		//echo $suffix."<br/>";
		//去除开头两个字符串,并替换&&为?
		$suffix=str_replace("&&","?&",substr($suffix,3));
		//echo $suffix."<br/>";
		//去除分页 p=3中的数字
		$suffix=preg_replace("/&p=(\d+)*/","",$suffix);
		
		$show=preg_replace("/(.*)Paimai\/FrontTopic\/index(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);
        $this->assign('page', $show); // 赋值分页输出
        //分配body css
        $this->assign("css", 'auction-ol auction-topic');
		$this->shijian = time();
        //p($this->goods);
        $this->display('Front:topictest');
    }
    public function ajax_loadmore(){

        if(!IS_AJAX)$this->error("你请求的页面不存在");

        $p=I('p',0,'intval');

        //请求的数量
        $num=I('num',0,'intval');

        $special_id=I('id',0,'intval');
        //p($p);
        //p($special_id);
        $specialgoods_field=array(//字段后期要优化
            "recordid",
            "goods_id",
            "goods_name",
            "goods_sn",
            "goods_nowprice",
            "goods_endtime",
            "goods_bidtimes",
            "goods_starttime",
        );
        $specialgoods_where=array(
            'goods_specialid'=>$special_id,
            'goods_isshow'=>1,
            );

        $specialgoods_limit=$p.",".$num;

        $goods_arr = M('PaimaiGoods')->field($specialgoods_field)->where($specialgoods_where)->limit($specialgoods_limit)->order("goods_id desc")->select();
        if(empty($goods_arr)){
            $data['status']=0;
            echo json_encode($data);
            exit;
        }
        foreach ($goods_arr as $k => &$v) {
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
            $v['leave_time']=$v['goods_endtime']-time();
            if($v['goods_starttime']>time()){
                //未开始
                $v['status']=1;
                $v['price']="<span class='price_type'>起拍价</span><b class='font_color green'>￥".$v['goods_nowprice']."</span></b>";
            }elseif($v['goods_starttime']<time()&&$v['goods_endtime']>time()){
                //正在进行
                $v['status']=2;
                $v['price']="<span class='price_type'>当前价</span><b class='font_color red'>￥".$v['goods_nowprice']."</span></b>";
            }elseif($v['goods_endtime']<time()){
                //已经结束
                $v['status']=3;
                $v['price']="<span class='price_type'>成交价</span><b class='font_color black'>￥".$v['goods_nowprice']."</span></b>";
            }
        }
        //p($goods_arr);

        echo json_encode($goods_arr);

    }

}
