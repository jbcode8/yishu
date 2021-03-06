<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-11
 * Time: 上午9:03
 */

namespace Paimai\Controller;


use Paimai\Controller\PaimaiPublicController;

class FrontNoticetestController extends PaimaiPublicController
{
    public function _initialize()
    {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
		//导航
		$this->nav="yugao";
        //$this->assign('title', '中国艺术网-在线竞卖');
    }

    public function index()
    {
		
        $weekarray = array("日", "一", "二", "三", "四", "五", "六");
        //echo "星期".$weekarray[date("w",132654789)];
        $tmp = array(
            strtotime("-3 day"),
            strtotime("-2 day"),
            strtotime("-1 day"),
            time(),
            strtotime("1 day"),
            strtotime("2 day"),
            strtotime("3 day")
        );
        //v($tmp);
        //echo strtotime("20140804")."--".strtotime("20140405");
        $day = array();
        for ($i = 0; $i < 7; $i++) {
            //显示页面中的日期
            $day[$i]['day'] = date("m-d", $tmp[$i]);
            //显示页面中的星期
            $day[$i]['week'] = "星期" . $weekarray[date("w", $tmp[$i])];
            //这一天的开始时间戳
            $day[$i]['starttime'] = strtotime(date("Ymd", $tmp[$i]));
            //这一天的终止时间戳
            $day[$i]['endtime'] = strtotime(date("Ymd", $tmp[$i]) + 1);
        }
        //v($day);
        $this->assign("day", $day);
        //p($day);
		//年
		$this->year=date("Y",time());
		//月日
		$this->month_day=date("m-d",time());
		
        $this->assign("shijian", time());
		$this->title="拍卖预告_拍卖预展_中国艺术网在线竞拍";
		$this->keywords="拍卖预告,拍卖预展,";
		$this->desc="中国艺术网在线竞拍拍卖预告频道为藏家提供藏品、古玩、古董、玉器、书画等最新的拍卖会安排，拍卖时间，和各项拍卖预展专场活动，藏家可以及时了解最新拍卖动态。";

        //分配body css
        $this->assign("css", 'auction-ol');

		$prePage = 12;
        $p = isset($_GET['p']) ? $_GET['p'] : 1;

		$goods_starttime = I("get.starttime", strtotime(date('Y-m-d')), "intval");
        $this->goods_starttime = $goods_starttime;
        //$count = I("get.count", 0, "intval");
        $goods_endtime = I("get.endtime", strtotime(date('Y-m-d'))+86400, "intval");
        $this->goods_endtime = $goods_endtime;
        /*v(date("Y/m/d H:i:s",$goods_starttime));
        v(date("Y/m/d H:i:s",$goods_endtime));*/
		$this->date_title = date('Y-m-d日',$goods_starttime);
		$this->cur_starttime = $goods_starttime;
        //goods_isshow=1
        $where['goods_isshow'] = array("eq",1);
        //goods_startime<$goods_starttime
        $where['goods_starttime'] = array("lt",$goods_starttime);
        //goods_endtime>$goods_endtime
        $where['goods_endtime'] = array("gt",$goods_endtime);
        $goods = M("PaimaiGoods")->where($where)->order('goods_starttime DESC')->limit(15)->/*page($p . ',' . $prePage)->*/select();
		//dump($goods);
        //v(M("PaimaiGoods")->getLastSql());
        //if ($goods) {
            foreach ($goods as $k => $v) {
                //根据时间判断当前状态,goods数组中也有goods_status字段可以判断状态
                if ($v['goods_endtime'] < time()) { //如果已经结束则状态为2
                    $goods[$k]['status'] = 2;
                } elseif ($v['goods_starttime'] > time()) { //如果还没开始则状态为0
                    $goods[$k]['status'] = 0;
                } else {
                    $goods[$k]['status'] = 1;
                }
                $goods[$k]['goods_name'] = substr_CN($v['goods_name'], 10);
                //$goods[$k]['goods_endtime'] = date("y-m-d H:i:s", $v['goods_endtime']);
                //$goods[$k]['goods_starttime'] = date("y-m-d H:i:s", $v['goods_starttime']);
                $goods[$k]['url'] = U("/paimai/goods-".$v["goods_id"]);
                $goods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
            }
           /* p($goods);*/
        //    echo json_encode($goods);
        //} else {
        //    echo json_encode(0);
        //    exit;
        //}
		//echo time();
		//dump($goods);
		$this->assign("lists", $goods);
		$this->total_num = M("PaimaiGoods")->where($where)->count();
		$Page = new \Think\Page($this->total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数

        //$Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('END', '尾页');

        //$Page->setConfig('theme', '%UP_PAGE% %FIRST% %LINK_PAGE% %DOWN_PAGE% %END%');
       
        $show = $Page->show(); // 分页显示输出
		//接受参数,并简单修改
		$suffix=$_SERVER['QUERY_STRING'];
		//echo $suffix."<br/>";
		//去除开头两个字符串,并替换&&为?
		$suffix=str_replace("&&","?&",substr($suffix,3));
		//echo $suffix."<br/>";
		//去除分页 p=3中的数字
		$suffix=preg_replace("/&p=(\d+)*/","",$suffix);
		
		$show=preg_replace("/(.*)Paimai\/FrontNotice\/index(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);
		$this->assign('page', $show); // 赋值分页输出*/

        //读取分类
        $cate_where = array(
            'cat_pid' => array('NEQ', '0'),
            'cat_isshow' => '1',
            );
        $this->cate = M('paimai_category')->field('cat_id,cat_name,cat_spell')->where($cate_where)->select();
        $this->display("Front/noticetest");
		
    }

#ajax区域
    public function getgoodsbytime()
    {
        if(!IS_AJAX)$this->error("你请求的页面不存在");

        $goods_starttime = I("get.starttime", 0, "intval");
        $count = I("get.count", 0, "intval");
        $goods_endtime = I("get.endtime", 0, "intval");
        /*v(date("Y/m/d H:i:s",$goods_starttime));
        v(date("Y/m/d H:i:s",$goods_endtime));*/
        //开拍预售的数据
        if(I('get.presole')){
            $where['goods_isshow'] = array("EQ",1);
            $where['goods_starttime'] = array("GT",$goods_starttime);
            $where['goods_endtime'] = array("LT",$goods_starttime);
            $goods = M("PaimaiGoods") ->where($where)->limit($count)->select();
            //如果调用数据为空，走原来的流程
            if(!$goods){
                //goods_isshow=1
                $where['goods_isshow'] = array("EQ",1);
                //goods_startime<$goods_starttime
                $where['goods_starttime'] = array("LT",$goods_starttime);
                //goods_endtime>$goods_endtime
                $where['goods_endtime'] = array("GT",$goods_endtime);
                $goods = M("PaimaiGoods") ->where($where)->limit($count)->select();
                //v(M("PaimaiGoods")->getLastSql());
            }
        } else {
            //goods_isshow=1
            $where['goods_isshow'] = array("EQ",1);
            //goods_startime<$goods_starttime
            $where['goods_starttime'] = array("LT",$goods_starttime);
            //goods_endtime>$goods_endtime
            $where['goods_endtime'] = array("GT",$goods_endtime);
            $goods = M("PaimaiGoods") ->where($where)->limit($count)->select();
            //v(M("PaimaiGoods")->getLastSql());

        }
        
        if ($goods) {
            foreach ($goods as $k => $v) {
                //根据时间判断当前状态,goods数组中也有goods_status字段可以判断状态
                if ($v['goods_endtime'] < time()) { //如果已经结束则状态为2
                    $goods[$k]['status'] = 2;
                } elseif ($v['goods_starttime'] > time()) { //如果还没开始则状态为0
                    $goods[$k]['status'] = 0;
                } else {
                    $goods[$k]['status'] = 1;
                }
                $goods[$k]['goods_name'] = substr_CN($v['goods_name'], 10);
                $goods[$k]['goods_endtime'] = date("y-m-d H:i:s", $v['goods_endtime']);
                $goods[$k]['goods_starttime'] = date("y-m-d H:i:s", $v['goods_starttime']);
                $goods[$k]['url'] = U("/paimai/goods-".$v["goods_id"]);
                $goods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
            }
           /* p($goods);*/
            echo json_encode($goods);
        } else {
            echo json_encode(0);
            exit;
        }
    }

    #ajax区域
    public function getgoodsbytimes()
    {
        if(!IS_AJAX)$this->error("你请求的页面不存在");
        $goods_starttime = I("get.starttime", 0, "intval");
        $count = I("get.count", 0, "intval");
        //获取当前页码
        $page = I("get.page", 0, "intval");
        $goods_endtime = I("get.endtime", 0, "intval");
        /*v(date("Y/m/d H:i:s",$goods_starttime));
        v(date("Y/m/d H:i:s",$goods_endtime));*/

        

        $where['goods_isshow'] = array("EQ",1);
        //goods_startime<$goods_starttime
        $where['goods_starttime'] = array("GT",$goods_starttime);
        //goods_endtime>$goods_endtime
        $where['goods_endtime'] = array("LT",$goods_endtime);


      

        $goods_num = M("PaimaiGoods")->where($where)->count();
        //总页数
        $page_num = ceil($goods_num/$count);
        //判断是否是最后一页
        if($page == $page_num){
            $page_num = 'last';
        }
        //判断是否是第一页
        if($page == 1){
            $page_num = 'first';
        }
        //echo  $page_num;die;
        $goods = M("PaimaiGoods") ->where($where)->limit(($page-1)*$count, $count)->select();
        //v(M("PaimaiGoods")->getLastSql());

        if(!$goods){
              //goods_isshow=1
                $where['goods_isshow'] = array("EQ",1);
                //goods_startime<$goods_starttime
                $where['goods_starttime'] = array("LT",$goods_starttime);
                //goods_endtime>$goods_endtime
                $where['goods_endtime'] = array("GT",$goods_endtime);
                
                $goods_num = M("PaimaiGoods")->where($where)->count();
                //总页数
                $page_num = ceil($goods_num/$count);
                //判断是否是最后一页
                if($page == $page_num){
                    $page_num = 'last';
                }
                //判断是否是第一页
                if($page == 1){
                    $page_num = 'first';
                }
                //echo  $page_num;die;
                $goods = M("PaimaiGoods") ->where($where)->limit(($page-1)*$count, $count)->select();
        }

        if ($goods) {
            foreach ($goods as $k => $v) {
                //根据时间判断当前状态,goods数组中也有goods_status字段可以判断状态
                if ($v['goods_endtime'] < time()) { //如果已经结束则状态为2
                    $goods[$k]['status'] = 2;
                } elseif ($v['goods_starttime'] > time()) { //如果还没开始则状态为0
                    $goods[$k]['status'] = 0;
                } else {
                    $goods[$k]['status'] = 1;
                }
                $goods[$k]['goods_name'] = substr_CN($v['goods_name'], 10);
                $goods[$k]['goods_endtime'] = date("y-m-d H:i:s", $v['goods_endtime']);
                $goods[$k]['goods_starttime'] = date("y-m-d H:i:s", $v['goods_starttime']);
                $goods[$k]['url'] = U("/paimai/goods-".$v["goods_id"]);
                $goods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
            }
           /* p($goods);*/
            $this->ajaxReturn(array('page_num' => $page_num, 'goods' => $goods), 'json');
            //echo json_encode($goods);
        } else {
            echo json_encode(0);
            exit;
        }
    }

    //异步加载更多
    public function ajax_loadmore() {
        if(!IS_AJAX) echo error;
        $p = I('post.page', 0, 'intval');
        $num = I('post.number', 0, 'intval');

        $goods_starttime = I('post.a_starttime', 0, 'intval');
        $goods_endtime = I('post.a_endtime',0 , 'intval');
        //查询条件
        $where['goods_isshow'] = array("eq",1);
        $where['goods_starttime'] = array("lt",$goods_starttime);
        $where['goods_endtime'] = array("gt", $goods_endtime);
        //查询条数
        $goods_limit=$p*$num.", ".$num;

        $goods = M("PaimaiGoods")->where($where)->order('goods_starttime DESC')->limit($goods_limit)->select();
        if(empty($goods)){
            $this->ajaxReturn(array('status'=>0, 'msg' => '002'), 'json');
            exit;
        }
        foreach ($goods as $k => $v) {
            $goods[$k]['goods_endtime_format'] = date('Y-m-d H:i:s', $v['goods_endtime']);
            $goods[$k]['goods_name'] = substr_CN($v['goods_name'], 10);
            $goods[$k]['url'] = U("/paimai/goods-".$v["goods_id"]);
            $goods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
        }


        if($goods){
            $this->ajaxReturn(array('status'=>1, 'goods'=>$goods),'JSON');
        } else {
            $this->ajaxRturn(array('status'=>0),'JSON');
        }

    }

} 