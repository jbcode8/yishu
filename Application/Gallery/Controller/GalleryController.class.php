<?php

namespace Gallery\Controller;
use Home\Controller\HomeController;
use Home\Model\DocumentModel;
/**
 * 画廊资讯、画廊黄页、画廊展览、发现、访谈
 */

class GalleryController extends HomeController
{

    public function _initialize()
    {
        $auth = getLoginStatus();
        $this->assign('auth',$auth);
        $this->assign('title','中国艺术网-画廊');

    }
    /**
     * 画廊首页
     */
    public function index()
    {
//        $recomGallery = D('GalleryWorks')->getWorks(2, array('status'=>2));
//        foreach($recomGallery as $k => $v) {
//            $recomGallery[$k]['image'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
//        }
//        foreach($recomGallery as $k => $v){
//            $recomGallery[$k]['works'] = M('gallery_works')->field('id, recordid, name, gid')->where(array('gid' => $v['id'],'status'=>2,'thumb'=>1))->order('hits DESC')->limit(2)->select();
//        }
//        for($i=0,$count = count($recomGallery);$i<$count;$i++) {
//            for($j=0,$countj = count($recomGallery[$i]['works']);$j<$countj;$j++) {
//                $recomGallery[$i]['works'][$j]['image'] = D('Content/Document')->getPic($recomGallery[$i]['works'][$j]['recordid'], 'thumb');
//            }
//        }
//        if(!S('arr')) {
//            $arr= M('Region')->field('name,id')->where(array('pid'=>2))->select();
//            S('arr', $arr);
//        }
        //画廊动态
        $this->assign('newsGallery', D('GalleryNews')->getNews(4, array('status'=>array('neq',0))));
        //最新加入
        $this->assign('newGallery', D('GalleryList')->getGallery(5, array('status'=>array('neq',0))));
        //最新作品
        $this->assign('newWorks',   D('GalleryWorks')->getWorks(9, array('status'=>array('neq',0))));
        //作品推荐
        $this->assign('recomWorks',   D('GalleryWorks')->getWorks(3, array('status'=>2)));
        //展览推荐
        $this->assign('recomExhibit',   D('GalleryExhibit')->getExhibit(3, array('status'=>array('eq',2))));
        //画廊推荐
        $this->assign('recomGallery',  D('GalleryList')->getGallery(3, array('status'=>array('eq',2))));
        $this->assign('arr', S('arr'));
        $this->assign('css','gallery-index');
        $this->display();
    }

    /**
     * 画廊资讯
     */
    public function news()
    {
        //热点关注上部的艺术空间
        $this->assign('gallery_list', D('GalleryList')->getGallery(5, array('status'=>array('neq',0))));
        //推荐作品
        $this->assign('gallery_works', D('GalleryWorks')->getWorks(4, array('status'=>2)));
        //推荐大师
        $this->assign('gallery_artist', D('GalleryArtist')->getArtist(6, array('status'=>2)));
        //热门展览
        $this->assign('exhibit_hot', D('GalleryExhibit')->getExhibit(10, array('status'=>2)));
        //热门画廊
        $this->assign('gallery_hot', D('GalleryList')->getGallery(10, array('status'=>array('neq',0))));
        $this->assign('css','gallery-information');
        //资讯列表
        $page = I('get.p') ? I('get.p'):1;
        $page_num = 1;
        $list = M('GalleryNews')->where(array('status'=>1))->page($page.','.$page_num)->select();
        $count      = M('GalleryNews')->where(array('status'=>1))->count();
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
        $this->assign('lists', $list);
        $this->assign('css','gallery-index gallery-information');
        $this->display();
    }

    /**
     * 画廊黄页
     */
    public function pages()
    {
        //获取地区
       if(!S('arr')) {
           $arr= M('Region')->field('name,id')->where(array('pid'=>2))->select();
           S('arr', $arr);
       }
        $area = S('arr');
        $areaSum=0;
        foreach( S('arr') as $key=>$val){
            $area[$key]['count'] = D('GalleryList')->where(array('region_id'=>$val['id']))->count();
            $areaSum +=  $area[$key]['count'];
        }
        $keyword = I('get.keyword','','trim');
        $region_id = I('get.region_id','','intval');

        if($keyword || $region_id){
            if($keyword) {
                $map['name'] = array('like','%'.$keyword.'%');
            }
            if($region_id) {
                $map['region_id'] = $region_id;
                $cityName = getCity($region_id);
            }

            $searchList =  D('GalleryList')->where($map)->select();
            $count      =  D('GalleryList')->where($map)->count();
            foreach($searchList as $k=>$v) {
                $searchList[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            }
            $this->assign('searchList', $searchList);
            $this->assign('count', $count);
            $this->assign('cityName', $cityName);
        }else{
            //获取北京上海广东画廊
            $kindGd = D('GalleryList')->field('name,id,recordid')->where(array('region_id'=>317))->select();
            $kindSh = D('GalleryList')->field('name,id,recordid')->where(array('region_id'=>41))->select();
            $kindBj = D('GalleryList')->field('name,id,recordid')->where(array('region_id'=>3))->select();
            if($kindGd) {
                foreach($kindGd as $k=>$v) {
                    $kindGd[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
                }
            }
            if($kindSh) {
                foreach($kindSh as $k=>$v) {
                    $kindSh[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
                }
            }
            if($kindBj) {
                foreach($kindBj as $k=>$v) {
                    $kindBj[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
                }
            }
            $this->assign('kindSh', $kindSh);
            $this->assign('kindBj', $kindBj);
            $this->assign('kindGd', $kindGd);
        }
        $this->assign('gallery_list', D('GalleryList')->getGallery(5, array('status'=>2)));
        $this->assign('gallery_visit', D('GalleryVisit')->getVisit(5, array('status'=>array('neq',0))));
        $this->assign('area', $area);
        $this->assign('sum', $areaSum);
        $this->assign('css','gallery-yellow-page gallery-index');
        $this->display();

    }

    /**
     * 展览
     */
    public function exhibit()
    {
        if(!S('arr')) {
            $arr= M('Region')->field('name,id')->where(array('pid'=>2))->select();
            S('arr', $arr);
        }
        //展览条件
        $map = array('catid'=>8,'status'=>1);
        if($keyword = I('get.keyword','','trim')) {
            $map['title'] = array('like', '%'.$keyword.'%');
            $map['model'] = 4;
        }
        if($starttime = I('get.starttime','','trim')) {
            $map['starttime'] = array('gt', strtotime($starttime));
        }
        if($endtime = I('get.endtime','','trim')) {
            $map['endtime'] = array('lt', strtotime($endtime));
        }
        if($region_id = I('get.region_id','','intval')) {
            $map['provinceid'] = $region_id;
        }
        if($type = I('get.type')) {
            if($type == 'jjks') {
                $map['starttime'] = array('gt', time());
            } elseif($type == 'zzzl'){
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
        //按照时间排序
        $createtime  = I('get.createtime');
        $starttime = I('get.starttime');
        if($createtime  || $starttime){
            if($createtime && $starttime){
                $order = 'document.create_time '.$createtime.','.'yishu_document_exhibit.starttime '.$starttime;
            }else{

                if($createtime){
                    $order = 'document.create_time '.$createtime ;
                }else{
                    $order = 'yishu_document_exhibit.starttime '.$starttime;
                }
            }

        }else{
            $order = 'yishu_document_exhibit.starttime DESC';
        }


        $Document = D('Document');
        $page = I('get.p') ? I('get.p'):1;
        $tableName = 'yishu_document_exhibit';
        $list = $Document
            ->alias('document')
            ->join("$tableName ON document.id= $tableName.id")
            ->where($map)
            ->field("document.title,document.description,document.recordid,$tableName.*")
            ->order($order)
            ->page($page.',10')
            ->select();
        $count  = $Document
            ->alias('document')
            ->join("$tableName ON document.id= $tableName.id")
            ->where($map)->count();
        if($list) {
            foreach($list as $k=>$v) {
                $list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
                $list[$k]['cityName'] = getCity($v['provinceid']);
            }
        }
        $Page       = new \Think\Page($count,10);
        $show       = $Page->show();
        $type = array('jjks'=>'即将开始','zzzl'=>'正在展览','yjjs'=>'已经结束', 'jrkm'=>'今日开幕');
        $this->assign('gallery_visit', D('GalleryVisit')->getVisit(10, array('status'=>array('neq',0))));
        $this->assign('gallery_works', D('GalleryWorks')->getWorks(4, array('status'=>2)));
        $this->assign('exhibit_hot', D('GalleryExhibit')->getExhibit(10, array('status'=>2)));
        $this->assign('page',$show);
        $this->assign('lists', $list);
        $this->assign('area', S('arr'));
        $this->assign('count', $count);
        $this->assign('css','gallery-exhibition gallery-index');
        $this->assign('type', $type);
        $this->display();
    }

    /**
     * 访谈
     */
    public function visit()
    {
        //轮播图片
        $visit = D('GalleryVisit');
        $visit_list =$visit->field('id, title, brief, create_time, recordid,gid')->where(array('status'=>2))->limit(4)->order('id DESC')->select();
        foreach($visit_list as $k=>$v) {
            $visit_list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        if(I('get.order')){
            $order = I('get.order');
        }else{
            $order = 'Asc';
        }
        $page = I('get.p') ? I('get.p'):1;
        $page_num = 10;
        $list = $visit->where(array('status'=>array('neq',0)))->page($page.','.$page_num)->order("update_time $order")->select();
        if($list) {
            foreach($list as $k=>$v) {
                $list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            }
        }
        $count      = $visit->where(array('status'=>array('neq',0)))->count();
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
        $this->assign('gallery_works', D('GalleryWorks')->getWorks(4, array('status'=>2)));
        $this->assign('gallery_artist',  D('GalleryArtist')->getArtist(6, array('status'=>array('neq',0))));
        $this->assign('visit_list', $visit_list);
        $this->assign('page',$show);
        $this->assign('pages', $pages);
        $this->assign('count',$count);
        $this->assign('select',$select);
        $this->assign('lists', $list);
        $this->assign('count', $count);
        $this->assign('css','gallery-interview gallery-index');
        $this->display();
    }


}