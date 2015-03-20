<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;

use Home\Controller\HomeController;

class FrontNewsController extends HomeController
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
        $this->Model = M(C('DB_V9') . '.news', 'v9_');
        //p($this->Model);
    }

    /**
     * 列表信息
     */
    public function index()
    {

        // 重置分页条数
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

        // 条件语句
        $where = array('catid' => 40, 'status' => 99);

        // 分頁
        //$this->page = $this->pages($where);

        // 鉴定资讯(跨库读取数据)

        // 今日热拍
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
        //p($this->hotAuction);

        //拍卖预展
        $time = time();
        $this->exhibition = M('AuctionMeeting')->field('id ,name,pre_address, pre_starttime, pre_endtime')->where("UNIX_TIMESTAMP(starttime) > $time && pid = 0 && status = 0")->order('pre_starttime desc')->limit(7)->select();
        //p($this->exhibition);
        #热门专题
        $map['catid'] = array('IN', '60,61,63,64,65');
        $map['thumb'] = array('NEQ', "");
        $this->hottheme = M()->db(1, 'DB_V9')->table("v9_news")->field(array('title', 'url', 'thumb'))-> /*where($map)->*/
        order('`inputtime` DESC')->limit(5)->select();
        //p($this->hottheme);
        //艺术家
        //p(M('Artist')->select());
        $this->artist = M('Artist')->field(array('id', 'name'))->where(array('disabled' => '1',))->order('`hits` DESC')->limit(6)->select();

        //p($this->artist);这个好像数据库没有数据

        //拍卖作品展示

        $field = array('id', 'url', 'title', 'description', 'thumb', 'keywords', 'inputtime');

        $prePage = 6;
        $p = isset($_GET['p']) ? $_GET['p'] : 1;
        $list = M()->db(1, 'DB_V9')->table("v9_news")->field($field)->where($where)->page($p . ',' . $prePage)->order('`listorder` DESC')->select();

        //p($this->list);
        $this->assign("list", $list);
        $count = M()->db(1, 'DB_V9')->table("v9_news")->where($where)->count(); // 查询满足要求的总记录数
        //p($count);
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

        //p($this->goods01);
        $this->display('Front:news');


    }

}
