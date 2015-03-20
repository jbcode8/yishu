<?php

namespace Artist\Controller;
use Home\Controller\HomeController;

class ArtistDetailController extends HomeController{

    public function _initialize()
    {

        //parent::_initialize();
        $id = I('get.id', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        //大师详细信息
        $detail =  M('ArtistLibrary')->find($id);
        $detail['thumb'] = D('Content/Document')->getpic($detail['recordid'], 'thumb');
        $this->assign('detail', $detail);

    }

    /**
     * 大师首页
     */
    public function index()
    {
        $id = I('get.id','','intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        //大师详细信息
        $detail =  M('ArtistLibrary')->find($id);
        $detail['thumb'] = D('Content/Document')->getpic($detail['recordid'], 'thumb');

        //在线展厅
        $exhibit = M('ArtistExhibit')->where(array('artistid'=>$id))->limit(4)->select();
        foreach($exhibit as $k=>$v) {
            $exhibit[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        //相册
        $album = M('ArtistAlbum')->where(array('artistid'=>$id,'disable'=>1))->limit(8)->select()                             ;
        foreach($album as $k=>$v) {
            $album[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }

        //个人动态
        $news = M('ArtistNews')->where(array('artistid'=>$id,'disable'=>1))->field('id,title')->limit(10)->select();

        $this->assign('exhibit', $exhibit);
        $this->assign('album', $album);
        $this->assign('news', $news);
        $this->display();

    }

    /**
     * 个人简介
     */
    public function events()
    {
        $id = I('get.id', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        //大事件
        $detail =  M('ArtistEvents')->where(array('aid'=>$id))->order('eventtime DESC')->select();
        foreach($detail as $key=>$v){
            $newArray[$v['eventtime']] =
                array(
                    'title'=>$v['title'],
                    'content'=>$v['content'],
                );
        }
        $this->assign('events', $newArray);
        $this->display();
    }

    /**
     * 在线展厅
     */
    public function work()
    {
        $id = I('get.id', '', 'intval');
        $wid = I('get.wid', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        $workInfo = M('ArtistWorks')->where(array('aid'=>$id))->select();
        if(empty($wid)){
            $select_key = 1;
        }else{
            $select_key = 0;
            foreach($workInfo as $k=>$val){
                if($wid == $val['id']){
                    $select_key = $k+1;
                }
            }
        }
        foreach($workInfo as $k=>$v) {
            $workInfo[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'image')[0];
            $workInfo[$k]['imgSize'] = GetImageSize('http://www.ys.com/Uploads/Artist/2014-04-28/535e1a66bb21d.jpg');
        }

        $this->assign('workInfo', $workInfo);
        $this->assign('select_key', $select_key);

        $this->display();
    }

    /**
     * 个人画展
     */
    public function exhibit()
    {
        $id = I('get.id', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        $page = I('get.p') ? I('get.p'):1;
        $exhibit = M('ArtistExhibit');
        $list = $exhibit->where(array('artistid'=>$id))->page($page.',12')->select();
        $count      = $exhibit->where(array('artistid'=>$id))->count();
        $Page       = new \Think\Page($count,12);
        $show       = $Page->show();
        foreach($list as $k=>$v) {
            $list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        $this->assign('page', $show);
        $this->assign('list', $list);

        $this->display();
    }

    /**
     * 个人动态
     */
    public function news()
    {
        $id = I('get.id', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        $page = I('get.p') ? I('get.p'):1;
        $News = M('ArtistNews');
        $list = $News->where(array('artistid'=>$id))->page($page.',22')->select();
        $count      = $News->where(array('artistid'=>$id))->count();
        $Page       = new \Think\Page($count,22);
        $show       = $Page->show();
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 个人相册封面页
     */
    public function album()
    {
        $id = I('get.id', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        $album = M('ArtistAlbum')->where(array('artistid'=>$id,'disable'=>1))->select();
        foreach($album as $k=>$v) {
            $album[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        $this->assign('album', $album);
        $this->display();
    }

    /**
     * 个人相册列表页
     */
    public function albumList()
    {
        $id = I('get.id', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        $albumid = I('get.albumid', '', 'intval');
        if(empty($albumid)){
            $this->error('相册不存在！');
        }
        $album = M('ArtistAlbum')->where(array('id'=>$albumid,'disable'=>1))->find();
        $photo = D('Content/Document')->getPic($album['recordid'], 'image');
        $this->assign('photo', $photo);
        $this->assign('album', $album);
        if(I('get.type','','trim') == 'hdp'){
            $this->display('photoHdp');
        }else{
            $this->display();
        }

    }

    /**
     * 留言板
     */
    public function message()
    {
        $id = I('get.id', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        $message = M('MessageContent')->where(array('aid'=>$id,'status'=>1,'mid'=>4))->select();
        $this->assign('message', $message);
        $this->display();
    }



    /**
     * 获奖收藏
     */
    public function award()
    {
        $id = I('get.id', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        //奖项荣誉
        $honor = M('ArtistHonor')->where(array('artistid'=>$id))->limit(5)->select();
        foreach($honor as $k=>$v) {
            $honor[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        //获奖作品
        $award = M('ArtistAward')->where(array('artistid'=>$id))->limit(5)->select();
        foreach($award as $k=>$v) {
            $award[$k]['pics'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        //收藏记录
        $collect = M('ArtistCollect')->where(array('artistid'=>$id,'disable'=>1))->select();
        $this->assign('honor', $honor);
        $this->assign('award', $award);
        $this->assign('collect', $collect);

        $this->display();
    }

	/**
     * 访谈详细
     */
	public function details()
    {
        $id = I('get.id', '', 'intval');
        if(empty($id)){
            $this->error('大师不存在！');
        }
        //访谈详细信息
        $detail =  M('ArtistInterview')->find($id);
        $detail['thumb'] = D('Content/Document')->getpic($detail['recordid'], 'thumb');
        $this->assign('detail', $detail);
        $this->display();
    }

    /**
     * 留言
     */
    public function lMessage()
    {
        $uid = session('admin_auth.uid');
        if(empty($uid)){
            $this->error('请先登录！');
        }
        $data['aid'] = I('post.aid', '', 'intval');
        $data['mid'] = 4;
        $data['uid'] = $uid;
        $data['content'] = I('post.content','','htmlspecialchars');
        $data['create_time'] = time();
        $model = M('MessageContent');
        if( $this->checkVerity(I('post.yzm')) ){
            if ($model->add($data) !== false) {
                $this->success('留言成功！');
            } else {
                $this->error('留言失败！');
            }
        }else{
            $this->error('验证码错误！');
        }

    }

    /**
     * 验证码
     */
    public function verify(){
        $verify = new \Think\Verify;
        $verify->imageH = 25;
        $verify->imageL = 100;
        $verify->length = 4;
        $verify->fontSize = 14;
        $verify->useCurve = true;
        $verify->useNoise = false;
        $verify->entry(1);
    }

    /**
     * 检测验证码
     * @param string  $yzm
     * @return boolean 检测结果
     */
    public function checkVerity($yzm = null)
    {

        $verify = new \Think\Verify();
        return $verify->check($yzm, 1);
    }

    /**
     * 个人动态详细页
     */
    public function newsDetail()
    {
        $id = I('get.nid', '', 'intval');
        if(empty($id)){
            $this->error('信息不存在！');
        }
        $newsDetail = M('ArtistNews')->find($id);
        /* 内容分页 */
        $p = I('get.p', '', 'intval');
        $p = empty($p) ? 1 : $p;
        if(strpos ($newsDetail['content'], '[page]')) {
            $contents = array_filter(explode('[page]', $newsDetail['content'])); //按分页标记分段
            $pagenumber = count ($contents); //分页数
            $page = new \Think\Page($pagenumber, 1);
            $newsDetail['content'] = $contents[$p - 1];
            $this->assign('page', $page->show()); //页码
        }
        $this->assign('newsDetail', $newsDetail);
        $this->display();
    }

}