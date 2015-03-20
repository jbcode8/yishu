<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-11
 * Time: 上午9:03
 */

namespace Paimai\Controller;

use Paimai\Controller\PaimaiPublicController;

class FrontHelpController extends PaimaiPublicController
{

    public function _initialize()
    {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
		//导航
		$this->nav="help";
        $this->assign('title', '中国艺术网-拍卖帮助');
    }

    /**
     * 帮助中心
     */
    public function index()
    {
		if(IS_GET){
			$cid = I('get.id');
			$tid = I('get.tid');
			if($cid){
				$this->news = M('paimai_news')->where(array('cid'=>$cid))->field('id,cid,title')->select();
				$this->cid = $cid;
			}else if($tid){
				$this->article = M('paimai_news')->where(array('id'=>$tid))->field('id,cid,title,keyword,dis,content')->find();
			}
		}else if(IS_AJAX){
			$title = I('post.title');
			$email = I('post.email');
			$phone = I('post.phone');
			$content = I('post.content');
			if(! $title){  
				$data['status'] = 0;
				$data['info'] = '请填写留言标题';
				$this->error($data,'JSON');
			}
			if(! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",$email)){  
				$data['status'] = 0;
				$data['info'] = '邮箱格式不正确';
				$this->ajaxReturn($data,'JSON');
			}
			if(! preg_match("/^[1]{1}+[3-9]{1}+[0-9]{9}$/",$phone)){  
				$data['status'] = 0;
				$data['info'] = '请写正确的手机';
				$this->ajaxReturn($data,'JSON');
			}
			if(! $content){  
				$data['status'] = 0;
				$data['info'] = '请填写留言内容';
				$this->ajaxReturn($data,'JSON');
			}
			$datas = array(
				'title' => $title,
				'email' => $email,
				'phone' => $phone,
				'content' => $content,
				'uid' => session('mid') ? session('mid') : 0,
				'time' => time(),
				'IP' => get_client_ip()
			);
			if(M('paimai_message')->add($datas)){
				$data['status'] = 1;
				$data['info'] = '留言成功，我们会尽快为您处理，谢谢您的支持';
				$this->ajaxReturn($data,'JSON');
			}else{
				$data['status'] = 0;
				$data['info'] = '留言失败';
				$this->ajaxReturn($data,'JSON');
			}
		}
		$cate = M('paimai_cate')->field('id,pid,name')->order('id')->limit(17)->select();
		$this->cates = menus_cate($cate);
		$map_1['id']  = array('in','14,15,32,34,35');
		$this->index_1 = M('paimai_news')->field('id,title')->where($map_1)->select();
		$map_2['id']  = array('in','12,13,16,17,18');
		$this->index_2 = M('paimai_news')->field('id,title')->where($map_2)->select();
		$map_3['id']  = array('in','6,7,8,9,10');
		$this->index_3 = M('paimai_news')->field('id,title')->where($map_3)->select();
		$map_4['id']  = array('in','24,15,36,37,38');
		$this->index_4 = M('paimai_news')->field('id,title')->where($map_4)->select();
		$map_5['id']  = array('in','39,40');
		$this->index_5 = M('paimai_news')->field('id,title')->where($map_5)->select();
		$this->display("Front/help");
	}
	
}