<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;

use Home\Controller\HomeController;

class FrontAgencyController extends HomeController
{

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->Model = M('Auction_agency');
	$this->assign('title', '中国艺术网-拍卖');

    }

    /**
     * 列表信息
     */
    public function index()
    {


        // 重置分页条数
        C('PAGE_NUMS', 10);

        //拍卖机构总数目
        $this->total_num = $this->Model->where('status = 1')->field('id')->count();

        //获取机构区域列表
        $this->areaList = M('Auction_area')->field('id, name')->select();

        /*
         * 根据地区筛选
         */
        $id = I('get.id', '', 'int');
        if ($id) {
            $con['areaid'] = I('get.id', '', 'int');
        }
        $con['status'] = 1;
        //26个英文字母
        $letter1 = abc();
        $this->letter = $letter1;

        foreach ($letter1 as $k => $v) {
            $con['pinyin'] = $v['k'];
            $letter1[$k]['pp'] = M('Auction_agency')->where($con)->select();
        }

        //将数据列表传递到前台
        $this->list = $letter1;
        //分配body css
        $this->assign("css", 'selling-panel auction-org');
        $this->display('Front:agency');
    }

    /*
     *机构介绍
     */
    public function agency_intro()
    {
        $this->agencyid = I('get.id', '', 'int');
        //根据ID获取该机构信息
        $this->agency_res = $this->Model->where(array('id' => I('get.id', '', 'int')))->find();

        /*
         * 征集信息
         */
        $this->collect_res = M('AuctionCollect')->where(array('agencyid' => I('get.id', '', 'int')))->order('createtime desc')->limit(1)->select();

        /*
         * 近期拍卖
         */
        //根据机构ID查询出他的拍卖会
        $time = time();
        $where = "UNIX_TIMESTAMP(endtime) < $time && pid = 0 && status = 0 && agencyid = '" . I('get.id', '', 'int') . "'";
        $this->meeting_res = M('AuctionMeeting')->where($where)->order('endtime desc')->limit(1)->select();

        /*
         *拍卖结果-----------------------获取所有专场信息
         */
        $this->special_list = M('AuctionMeeting')->field('id, name,areaid, agencyid, money, address, starttime')->where("agencyid = '" . I('get.id', '', 'int') . "' and pid > 0 and UNIX_TIMESTAMP(endtime) < $time")->limit(6)->order('id desc')->select();
        //p($this->special_list);


        /*
         *拍品欣赏
         */
        $this->auction_data = M('AuctionExhibit')->field('id, name, thumb, agencyid, sn, price, endprice, starttime')->where(array('agencyid' => I('get.id', '', 'int')))->limit(10)->order('starttime desc')->select();

        //最近更新的拍卖预展
        $time = time();
        $this->goods_preview = M('AuctionMeeting')->field('id ,name,pre_address, pre_starttime, pre_endtime')->where("UNIX_TIMESTAMP(starttime) > $time && pid = 0 && status = 0")->order('pre_starttime desc')->limit(7)->select();

        //最近更新的拍卖结果
        $this->goods_res = M('AuctionMeeting')->field('id ,name, agencyid, address')->where("UNIX_TIMESTAMP(endtime) <= $time && status = 0 && pid = 0")->order('hits desc')->limit(8)->select();

        //相关拍品速览
        $this->goods_list = M('AuctionExhibit')->field('id, thumb, name, category, price, agencyid')->limit(5)->order('id desc')->select();
        //分配body css
        $this->assign("css", 'selling-panel auction-org-business');
        $this->display('Front:agency_intro');
    }

    /*
      * 机构首页机构搜索结果
      */
    public function agency_list()
    {
        //用户搜索的内容
        $this->res = I('get.keyword');

        //搜索的结果
        C('PAGE_NUMS', 210);
        $con = "name like '%" . mysql_real_escape_string(I('get.keyword')) . "%'";
        $this->num = $this->Model->where($con)->count();
        $this->list = $this->Model->field('id, name')->where($con)->order('id desc')->select();

        $this->display('Front:agency_list');
    }

    /*
     * 展品详细页
     */
    public function goods_detail()
    {
        $gid = I('get.id', 0, 'int');

        if (I('get.sn')) {
            $res = M('AuctionExhibit')->where('isshow = 1 && sn = "' . mysql_real_escape_string(I('get.sn')) . '"')->select();
            if ($res) {
                $gid = $res[0]['id'];
            }
        }

        $data = M('AuctionExhibit')->where('isshow = 1')->find($gid);
        empty($data) AND $this->error('此信息不存在或者已被删除！');

        $data['hits'] = array('exp', 'hits+1'); // 拍品点击量+1
        M('AuctionExhibit')->where(array('id' => $gid))->save($data); // 根据条件保存修改的数据

        //获取该拍品信息
        $this->list = $data;

        //获取该艺术家的拍卖纪录
        $time = time();
        $this->history_res = M('AuctionExhibit')->field('id, thumb, name, sn, price, endprice, agencyid, starttime')->where("UNIX_TIMESTAMP(endtime) < $time && author = '" . $data['author'] . "'")->limit(6)->select();

#跨库
        // 人物动态
        $this->artist_news = M(C('PHPCMS_DB') . '.news', 'v9_')->field('id, title, inputtime, url')->where(array('disable' => 1, 'catid' => 42))->limit(10)->order('id DESC')->select();

        //最近更新的拍卖预展
        $time = time();
        $this->yz_new = M('AuctionMeeting')->field('id ,name,pre_address, pre_starttime, pre_endtime')->where("UNIX_TIMESTAMP(starttime) > $time && pid = 0 && status = 0")->order('pre_starttime desc')->limit(7)->select();

        //最近更新的拍卖结果
        $this->jg_new = M('AuctionMeeting')->field('id ,name, agencyid, address')->where("UNIX_TIMESTAMP(endtime) <= $time && status = 0 && pid = 0")->order('hits desc')->limit(8)->select();

        //相关拍品速览
        $this->goods_list = M('AuctionExhibit')->field('id, thumb, name, category, price, agencyid')->where('isshow = 1')->limit(5)->order('id desc')->select();

        //最上方图片相册
        $goods = M('AuctionExhibit')->field('id, thumb, name, author, sn, endprice')->where('isshow = 1')->order('id desc')->select();

        if ($goods[0]['id'] == $gid) {
            $this->all_goods = $goods;
        } else {
            $newGoods = array();
            foreach ($goods as $g) {
                if ($g['id'] == $gid) {
                    $newGoods[0] = $g;
                }
            }
            foreach ($goods as $g) {
                if ($g['id'] != $gid) {
                    $newGoods[] = $g;
                }
            }
            $this->all_goods = $newGoods;
        }

        //dump($gid);
        //dump($this->all_goods);
        //分配body css
        $this->assign("css", 'selling-panel selling-preview-pro-detail');
        $this->display('Front:goods_detail');
    }

    /*
     * 艺术家详细页
     */
    public function artist_detail()
    {
        //获取艺术家ID，根据ID获取该艺术家信息
        $uid = I('get.id', '', 'int');

        //接收前台人物搜索
        $uname = I('get.uname');
        //如果前台搜索某艺术家，那么将根据该艺术家的ID查询信息
        if ($uname) {
            //根据姓名查询艺术家ID
            $artist = M('Artist')->field('id')->where(array('name' => $uname, 'disable' => 1))->find();
            if ($artist) {
                $uid = $artist['id'];
            } else {
                $this->error('该艺术家不存在，或已被删除！');
            }

        }

        $this->artist_msg = M('Artist')->field('id, name, thumb, goodat, jointime')->find($uid);

        //根据该艺术家ID，获取该艺术家的三个作品
        $this->artist_works = M('ArtistWorks')->field('id, name, thumb')->where(array('artistid' => $uid))->limit(3)->order('id desc')->select();

        //佳作推荐
        $this->work_recommend = D('ArtistWorks')->field(array('id', 'name', 'thumb', 'creation', 'material', 'size', 'artistid'))->where(array('disable' => 1))->order('hits DESC')->limit(1)->select();

        //精英艺术家
        $this->elite_artist = M('Artist')->field('id, name, thumb, goodat, hits')->where('type = 1 && disable = 1')->limit(6)->select();

        //品牌画廊
        $this->gallery_list = M('Gallery')->field('id, name')->where('disable = 1')->limit(10)->order('hits desc')->select();

        //该艺术家全部展品数目
        $this->all_num = M('AuctionExhibit')->where("isshow = 1 && author = '" . $uid . "'")->count();

        //该艺术家预展中展品数目
        $time = time();
        $this->preview_num = M('AuctionExhibit')->where("UNIX_TIMESTAMP(starttime) > $time && isshow = 1 && author = '" . $uid . "'")->count();

        //该艺术家已成交的展品数目
        $this->result_num = M('AuctionExhibit')->where("UNIX_TIMESTAMP(endtime) <= $time && isshow = 1 && author = '" . $uid . "' && endprice > 0")->count();

        //该艺术家已成交的展品的总成交额
        $this->total_money = M('AuctionExhibit')->where("UNIX_TIMESTAMP(endtime) <= $time && isshow = 1 && author = '" . $uid . "' && endprice > 0")->sum('endprice');

        //获取此艺术家全部展品
        C('PAGE_NUMS', 10); // 重置分页条数
        $this->Model = M('AuctionExhibit');
        //筛选条件
        $condition = "isshow = 1 && author = '" . $uid . "'";

        //获取前台传递的状态
        if (I('get.status')) {
            if (I('get.status') == 'yz') {
                $condition = "UNIX_TIMESTAMP(starttime) > $time && isshow = 1 && author = '" . $uid . "'";
            } elseif (I('get.status') == 'js') {
                $condition = "UNIX_TIMESTAMP(endtime) <= $time && isshow = 1 && author = '" . $uid . "'&& endprice > 0";
            } else {
                $this->error('参数有误...');
            }

        }
        //排序条件
        $order = "starttime desc";

        //获取前台传递的排序方案
        if (I('get.order')) {
            if (I('get.order') == 'gj') {
                $order = "price asc";
            } elseif (I('get.order') == 'cjj') {
                $order = "endprice asc";
            } else {
                $this->error('参数有误...');
            }

        }

        $this->page = $this->pages($condition);
        $this->list_num = $this->Model->where($condition)->count();
        $this->total_exhibit = $this->Model->field('id, author, sn, endprice, name, price, agencyid, starttime')->where($condition)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order($order)->select();

        $this->display('Front:artist_detail');
    }

    public function agency_meeting()
    {
        //echo I('get.id', '', 'int');
        //获取该拍卖会信息
        $meeting = M('AuctionMeeting')->field('id, name, thumb, address, pre_starttime,pre_endtime, pre_address,starttime,endtime, agencyid, introduce')->find(I('get.id', '', 'int'));
        $this->meeting_res = $meeting;
        //dump($this->meeting_res);
        //p($this->meeting_res);

        $data['hits'] = array('exp', 'hits+1'); // 拍品点击量+1
        M('AuctionMeeting')->where(array('id' => I('get.id', 0, 'int')))->save($data); // 根据条件保存修改的数据

#无数据
        //查询该拍卖会的最新预展信息
        $time = time();
        $this->meeting_yz = M('AuctionExhibit')->field('id, pre_starttime, pre_endtime')->where("UNIX_TIMESTAMP(starttime) > $time && meetingid >= '" . mysql_real_escape_string(I('get.id', '', 'int')) . "' && isshow = 1")->order('starttime desc')->limit(1)->select();
        // p( $this->meeting_yz);//无数据
#无数据
        //查询该拍卖会的最新拍卖信息
        $this->meeting_jg = M('AuctionExhibit')->field('id, starttime, endtime')->where("UNIX_TIMESTAMP(endtime) <= $time && meetingid >= '" . mysql_real_escape_string(I('get.id', '', 'int')) . "' && isshow = 1")->order('starttime desc')->limit(1)->select();
        //p($this->meeting_jg);//无数据

        //获取该拍卖会所属机构信息
        $this->agency_res = $this->Model->field('id, name, address, thumb, tel, email, website, post')->where(array('id' => $meeting['agencyid']))->find();
        // p($this->agency_res);

        //获取该拍卖会下专场信息
        $this->list_num = M('AuctionMeeting')->where(array('pid' => I('get.id', '', 'int'), 'status' => 0))->count();
//        /p($this->list_num);
//       / $this->list = M('AuctionMeeting')->field('id, name, pid, money,pre_starttime, thumb, pre_endtime, starttime, address, endtime')->where(array('pid' => I('get.id', '', 'int'), 'status' => 0))->order('starttime desc')->select();

        $p = isset($_GET['p']) ? $_GET['p'] : 1;
        //每页显示的数量
        $prePage = 8;
        $list = M('AuctionMeeting')->field('id, name, pid, money,pre_starttime, thumb, pre_endtime, starttime, address, endtime')->where(array('pid' => I('get.id', '', 'int'), 'status' => 0))->order('starttime desc')->page($p . ',' . $prePage)->select();
        //p($list);
        $this->assign("list", $list);
        $count = M('AuctionMeeting')->where(array('pid' => I('get.id', '', 'int'), 'status' => 0))->count(); // 查询满足要求的总记录数
        $Page = new \Think\Page($count, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        //p($show);
        $this->assign('page', $show); // 赋值分页输出

        //p($this->list);

        //拍品推荐
        $this->pptj_res = M('AuctionExhibit')->field('id, name, thumb, price, endprice, areaid, starttime')->where('isshow = 1')->limit(10)->order('hits desc')->select();
        //p( $this->pptj_res);
        //分配body css
        $this->assign("css", 'selling-panel selling-preview-detail');
        $this->display('Front:agency_meeting');
    }

    public function agency_special()
    {
        //获取该专场信息
        $special = M('AuctionMeeting')->field('id, agencyid, starttime, address, name')->where(array('id' => I('get.id', '', 'int')))->find();
        $this->special_res = $special;
        //p($this->special_res);

        $data['hits'] = array('exp', 'hits+1'); // 拍品点击量+1
        M('AuctionMeeting')->where(array('id' => I('get.id', 0, 'int')))->save($data); // 根据条件保存修改的数据

        //根据专场ID获取他的机构信息
        $this->agency_res = $this->Model->field('id, name, address, post, tel, email, website')->where(array('id' => $special['agencyid']))->find();
        //p( $this->agency_res);
        /*
         * 获取该专场所有拍品信息
         */
//        /C('PAGE_NUMS', 10); // 重置分页条数
        $this->Model = M('AuctionExhibit');

        $condition = "isshow = 1 && specialid = '" . I('get.id', '', 'int') . "'";
        //$this->page = $this->pages($condition);
        $this->list_num = $this->Model->where($condition)->count();

        $order = "starttime desc";

        if (I('get.order')) {
            if (I('get.order') == 'gj') {
                $order = "price desc";
            } elseif (I('get.order') == 'rmd') {
                $order = "hits desc";
            }
        }

        //$this->list = $this->Model->field('id, name, thumb, sn, price, endprice, areaid, starttime')->where($condition)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order($order)->select();

        $p = isset($_GET['p']) ? $_GET['p'] : 1;
        //每页显示的数量
        $prePage = 10;
        $list = $this->Model->field('id, name, thumb, sn, price, endprice, areaid, starttime')->where($condition)->page($p . ',' . $prePage)->order($order)->select();
        //p($list);
        $this->assign("list", $list);
        $count = $this->Model->where($condition)->count(); // 查询满足要求的总记录数
        $Page = new \Think\Page($count, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        /*  $Page->setConfig('header','共%TOTAL_ROW%条记录');
          $Page->setConfig('prev', '上一页');
          $Page->setConfig('next', '下一页');
          $Page->setConfig('first','首页');
          $Page->setConfig('last', '尾页');*/
        $Page->setConfig('prev', " 上一页");
        $Page->setConfig('next', '下一页 ');
        $Page->setConfig('first', ' 首页');
        $Page->setConfig('last', '尾页 ');


        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        //p($show);
        $this->assign('page', $show); // 赋值分页输出
        //分配body css
        $this->assign("css", 'selling-panel selling-topic-detail');

        $this->display('Front:agency_special');
    }

    public function ajax()
    {
        $gid = I('get.gid', '', 'int');
        $data = M('AuctionExhibit')->where('isshow = 1')->find($gid);
        $data['hits'] = array('exp', 'hits+1'); // 拍品点击量+1
        M('AuctionExhibit')->where(array('id' => $gid))->save($data); // 根据条件保存修改的数据
        $data['category'] = getAuctionCategoryName($data['category']);
        $data['specialid'] = getSpecialName($data['specialid']);
        $data['meetingid'] = getMeetingName($data['meetingid']);
        $data['agencyid'] = getAgencyName($data['agencyid']);
        echo json_encode($data);
    }
}
