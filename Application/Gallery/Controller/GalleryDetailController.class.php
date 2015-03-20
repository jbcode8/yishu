<?php

namespace Gallery\Controller;
use Home\Controller\HomeController;
use Home\Model\DocumentModel;
/**
 * 封面页控制器
 */

class GalleryDetailController extends HomeController{

    public function _initialize() {
        $gid = I('get.gid', '', 'intval');
        $gInfo = D('GalleryList')->getGalleryInfo($gid, '*');
        empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
        $gInfo['gthumb'] = D('Content/Document')->getPic($gInfo['recordid'], 'thumb');
        $this->assign('gInfo', $gInfo);
        $this->assign('workcount', M('GalleryWorks')->where(array('gid'=>$gid))->count());
        $this->assign('exhibitcount', M('GalleryExhibit')->where(array('gid'=>$gid))->count());
        $this->assign('artistcount', M('GalleryArtist')->where(array('gid'=>$gid))->count());
        $artist = D('GalleryArtist')->getArtist(3,array('gid'=>$gInfo['id']));
        $this->assign('artist',$artist);
        $auth = getLoginStatus();
        $this->assign('auth',$auth);
    }
    
    /**
     * 画廊封面
     */
    public function gallery()
    {
        $gid = I('get.gid', '', 'intval');
        if($gid) {
            //获取画廊信息
            $page = I('get.p') ? I('get.p'):1;
            $page_num = 5;
            $gInfo = D('GalleryList')->getGalleryInfo($gid, 'id,name,region_id,address,found,keywords,desc,recordid');
            empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
            $city = M('region')->where(array('id'=>$gInfo['region_id']))->getfield('name');
            $gInfo['addressInfo'] = $city.' '.$gInfo['address'];
            $gInfo['gthumb'] = D('Content/Document')->getPic($gInfo['recordid'], 'thumb');
            $this->assign('gInfo', $gInfo);
            $this->assign('wInfo', D('GalleryWorks')->getWorks(4, array('gid'=>$gid,'status'=>array('neq', 0))));
            $this->assign('eInfo', D('GalleryExhibit')->getExhibit(4, array('gid'=>$gid,'status'=>array('neq', 0))));
            $this->assign('aInfo', D('GalleryArtist')->getArtist(5, array('gid'=>$gid,'status'=>array('neq',0))));
            $message = M('MessageContent')->where(array('aid'=>$gid,'status'=>1,'mid'=>1))->page($page.','.$page_num)->select();
            $count      = M('MessageContent')->where(array('aid'=>$gid,'status'=>1,'mid'=>1))->count();
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
            $this->assign('message', $message);
            $this->display();
        } else {
            $this->error('此画廊信息不存在或者已经被删除！');
        }
    }

    /**
     * 画廊介绍
     */
    public function gintro()
    {
        $gid = I('get.gid', '', 'intval');
        if($gid) {
            $gInfo = D('GalleryList')->getGalleryInfo($gid, 'id,name,region_id,address,found,keywords,desc,recordid');
            empty($gInfo) AND $this->error('此画廊信息不存在或者已经被删除！');
            $gInfo['gthumb'] = D('Content/Document')->getPic($gInfo['recordid'], 'thumb');
        } else {
           $this->error('此画廊信息不存在或者已经被删除！');
        }
        $this->assign('gInfo', $gInfo);
        $this->display();
    }

    /**
     * 画廊展览
     */
    public function gexhibit()
    {
        $gid = I('get.gid', '', 'intval');
        if($gid){
            $page = I('get.p') ? I('get.p'):1;
            $map = array('gid'=>$gid, 'status'=>array('neq',0));
            $exhibition = M('GalleryExhibit')->where($map)->page($page.',12')->select();
            empty($exhibition) AND $this->error('此展览信息不存在或者已经被删除！');
            foreach($exhibition as $k=>$v) {
                $exhibition[$k]['athumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            }
            $count      = M('GalleryExhibit')->where($map)->count();
            $Page       = new \Think\Page($count,12);
            $show       = $Page->show();
            $this->assign('page',$show);
            $this->assign('exhibition', $exhibition);
            $this->display();
        } else {
            $this->error('此画廊信息不存在或者已经被删除！');

        }

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
            empty($list) AND $this->error('此艺术家信息不存在或者已经被删除！');
            foreach($list as $k=>$v) {
                $list[$k]['athumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            }
            $count      = $GalleryArtist->where($map)->count();
            $Page       = new \Think\Page($count,5);
            $show       = $Page->show();
            $this->assign('page',$show);
            $this->assign('lists', $list);
            $this->display();
        } else {
            $this->error('此画廊信息不存在或者已经被删除！');
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
            $list = $GalleryWorks->where($map)->field('id,aid,name,creation,material,size,recordid,gid')->order('create_time DESC')->page($page.',6')->select();
            empty($list) AND $this->error('此画廊信息不存在或者已经被删除！');
            // 获取画廊艺术家的作品数目
            $wcount = $GalleryWorks->field('COUNT(id) AS num, aid')->where(array('gid' =>$gid))->group('aid')->select();
            foreach($list as $k=>$v) {
                $list[$k]['gthumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
                $list[$k]['aname'] = D('GalleryArtist')->getOneArtist($v['aid']);
            }
            $count      = $GalleryWorks->where($map)->count();
            $Page       = new \Think\Page($count,6);
            $show       = $Page->show();
            $this->assign('page',$show);
            $this->assign('lists', $list);
            $this->display();
        } else {
            $this->error('此画廊信息不存在或者已经被删除！');
        }
    }

    /**
     * 联系画廊
     */
    public function gcontact()
    {
       $this->display();
    }

    /**
     * 联系画廊
     */
    public function gnews()
    {
        $page = I('get.p') ? I('get.p'):1;
        $list = M('GalleryNews')->where(array('status'=>1))->page($page.',14')->select();
        $count      =  M('GalleryNews')->where(array('status'=>1))->count();
        $Page       = new \Think\Page($count,14);
        $show       = $Page->show();
        $this->assign('page',$show);
        $this->assign('lists', $list);
        $this->display();
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
        if($to == 'news'){
            // 资讯详细
            $detail = M('GalleryNews')->where(array('id' => $id))->find();
            $detail['athumb'] = D('Content/Document')->getPic($detail['recordid'], 'thumb');
            $this->assign('show', $detail);
            $this->display('news');
        }else if($to == 'exhibit'){
            // 展览详细
            $detail = M('GalleryExhibit')->where(array('id' => $id))->find();
            $detail['athumb'] = D('Content/Document')->getPic($detail['recordid'], 'thumb');
            $detail['region_name'] = D('GalleryExhibit')->getRegion($detail['region_id']);
            $this->assign('show', $detail);
            $this->display('exhibit');
        }else if($to == 'artist'){
            // 艺术家详细
            $detail = D('GalleryArtist')->where(array('id' => $id))->find();
            $detail['athumb'] = D('Content/Document')->getPic($detail['recordid'], 'thumb');
        }else if($to == 'works') {
            // 作品详细
            $detail = D('GalleryWorks')->where(array('id' => $id))->find();
            $detail['wthumb'] = D('Content/Document')->getPic($detail['recordid'], 'thumb');
            $detail['catename'] = D('GalleryCategory')->getCateName($detail['cate_id']);
            $this->assign('show', $detail);
            $this->display('works');
        }else if($to == 'visit'){
			$visit = D('GalleryVisit');
			$detail = $visit->where(array('id' => $id))->find();
			$detail['thumb'] = D('Content/Document')->getPic($detail['recordid'], 'thumb');
            $this->assign('detail', $detail);
            $this->display('visit');
		}else {
            $this->error('URL不正确！');
        }

    }

    /**
     * 留言
     */
    public function lMessage()
    {
        $auth = getLoginStatus();
        if(!$auth){
            $this->error('请先登录！');
        }
        $data['aid'] = I('post.gid', '', 'intval');
        $data['mid'] = 1;
        $data['uid'] = $auth['uid'];
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

    

}