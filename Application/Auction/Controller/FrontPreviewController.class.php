<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖预展_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;

use Home\Controller\HomeController;

class FrontPreviewController extends HomeController
{

    /**
     * 初始化
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->Model = M('AuctionExhibit');
	$this->assign('title', '中国艺术网-拍卖');


        $time = time();
        //拍品总数
        $this->goods_num = $this->Model->where("UNIX_TIMESTAMP(starttime) > $time")->count('id');

        //拍卖会总数
        $this->meeting_num = M('AuctionMeeting')->where("UNIX_TIMESTAMP(starttime) > $time && pid = 0 && status = 0")->count('id');

        //专场总数
        $this->special_num = M('AuctionMeeting')->where("UNIX_TIMESTAMP(starttime) > $time && pid > 0")->count('id');

        //获取所有拍品分类
        $this->category_list = M('AuctionCategory')->field('id, name')->select();

        //获取所有机构
        $this->agency_list = M('AuctionAgency')->where('status = 1')->field('id, name')->select();
        //p($this->agency_list);

        //获取所有的一级城市名称
        $this->area_list = M('Region')->field('id, name')->where('pid = 2')->select();

        //p($this->area_list);
    }

    /**
     * 列表信息
     */
    public function index()
    {

        C('PAGE_NUMS', 10); // 重置分页条数
        $time = time();
        //筛选条件(默认条件)
        $condition = "UNIX_TIMESTAMP(starttime) > $time && isshow = 1";

        //获取前台传递的日期
        $starttime = trim(I('get.starttime'));
        $endtime = trim(I('get.endtime'));

        if ($starttime && $endtime) {
            $start_time = $starttime > time() ? $starttime : time();
            $condition .= " AND UNIX_TIMESTAMP(starttime) > $start_time && UNIX_TIMESTAMP(starttime) <= $endtime";
        }

        //获取前台传递的分类
        if (I('get.fl')) {
            $condition .= " AND category = '" . I('get.fl') . "'";
        }

        //获取前台传递的机构
        if (I('get.jg')) {
            $condition .= " AND agencyid = '" . I('get.jg') . "'";
        }

        //获取前台传递的城市
        if (I('get.cs')) {
            $condition .= " AND areaid = '" . I('get.cs') . "'";
        }
        //接收前台传来的无底价
        if (I('get.order')) {
            if (I('get.order') == 'wdj') {
                $condition .= " AND price = 0";
            }
        }

        //接收前台传递的价格区间
        $start_money = trim(I('get.start_money'));
        $end_money = trim(I('get.end_money'));
        if ($start_money || $end_money) {
            if ($start_money == '') {
                $condition .= " AND price <= '" . mysql_real_escape_string($end_money) . "'";
            } else if ($end_money == '') {
                $condition .= " AND price >= '" . mysql_real_escape_string($start_money) . "'";
            } else {
                $condition .= " AND price >= '" . mysql_real_escape_string($start_money) . "' && price <= '" . mysql_real_escape_string($end_money) . "'";
            }
        }

        //==================前台搜索框--拍品搜索开始=======================================
        if (I('get.gname')) {
            $condition = "name like '%" . mysql_real_escape_string(I('get.gname')) . "%'&& UNIX_TIMESTAMP(starttime) > $time && isshow = 1";
        }
        //==================前台搜索框--拍品搜索结束=======================================

        //排序条件
        $order = 'starttime desc';

        if (I('get.order')) {
            if (I('get.order') == 'gj') {
                $order = 'price desc';
            }
        }


        //echo $condition;exit;
        //$this->page = $this->pages($condition);
        //搜索结果数目
        $list_num = $this->Model->where($condition)->count();
        $this->assign("list_num", $list_num);
        /*echo $list_num;exit;*/
        //查询结果

        $p = isset($_GET['p']) ? $_GET['p'] : 1;
        //每页显示的数量
        $prePage = 10;
        $list = $this->Model->where($condition)->page($p . ',' . $prePage)->select();

        $this->assign("list", $list);
        //p($list);
        $Page = new \Think\Page($list_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        //P($Page);
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出

        $this->assign('page', $show); // 赋值分页输出
        //分配body css
        $this->assign("css", 'selling-panel selling-preview');
        $this->display('Front:preview');
    }


    //拍卖预展专场--首页
    public function special()
    {

        //C('PAGE_NUMS', 15); // 重置分页条数
        $this->Model = M('Auction_meeting');

        //筛选条件(默认条件)
        $time = time();
        $condition = "UNIX_TIMESTAMP(starttime) > $time && pid > 0 && status = 0";

        //获取前台传递的日期
        $starttime = trim(I('get.starttime'));
        $endtime = trim(I('get.endtime'));

        if ($starttime && $endtime) {
            $start_time = $starttime > $time ? $starttime : $time;
            $condition .= " AND UNIX_TIMESTAMP(starttime) > $start_time && UNIX_TIMESTAMP(starttime) <= $endtime";
        }

        //获取前台传递的机构
        if (I('get.jg')) {
            $condition .= " AND agencyid = '" . I('get.jg') . "'";
        }

        //获取前台传递的城市
        if (I('get.cs')) {
            $condition .= " AND areaid = '" . I('get.cs') . "'";
        }
        //==================前台搜索框--专场搜索开始=======================================
        if (I('get.gname')) {
            $condition = "name like '%" . mysql_real_escape_string(I('get.gname')) . "%' && UNIX_TIMESTAMP(starttime) > $time && pid > 0 && status = 0";
        }
        //==================前台搜索框--专场搜索结束=======================================

        //排序条件
        $order = 'starttime desc';

        //$this->page = $this->pages($condition);
        $this->list_num = $this->Model->where($condition)->count();
        //专场列表
        //$this->special_list = $this->Model->field('id, name, pid, agencyid, areaid, starttime')->limit($this->p->firstRow . ', ' . $this->p->listRows)->where($condition)->order($order)->select();

        $p = isset($_GET['p']) ? $_GET['p'] : 1;
        //每页显示的数量
        $prePage = 15;
        $list = $this->Model->field('id, name, pid, agencyid, areaid, starttime')->page($p . ',' . $prePage)->where($condition)->order($order)->select();
        // echo getAreaName("2");exit;
        //p($list);
        $this->assign("special_list", $list);
        //$count= $this->Model->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($this->list_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        //p($show);
        $this->assign('page', $show); // 赋值分页输出
        //分配body css
        $this->assign("css", 'selling-panel selling-preview');

        $this->display('Front:yz_special_index');
    }

    //拍卖预展拍卖会--首页
    public function meet()
    {

        //C('PAGE_NUMS', 15); // 重置分页条数
        $this->Model = M('Auction_meeting');

        //筛选条件(默认条件)
        $time = time();
        $condition = "UNIX_TIMESTAMP(starttime) > $time && pid = 0 && status = 0";

        //获取前台传递的日期
        $starttime = trim(I('get.starttime'));
        $endtime = trim(I('get.endtime'));

        if ($starttime && $endtime) {
            $start_time = $starttime > $time ? $starttime : $time;
            $condition .= " AND UNIX_TIMESTAMP(starttime) > $start_time && UNIX_TIMESTAMP(starttime) <= $endtime";
        }

        //获取前台传递的机构
        if (I('get.jg')) {
            $condition .= " AND agencyid = '" . I('get.jg') . "'";
        }

        //获取前台传递的城市
        if (I('get.cs')) {
            $condition .= " AND areaid = '" . I('get.cs') . "'";
        }

        //==================前台搜索框--拍卖会搜索开始=======================================
        if (I('get.gname')) {
            $condition = "name like '%" . mysql_real_escape_string(I('get.gname')) . "%' && UNIX_TIMESTAMP(starttime) > $time && pid = 0 && status = 0";
        }
        //==================前台搜索框--拍卖会搜索结束=======================================


        //排序条件
        $order = 'starttime desc';

        //$this->page = $this->pages($condition);
        $this->list_num = $this->Model->where($condition)->count();
        //拍卖会列表
        //$this->meeting_list = $this->Model->field('id, name, agencyid, pre_starttime, pre_endtime, areaid, starttime, endtime')->where($condition)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order($order)->select();

        $p = isset($_GET['p']) ? $_GET['p'] : 1;
        //每页显示的数量
        $prePage = 10;
        $list = $this->Model->field('id, name, agencyid, pre_starttime, pre_endtime, areaid, starttime, endtime')->where($condition)->page($p . ',' . $prePage)->order($order)->select();
//        /p($list);
        $this->assign("meeting_list", $list);
        $Page = new \Think\Page($this->list_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        //p($show);

        $this->assign('page', $show); // 赋值分页输出
        //exit('11');
        //分配body css
        $this->assign("css", 'selling-panel selling-preview');
        $this->display('Front:yz_meeting_index');
    }
}
