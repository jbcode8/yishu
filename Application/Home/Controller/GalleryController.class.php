<?php

namespace Home\Controller;
/**
 * 画廊首页、封面页控制器
 */

class GalleryController extends HomeController{

    public function _initialize() {

        parent::_initialize();

    }

    /**
     * 画廊首页
     */
    public function index()
    {
        $recomGallery = D('gallery_works')->getWorks(2, array('status'=>2));
        foreach($recomGallery as $k => $v) {
            $recomGallery[$k]['image'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        foreach($recomGallery as $k => $v){
            $recomGallery[$k]['works'] = M('gallery_works')->field('id, recordid, name, gid')->where(array('gid' => $v['id'],'status'=>2,'thumb'=>1))->order('hits DESC')->limit(2)->select();
        }
        for($i=0,$count = count($recomGallery);$i<$count;$i++) {
            for($j=0,$countj = count($recomGallery[$i]['works']);$j<$countj;$j++) {
                $recomGallery[$i]['works'][$j]['image'] = D('Document')->getPic($recomGallery[$i]['works'][$j]['recordid'], 'thumb');
            }
        }
        $this->assign('newGallery', D('gallery_list')->getGallery(5, array('status'=>array('neq',0))));
        $this->assign('newWorks',   D('gallery_works')->getWorks(8, array('status'=>array('neq',0))));
        $this->assign('recomWorks',   D('gallery_works')->getWorks(5, array('status'=>2)));
        $this->assign('recomGallery', $recomGallery);
        $this->display();
    }

    /**
     * 画廊封面
     */
    public function gallery()
    {
        $gid = I('get.gid', '', 'intval');
        if($gid) {
            //获取画廊信息
            $gInfo = D('GalleryList')->getGalleryInfo($gid, 'id,name,region_id,address,found,keywords,desc,recordid');
            empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
            $city = M('region')->where(array('id'=>$gInfo['region_id']))->getfield('name');
            $gInfo['addressInfo'] = $city.' '.$gInfo['address'];
            $gInfo['gthumb'] = D('Document')->getPic($gInfo['recordid'], 'thumb');
            $this->assign('gInfo', $gInfo);
            $this->assign('wInfo', D('GalleryWorks')->getWorks(4, array('gid'=>$gid,'status'=>array('neq', 0))));
            $this->assign('aInfo', D('GalleryArtist')->getArtist(5, array('gid'=>$gid,'status'=>array('neq',0))));
            $this->display();
        } else {
            empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
        }
    }

    /**
     * 画廊介绍
     */
    public function gintro()
    {
        $gid = I('get.gid', '', 'intval');
        if($gid) {
            $gInfo = D('GalleryList')->getGalleryInfo($gid, 'recordid,content');
            empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
        } else {
            empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
        }
        $this->assign('gInfo', $gInfo);
        $this->display();
    }

    /**
     * 画廊展览
     */
    public function gexhibit()
    {
        $this->display();
    }

    /**
     * 画廊艺术家
     */
    public function gartist()
    {
        $gid = I('get.gid', '', 'intval');
        if($gid) {
            $GalleryArtist = D('GalleryArtist');
            $page = I('get.p') ? I('get.p'):1;
            $map = array('gid'=>$gid, 'status'=>array('neq',0));
            $list = $GalleryArtist->where($map)->field('id,name,recordid')->order('create_time DESC')->page($page.',5')->select();
            foreach($list as $k=>$v) {
                $list[$k]['athumb'] = D('Document')->getPic($v['recordid'], 'thumb');
            }
            $count      = $GalleryArtist->where($map)->count();
            $Page       = new \Think\Page($count,5);
            $show       = $Page->show();
            $this->assign('page',$show);
            $this->assign('lists', $list);
            dump($list);
            $this->display();
        } else {
            empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
        }
    }

    /**
     * 画廊作品
     */
    public function gworks()
    {
        $gid = I('get.gid', '', 'intval');
        if($gid) {
            $GalleryWorks = D('GalleryWorks');
            $page = I('get.p') ? I('get.p'):1;
            $map = array('gid'=>$gid, 'status'=>array('neq',0));
            if($aid = I('get.aid','','intval')) {
                $map['aid'] = $aid;
            }
            $list = $GalleryWorks->where($map)->field('id,aid,name,creation,material,size,recordid')->order('create_time DESC')->page($page.',6')->select();
            // 获取画廊艺术家的作品数目
            $wcount = $GalleryWorks->field('COUNT(id) AS num, aid')->where(array('gid' =>$gid))->group('aid')->select();
            foreach($list as $k=>$v) {
                $list[$k]['gthumb'] = D('Document')->getPic($v['recordid'], 'thumb');
                $list[$k]['aname'] = D('GalleryArtist')->getOneArtist($v['aid']);
            }
            $count      = $GalleryWorks->where($map)->count();
            $Page       = new \Think\Page($count,6);
            $show       = $Page->show();
            $this->assign('page',$show);
            $this->assign('lists', $list);
            $this->display();
        } else {
            empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
        }
    }

    /**
     * 联系画廊
     */
    public function gcontact()
    {
        $gid = I('get.gid', '', 'intval');
        if($gid) {
            $gInfo = D('GalleryList')->getGalleryInfo($gid, 'address,phone,mail,web');
            $this->display();
        } else {
            empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
        }
    }

    /**
     * 画廊详细页
     */
    public function show()
    {
        // 页面对象
        $to = I('get.to', '', 'trim');
        empty($to) AND $this->error('URL不正确！');

        $id = I('get.id', '', 'intval');
        empty($id) AND $this->error('URL不正确！');
        // 资讯详细
        // 展览详细
        // 艺术家详细
        if($to == 'artist'){
            $show = D('GalleryArtist')->where(array('id' => $id))->find();
            $show['athumb'] = D('Document')->getPic($show['recordid'], 'thumb');
        } else if($to == 'works') {
            $show = D('GalleryWorks')->where(array('id' => $id))->find();
            $show['wthumb'] = D('Document')->getPic($show['recordid'], 'thumb');
        } else {
            $this->error('URL不正确！');
        }
        $this->assign('show', $show);
    }

}