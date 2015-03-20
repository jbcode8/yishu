<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;

use Home\Controller\HomeController;

class FrontTopicController extends HomeController
{

    /**
     * 初始化
     */
    public function _initialize()
    {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
       $this->assign('title', '中国艺术网-拍卖');
    }

    /**
     * 列表信息
     */
    public function index()
    {
        // 今日热拍
        //$hotSql = 'SELECT D.currentprice,D.starttime,D.endtime,D.id,D.gid,G.name,G.thumb FROM yishu_auction_data D,yishu_auction_goods G WHERE D.gid = G.id ORDER BY D.id DESC LIMIT 4';
        $field = array(
            'goods_nowprice' => 'currentprice',
            'goods_starttime' => 'starttime',
            'goods_endtime' => 'endtime',
            'goods_id' => 'id',
            'goods_name' => 'name',
            'recordid'
        );
        $hotAuction = M("PaimaiGoods")->field($field)->where("goods_isshow=1")->order('id desc')->limit(4)->select();
        foreach ($hotAuction as $k => $v) {
            $hotAuction[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        $this->assign("hotAuction", $hotAuction);
        //$this->hotAuction = M()->query($hotSql);
        //p($this->hotAuction);//数据正常
        //拍卖预展
        $time = time();
        $this->exhibition = M('AuctionMeeting')->field('id ,name,pre_address, pre_starttime, pre_endtime')->where("UNIX_TIMESTAMP(starttime) > $time && pid = 0 && status = 0")->order('pre_starttime desc')->limit(7)->select();

//        p($this->exhibition);//数据正常

        //拍卖作品展示
//        /$res = M()->query("SELECT g.`name`,d.`id`,d.`endtime`,g.`thumb`,d.`startprice`,g.`size` FROM yishu_auction_data AS d LEFT JOIN yishu_auction_goods AS g ON d.gid=g.id ORDER BY d.`starttime` DESC LIMIT 4");
        //$res = M()->query("SELECT g.`name`,d.`id`,d.`endtime`,g.`thumb`,d.`startprice`,g.`size` FROM yishu_auction_data AS d LEFT JOIN yishu_auction_goods AS g ON d.gid=g.id ORDER BY d.`starttime` DESC LIMIT 4");
        $field = array(
            'goods_name' => 'name',
            'goods_id' => 'id',
            'goods_endtime' => 'endtime',
            'recordid',
            'goods_startprice' => 'startprice',
        );
        $res = M('PaimaiGoods')->field($field)->limit(4)->select();
        foreach ($res as $k => $v) {
            $res[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        //p($res);
        $this->goods01 = $res[0];
        $this->goods02 = $res[1];
        $this->goods03 = $res[2];
        $this->goods04 = $res[3];

#跨库操作
        //热点专题
        $map['catid'] = array('IN', '60,61,63,64,65');
        $map['thumb'] = array('NEQ', "");
        $this->hottheme = M()->db(1, 'DB_V9')->table("v9_news")->field(array('title', 'url', 'thumb'))-> /*where($map)->*/
        order('`inputtime` DESC')->limit(5)->select();
        //p($this->hottheme);


        //p($res);
        // 重置分页条数
        //C('PAGE_NUMS', 8);

        // 条件语句
        $where = array('catid' => 62, 'status' => 99);

        // 分頁
        // $this->page = $this->pages($where);
#跨库操作
        // 拍卖专题(跨库读取数据)
        $field = array('id', 'catid', 'title', 'description', 'thumb', 'keywords', 'inputtime', 'url');
        // $this->list = M()->db(1,'DB_V9')->table("v9_news")->field($field)->where($where)->order('`listorder` DESC')->limit($this->p->firstRow.', '.$this->p->listRows)->select();

        $p = isset($_GET['p']) ? $_GET['p'] : 1;
        //每页显示的数量
        $prePage = 6;
        $list = M()->db(1, 'DB_V9')->table("v9_news")->field($field)->page($p . ',' . $prePage)->table("v9_news")-> /*where($where)->*/
        order('`listorder` DESC')->select();

        //$list=$this->Model->field($field)->page($p.','.$prePage)->order('`id` DESC')->select();
        //p($list);
        $this->assign("list", $list);
        $count = M()->db(1, 'DB_V9')->table("v9_news")->count(); // 查询满足要求的总记录数
        $Page = new \Think\Page($count, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '');

        //$Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        //p($show);
        $this->assign('page', $show); // 赋值分页输出
        //分配body css
        $this->assign("css", 'selling-panel selling-topic');

        $this->display('Front:topic');
    }

}
