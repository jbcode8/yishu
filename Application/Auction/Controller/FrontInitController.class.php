<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;

use Home\Controller\HomeController;
use \Think\Model;
class FrontInitController extends HomeController {

    /**
     * 初始化
     */
    public function _initialize() {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
        $this->assign('title', '中国艺术网-拍卖');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 热门拍品
        $Model=M();

      /* echo "<pre>";
        print_r($Model);exit;*/
        //$this->hotAuction = M('AuctionExhibit')->field('id, thumb, name, price,author, starttime')->where('isshow = 1')->order('hits desc')->limit(5)->select();
        $this->hotAuction = M('AuctionExhibit')->where('isshow = 1')->order('hits desc')->limit(5)->select();
        //P($this->hotAuction);

        /**
         * 最新出价
         */
        // 1. 先获取最新出价的ID
       // 备份$getAid = $Model->query('SELECT DISTINCT `aid` FROM `bsm_auction_price_record` ORDER BY `id` DESC LIMIT 5');
        $getAid = $Model->query('SELECT DISTINCT `aid` FROM `yishu_auction_price_record` ORDER BY `id` DESC LIMIT 5');
        //p($getAid);
        if(is_array($getAid) && !empty($getAid)){
            foreach($getAid as $rs){
                $aid .= ','.$rs['aid'];
            }
            $aid = trim($aid, ',');
            // 2. 再根据最新的出价信息 获取拍卖信息
           //备份 $newSql = 'SELECT D.currentprice,D.endtime,D.id,D.gid,G.name,G.thumb FROM bsm_auction_data D,bsm_auction_goods G WHERE D.gid = G.id AND D.id IN('.$aid.') ORDER BY D.id DESC LIMIT 5';
            $newSql = 'SELECT D.currentprice,D.endtime,D.id,D.gid,G.name,G.thumb FROM yishu_auction_data D,yishu_auction_goods G WHERE D.gid = G.id AND D.id IN('.$aid.') ORDER BY D.id DESC LIMIT 5';
            //p($newSql);
            $this->newAuction = $Model->query($newSql);
            //p($this->newAuction);
        }

        // 今日开拍
        $this->todayTime = time();
        //$this->todayAuction = D('AuctionData')->/*field(array('id','title','startprice'))->*/where('starttime < '.$this->todayTime)->limit(11)->select();

        //$this->todayAuction = D('AuctionData')->field("yishu_auction_goods.id as goods_id,title,startprice,yishu_auction_data.id as data_id,thumb")->join("yishu_auction_goods on yishu_auction_data.id=yishu_auction_goods.id")/*field(array('id','title','startprice'))->*/->where('starttime < '.$this->todayTime)->limit(4)->select();
        //p($this->todayAuction);
        //今天的起始时间戳和结束时间戳
        $day['starttime'] = strtotime(date("Ymd", time()));

        //这一天的终止时间戳
        $day['endtime'] = strtotime(date("Ymd", strtotime("1 day")));
        /*echo time();
        p($day);*/
        $todayfield = array(
            'goods_id',
            'goods_name' => 'title',
            'goods_startprice' => 'startprice',
            'recordid',
            'goods_starttime',
            'goods_bidtimes'
        );
        // v($day);
        $todayAuction = M('PaimaiGoods')->field($todayfield)->where('goods_starttime<' . $day['endtime'] . ' and goods_starttime>' . $day['starttime'])->limit(4)->select();
        //p($todayAuction);
        foreach ($todayAuction as $k => $v) {
            $todayAuction[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        // p($todayAuction);
        $this->assign("todayAuction", $todayAuction);


#跨库
        // 拍卖专题
       // $this->arrTopic = M('DB_V9.news', 'v9_')->field(array('id','catid','title','description','thumb','url'))->where(array('catid' => 62, 'status' => 99))->order('`listorder` DESC')->limit(4)->select();
        $this->arrTopic=M()->db(1,'DB_V9')->table("v9_news")->field(array('id','catid','title','description','thumb','url'))->order('`listorder` DESC')->limit(4)->select();

        //echo "eee";
        //$this->assign("arrTopic",$this->arrTopic);
		//p($this->arrTopic);
		// 焦点图[推荐位置]
        $this->arrFocus = M()->db(1,'DB_V9')->table("v9_position_data")->field(array('id','data'))/*->where(array('posid' => 95))*/->order('`listorder` DESC')->limit(5)->select();
//p($this->arrFocus);

		// 焦点新闻[1条带描述；6条标题列表]，注：以下信息不与焦点图重复
		$field = array('id', 'url', 'title', 'inputtime', 'description');
		$where['catid'] = 40;
		empty($haveIds) OR $where['id'] = array('NOT IN', $haveIds);
#跨库操作
		$arrNews = M()->db(1,'DB_V9')->table("v9_news")->field($field)->where($where)->order('`inputtime` DESC')->limit(7)->select();
//        /p($arrNews);
		$this->newsOne = array_shift($arrNews);
		$this->newsList = $arrNews;

        //拍卖直播
        $this->live = M('AuctionMeeting')->field('id, name, thumb, agencyid, starttime, address, hits, endtime')->limit(12)->where(array('status' => 0, 'pid' => 0))->order('starttime desc')->select();
       // p($this->live);

#跨库
        //合作机构
        $res= M(C('DB_V9').'.link','v9_')->field(array('url','name','logo'))->where("`passed` = 1 ")->limit(20)->select();
        $link01 = array();
        $link02 = array();
        foreach($res as $link){
            if($link['logo'] == ''){
                 $link01[]=$link;
            }
            else{
                $link02[]=$link;
            }
        }
        $this->link01 = $link01;
        $this->link02 = $link02;
		
		//品牌专区(其实就是拍卖会)
		//$this->works_pptj = M('AuctionMeeting')->field('id, thumb, name')->where('status = 0 && pid = 0')->order('hits desc')->limit(16)->select();
		$this->works_pptj = M('AuctionAgency')->field('id, thumb, name')->where("status = 1 && thumb<>''")->limit(24)->select();

         //p($this->works_pptj);
		//拍卖预展
		$time = time();
		$this->preview_list = M('AuctionMeeting')->field('id ,name, agencyid,pre_address, pre_starttime, pre_endtime')->where("UNIX_TIMESTAMP(starttime) > $time && pid = 0 && status = 0")->order('hits desc')->limit(5)->select();
		//p($this->preview_list);
        #拍卖征集
        $this->hot_list= M('AuctionCollect')->field('agencyid')->where(array('show' => 0))->order('hits desc')->limit(4)->select();
        //p($this->hot_list);
		//拍卖结果

		$this->result_list = M('AuctionMeeting')->field('id ,name, money, agencyid, address')->where("UNIX_TIMESTAMP(endtime) <= $time && status = 0 && pid = 0")->order('hits desc')->limit(5)->select();
		//p($this->result_list);
        //分配body css
        $this->assign("css", 'selling-panel selling-index');
        $this->display('Front:index');
    }


}
