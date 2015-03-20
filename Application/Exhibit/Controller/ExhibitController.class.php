<?php
namespace Exhibit\Controller;
use Home\Controller\HomeController;

class ExhibitController extends HomeController{

    public function _initialize() {
        //parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth',$auth);
        $this->assign('title','中国艺术网-展讯');

    }

    public function index(){
		echo 1;
    }

    public function onlineExhibit(){
        $page = I('get.p') ? I('get.p'):1;
        $page_num = 12;
        $map = array('catid'=>27,'status'=>1);
        $list = D('Content/Document')->where($map)->order('update_time desc')->page($page.','.$page_num)->select();
        foreach($list as $k=>&$v) {
            $v['url'] = D('Content/Document')->getpic($v['recordid'], 'image')[0];
        }
        $count      = D('Content/Document')->where($map)->count();
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
        $this->assign('list', $list);
        $this->assign('css', 'online-exhibition-index');
        $this->display();
    }

    public function onlineExhibitDetail(){
        $id = I('get.id') ;
        $exhibit_detail = D('Content/Document')->detailNorelation($id);
        if(!(is_array($exhibit_detail) || 1 !== $exhibit_detail['status'])){
            $this->error = '文档被禁用或已删除！';
            return false;
        }
        $exhibit_images = D('Content/Document')->getpic($exhibit_detail['recordid'], 'image');
        $this->assign('exhibit_detail', $exhibit_detail);
        $this->assign('exhibit_images', $exhibit_images);

        $map = array('catid'=>27,'status'=>1,'position'=>1);
        $recommend_list = D('Content/Document')->where($map)->order('update_time desc')->select();
        foreach($recommend_list as $k=>&$v) {
            $v['url'] = D('Content/Document')->getpic($v['recordid'], 'image')[0];
        }
        if(count($recommend_list)%4!=0){
            for($i=0;$i<(count($recommend_list)%4);$i++){
                array_pop($recommend_list);
            }
        }
        $this->assign('recommend_list', $recommend_list);
        $this->assign('css', 'online-exhibition-index');
        $this->display();
    }

    public function getHomeExhibit(){
        $time = I('post.time');
        $home_exhibit = D('Exhibit')->getHomeExhibit($time);
        echo json_encode($home_exhibit[0]);
    }
}