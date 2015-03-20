<?php

namespace Artist\Controller;
use Home\Controller\HomeController;

class ArtistCategoryController extends HomeController{

    public function _initialize() {

        //parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth',$auth);
        $this->assign('title','中国艺术网-大师');

    }

    /**
     * 大师首页
     */
    public function index()
    {
        //关联模型
        /*
        $library = D("Library");
        $arr_library = $library->relation(true)->find(10);
        dump($arr_library);
        */
        $Artist = D('Library');
        $interview_list =M('ArtistInterview')->field('id,title,recordid')->where(array('status'=>1))->limit(3)->select();
        foreach($interview_list as $k=>$v) {
            $interview_list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        //独家专访
        $this->assign('interview_list', $interview_list);
        //今日新面孔
        $this->assign('library_list',  D('Library')->getLibrary(6, array('status'=>1), 'createtime DESC'));
        //本周之星
        $this->assign('week_list', D('Library')->getWeekStar(3, array('status'=>1)));
        //艺术家展览
        $exhibit = M('ArtistExhibit')->where(array('disable'=>1))->limit(4)->select();
        foreach($exhibit as $k=>$v) {
            $exhibit[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        $news_list = M('news','v9_','DB_V9')->where("catid=42 and posids=1 and thumb!=''")->limit(6)->order('inputtime DESC')->select();
        $news_list2 = M('news','v9_','DB_V9')->where(array('catid' => 43))->limit(12)->order('inputtime DESC')->select();
        $this->assign('news_list', $news_list);
        $this->assign('news_list2', $news_list2);
        $this->assign('artist_exhibit', $exhibit);
        //最新作品
        $this->assign('works_list', D('Works')->getWorks(8, array('status'=>1)));
        //画中索引
        $this->assign('gh', $Artist->getLibrary(24, array('status'=>1, 'cid'=>2), 'createtime DESC'));
        $this->assign('yh', $Artist->getLibrary(24, array('status'=>1, 'cid'=>7), 'createtime DESC'));
        $this->assign('sf', $Artist->getLibrary(24, array('status'=>1, 'cid'=>3), 'createtime DESC'));
        //$this->assign('sc', $Artist->getLibrary(20, array('status'=>1, 'cid'=>20), 'createtime DESC'));
        $this->assign('bh', $Artist->getLibrary(24, array('status'=>1, 'cid'=>11), 'createtime DESC'));
        //$this->assign('yq', $Artist->getLibrary(20, array('status'=>1, 'cid'=>22), 'createtime DESC'));
        //$this->assign('zs', $Artist->getLibrary(20, array('status'=>1, 'cid'=>23), 'createtime DESC'));
        //人物动态
        $this->assign('events_list', M('ArtistNews')->field('id,title,artistid')->where(array('disable'=>1))->limit(7)->select());
        $this->assign('css', 'master-index');
        $this->display('index');
    }

    /**
     * 艺术家库
     */
    public function artist()
    {

        $Artist = D('ArtistLibrary');
        if($keywords = I('get.keywords', '', 'trim')){
            $map['name'] = array('like',"%$keywords%");
        }
        if($zm = I('get.zm', '', 'trim')){
            $map['letter'] = $zm;
        }
        if($provinceid = I('get.provinceid', '', 'intval')){
            $map['provinceid'] = $provinceid;
        }
        if($cid = I('get.cid', '', 'intval')){
            $map['cid'] = $cid;
        }
        if(!S('arr')) {
            $arr= M('Region')->field('name,id')->where(array('pid'=>2))->select();
            S('arr', $arr);
        }
        $area = S('arr');
        $artistList = $Artist->field(array('letter', 'name', 'type', 'id' ,'recordid','birthday','provinceid','cid'))->where($map)->select();

        $artistList = newArray($artistList);
        foreach($artistList as $k=>$v){
            foreach($v as $key=>$value){
                $artistList[$k][$key]['thumb'] = D('Content/Document')->getPic($value['recordid'], 'thumb');
            }
        }

        //大师排行榜
        $this->assign('artist_list',  D('Library')->getLibrary(8, array('status'=>1), 'view DESC'));
        //名人名作
        $this->assign('events_list', D('Works')->getWorks(4, array('status'=>1)));
        //艺术家库图片
        $this->assign('pic_list', D('Library')->getLibrary(9, array('status'=>1)));
        //所有艺术家
        $this->assign('lists', $artistList);
        //热搜
        $this->assign('hot_search', D('Library')->getLibrary(5, array('status'=>1),'view DESC'));

        $this->assign('css', 'master-index master-base');
        $this->assign('count', $Artist->where(array('status'=>1))->count());
        $this->assign('area',$area);
        $this->assign('az', $this->getAz());
        $this->display();
    }

    /**
     * 艺术家空间
     */
    public function space()
    {
        $Library = D('Library');
        //精英艺术家
        $goodLibrary = $Library->getLibrary(4, array('status'=>1,'type'=>1), 'view DESC');
        foreach($goodLibrary as $k => $v){
            $goodLibrary[$k]['count'] =  M('ArtistWorks')->where(array('aid' => $v['id'],'status'=>1,'thumb'=>1))->count();
            $goodLibrary[$k]['works'] = M('ArtistWorks')->field('id, recordid, name, aid,years,size,material')->where(array('aid' => $v['id'],'status'=>1,'thumb'=>1))->order('hits DESC')->limit(4)->select();
        }

        for($i=0,$count = count($goodLibrary);$i<$count;$i++) {
            for($j=0,$countj = count($goodLibrary[$i]['works']);$j<$countj;$j++) {
                $goodLibrary[$i]['works'][$j]['image'] = D('Content/Document')->getPic($goodLibrary[$i]['works'][$j]['recordid'], 'thumb');
            }
        }
        //本周之星
        $this->assign('week_list', D('Library')->getWeekStar(6, array('status'=>1)));
        //最新入驻
        $this->assign('library_list', D('Library')->getLibrary(3, array('status'=>1), 'createtime DESC'));
        //推荐艺术家
        $this->assign('library_good', D('Library')->getLibrary(2, array('type'=>2), 'createtime DESC'));
        //推荐展览
        $this->assign('exhibit_hot', D('Gallery/GalleryExhibit')->getExhibit(4, array('status'=>2)));
        $Artist = D('Library');
        $this->assign('gh', $Artist->getLibrary(4, array('status'=>1, 'cid'=>14), 'createtime DESC'));
        $this->assign('yh', $Artist->getLibrary(4, array('status'=>1, 'cid'=>17), 'createtime DESC'));
        $this->assign('sf', $Artist->getLibrary(4, array('status'=>1, 'cid'=>19), 'createtime DESC'));
        $this->assign('bh', $Artist->getLibrary(4, array('status'=>1, 'cid'=>21), 'createtime DESC'));
        $this->assign('sc', $Artist->getLibrary(4, array('status'=>1, 'cid'=>20), 'createtime DESC'));
        $this->assign('yq', $Artist->getLibrary(4, array('status'=>1, 'cid'=>22), 'createtime DESC'));
        $this->assign('zs', $Artist->getLibrary(4, array('status'=>1, 'cid'=>23), 'createtime DESC'));
        $this->assign('css', 'master-index master-space');
        $this->assign('goodLibrary', $goodLibrary);
        $this->display();

    }

    /**
     * 艺术家作品
     */
    public function works()
    {
        $artist_category = D('Category')->getCategory();
        $works_total = 0;
        foreach($artist_category as &$val){
            $val['works_num'] = D('Works')->getWorksNumByCid($val['id']);
            $works_total += $val['works_num'];
        }
        $this->assign('works_total',$works_total);
        $this->assign('artist_category',$artist_category);

        if($cid = I('get.cid', '', 'intval')){
            $map['cid'] = $cid;
        }
        $Works = D('Works');
        $page = I('get.p') ? I('get.p'):1;
        $page_num = 12;
        $list = $Works->where($map)->page($page.','.$page_num)->select();
        $count      = $Works->where($map)->count();
        $Page       = new \Think\Page($count,$page_num);
        $show       = $Page->show();

        $pages = ceil($count/$page_num);
        $i = 1;
        $select = '';
        while ($i <= $pages) {
            if (I('get.p') == $i) {
                $select .= "<option value='" . $i . "' selected>" . $i . "</option>";
            } else {
                $select .= "<option value='" . $i . "'>" . $i . "</option>";
            }
            $i++;
        }
        $this->assign('page', $show);
        $this->assign('pages', $pages);
        $this->assign('count',$count);
        $this->assign('select',$select);


        foreach($list as $k=>&$v) {
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'image')[0];
            $artist = D('Library')->getLibrary(1,array('id'=>$v['aid']),'id desc');
            $v['artist'] = $artist[0];
        }

        $this->assign('list', $list);
        $this->assign('css', 'master-index');
        $this->display();
    }

    /**
     * 展览
     */
    public function exhibit()
    {

        //展览条件
        if($type = I('get.type','','trim')) {
            if($type == 'zzzl'){
                $map['starttime'] = array('lt', time());
                $map['endtime'] = array('gt', time());
            } elseif($type == 'yjjs') {
                $map['endtime'] = array('lt', time());
            } elseif($type == 'jrkm') {
                $today = date('Y-m-d', time());
                $todayS = $today.' 00:00:00';
                $todayE = $today.' 23:59:59';
                $map['starttime'] = array('gt',strtotime($todayS));
                $map['starttime'] = array('lt',strtotime($todayE));
            }
        }
        if($region_id = I('get.region_id','','trim')){
            $map['provinceid'] = $region_id;
        }
        //地区
        if(!S('arr')) {
            $arr= M('Region')->field('name,id')->where(array('pid'=>2))->select();
            S('arr', $arr);
        }
        $map['catid'] = 8;
        $Document = D('Document');
        $one = $Document->field('id,description')->where(array('status'=>1,'catid'=>8))->order('view Desc')->find();
        $oneList = M('DocumentExhibit')->where(array('id'=>$one['id']))->find();
        $oneList['description'] = $one['description'];
        $page = I('get.p') ? I('get.p'):1;
        $tableName = 'yishu_document_exhibit';
        $list = $Document
            ->alias('document')
            ->join("$tableName ON document.id= $tableName.id")
            ->where($map)
            ->field("document.title,document.description,document.recordid,$tableName.*")
            ->order('document.create_time')
            ->page($page.',10')
            ->select();
        $count  = $Document
            ->alias('document')
            ->join("$tableName ON document.id= $tableName.id")
            ->where($map)->count();
        if($list) {
            foreach($list as $k=>$v) {
                $list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            }
        }
        $auctionSpecialPre = D('Auction/HomeAuction')->auctionSpecial(1,4);
        $Page       = new \Think\Page($count, 10);
        $show       = $Page->show();
        $this->assign('page', $show);
        $this->assign('lists', $list);
        $this->assign('oneList', $oneList);
        $this->assign('css', 'master-index master-exhibition');
        //大师排行榜
        $this->assign('artist_list',  D('Library')->getLibrary(8, array('status'=>1), 'view DESC'));
        //佳作推荐
        $this->assign('works_list', D('Works')->getWorks(4, array('type'=>1)));
        $this->assign('area', S('arr'));
        $this->assign('auctionSpecialPre',$auctionSpecialPre);
        $this->display();
    }

    /**
     * 人物动态
     */
    public function dynamic(){
        $ArtistNews = M('ArtistNews');
        $page = I('get.p') ? I('get.p'):1;
        $page_num = 5;
        $list = $ArtistNews->where(array('disable'=>1))->order('createtime DESC')->select();
        $news_list = M('news','v9_','DB_V9')->where(array('disable' => 1,'catid' => 42))->limit($page_num)->order('inputtime DESC')->page($page.','.$page_num)->select();

        $count  = M('news','v9_','DB_V9')->where(array('disable' => 1,'catid' => 42))->order('inputtime DESC')->count();
        $Page       = new \Think\Page($count, $page_num);
        $show       = $Page->show();
        $pages = ceil($count/$page_num);
        $i = 1;
        $select = '';
        while ($i <= $pages) {
            if (I('get.p') == $i) {
                $select .= "<option value='" . $i . "' selected>" . $i . "</option>";
            } else {
                $select .= "<option value='" . $i . "'>" . $i . "</option>";
            }
            $i++;
        }

        $this->assign('page', $show);
        $this->assign('pages', $pages);
        $this->assign('count',$count);
        $this->assign('select',$select);

        $this->assign('lists', $list);
        $this->assign('news_list', $news_list);
        //大师排行榜
        $this->assign('artist_list',  D('Library')->getLibrary(8, array('status'=>1), 'view DESC'));
        //佳作推荐
        $this->assign('works_list', D('Works')->getWorks(4, array('type'=>1)));
        $this->assign('css', 'master-index');
        $this->display();

    }

    /**
     * 访谈
     */
    public function interview()
    {
        //轮播图片
        $Interview = D('Interview');
        $map['status'] = 1;
        if($cid = I('get.cid', '', 'intval')){
            $map['cid'] = $cid;
        }
        if($type = I('get.type', '', 'trim')){
            $order = "$type Desc";
        }else{
            $order = "createtime DESC";
        }
        $page = I('get.p') ? I('get.p'):1;
        $list = $Interview->where($map)->page($page.',12')->order($order)->select();

        $count      = $Interview->where($map)->count();
        $Page       = new \Think\Page($count, 12);
        $show       = $Page->show();
        foreach($list as $k=>$v) {
            $list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        //大师排行榜
        $this->assign('artist_list',  D('Library')->getLibrary(8, array('status'=>1), 'view DESC'));
        //名人名作
        $this->assign('events_list', D('Works')->getWorks(4, array('status'=>1)));
        $this->assign('page', $show);
        $this->assign('lists', $list);
        $this->assign('count', $count);
        $this->assign('category', M('ArtistCategory')->select());
        $this->assign('css', 'master-index master-exhibition master-exclusive');
        $this->display();
    }

    /**
     * 获取字母A-Z
     */
    public function getAz()
    {
        $i = 26;
        $c = 65;
        while($i>0){
            $az[] = chr($c);
            $c++;
            $i--;
        }
        return $az;
    }

}