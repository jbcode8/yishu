<?php

namespace Home\Controller;
/**
 * 画廊资讯、画廊黄页、画廊展览、发现、访谈
 */

class GalleryDetailController extends HomeController
{

    public function _initialize()
    {

        parent::_initialize();

    }

    /**
     * 画廊资讯
     */
   public function news()
   {
       //热点关注上部的艺术空间
       $this->assign('gallery_list', D('GalleryList')->getGallery(4, array('status'=>array('neq',0))));
       //推荐作品
       $this->assign('gallery_works', D('GalleryWorks')->getWorks(4, array('status'=>2)));
       //最新大师
       $this->assign('gallery_artist', D('GalleryArtist')->getArtist(6, array('status'=>array('neq',0))));
       //热门展览
       $this->assign('gallery_hot', D('GalleryList')->getGallery(6, array('status'=>2)));
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
        if($areaid = I('get.areaid','','intval')) {
            //获取指定地区画廊
            $kindGallery = D('GalleryList')->field('name,id,recordid')->where(array('region_id'=>$areaid))->select();
            if($kindGallery) {
                foreach($kindGallery as $k=>$v) {
                    $kindGallery[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
                }
            }
            $this->assign('kindGallery', $kindGallery);
            $this->assign('kindCount', count($kindGallery));
        } else {
            //画廊搜索
            if($keyword = I('get.keyword','','trim')) {
                $map['name'] = array('like','%'.$keyword.'%');
                if($region_id = I('get.region_id','','intval')) {
                    $map['region_id'] = $region_id;
                }
                $page = I('get.p') ? I('get.p'):1;
                $searchList =  D('GalleryList')->where($map)->page($page.',1')->select();
                $count      =  D('GalleryList')->where($map)->count();
                $Page       = new \Think\Page($count,1);
                $show       = $Page->show();
                $this->assign('page',$show);
                $this->assign('searchList', $searchList);
            } else {
                //获取北京上海画廊
                $kindSh = D('GalleryList')->field('name,id,recordid')->where(array('region_id'=>41))->select();
                $kindBj = D('GalleryList')->field('name,id,recordid')->where(array('region_id'=>3))->select();
                if($kindSh) {
                    foreach($kindSh as $k=>$v) {
                        $kindSh[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
                    }
                }
                if($kindBj) {
                    foreach($kindBj as $k=>$v) {
                        $kindBj[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
                    }
                }
                $this->assign('kindSh', $kindSh);
                $this->assign('countSh', count($kindSh));
                $this->assign('kindBj', $kindBj);
                $this->assign('countBj', count($kindBj));
            }

        }
        $this->assign('gallery_list', D('GalleryList')->getGallery(5, array('status'=>2)));
        $this->assign('gallery_visit', D('GalleryVisit')->getVisit(4, array('status'=>array('neq',0))));
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
        if($keyword = I('get.keyword','','trim')) {
            $map['title'] = array('like', '%'.$keyword.'%');
            $map['model'] = 4;
            $list = D('Document')->where($map)->select();
        }
        if($starttime = I('get.starttime','','trim')) {
            $map['starttime'] = array('gt', $starttime);
        }
        if($endtime = I('get.starttime','','trim')) {
            $map['endtime'] = array('lt', $endtime);
        }
        if($provinceid = I('get.provinceid','','intval')) {
            $map['provinceid'] = $provinceid;
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
                $map['starttime'] = array('gt',time());
                $map['starttime'] = array('lt',strtotime('-1 day'));
            }
        }
        $exhibit = M('DocumentExhibit');
        $page = I('get.p') ? I('get.p'):1;
        $list = $exhibit->where($map)->page($page.',1')->select();
        $count      = $exhibit->where($map)->count();
        $Page       = new \Think\Page($count,1);
        $show       = $Page->show();
        $this->assign('gallery_visit', D('GalleryVisit')->getVisit(4, array('status'=>array('neq',0))));
        $this->assign('gallery_works', D('GalleryWorks')->getWorks(4, array('status'=>2)));
        $this->assign('page',$show);
        $this->assign('lists', $list);
        $this->display();
    }

    /**
     * 访谈
     */
    public function visit()
    {
        //轮播图片
        $visit = D('GalleryVisit');
        $visit_list =$visit->field('id, title, brief, create_time, recordid')->limit(4)->order('id DESC')->select();
        foreach($visit_list as $k=>$v) {
            $visit_list[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        $page = I('get.p') ? I('get.p'):1;
        $list = $visit->where(array('status'=>1))->page($page.',1')->select();
        $count      = $visit->where(array('status'=>1))->count();
        $Page       = new \Think\Page($count,1);
        $show       = $Page->show();
        $this->assign('gallery_works', D('GalleryWorks')->getWorks(4, array('status'=>2)));
        $this->assign('gallery_artist',  D('GalleryArtist')->getArtist(6, array('status'=>array('neq',0))));
        $this->assign('page',$show);
        $this->assign('lists', $list);
        $this->display();
    }

    /**
     * 访谈详细
     */
    public function vDetail()
    {
        $id = I('get.id', '', 'intval'); //获取数据的ID
        $visit = D('GalleryVisit');
        $detail = $visit->where(array('id' => $id, 'status' => 1))->find();
        $detail['thumb'] = D('Document')->getPic($detail['recordid'], 'thumb');
        $this->assign('detail',$detail);
        $this->display();
    }

}