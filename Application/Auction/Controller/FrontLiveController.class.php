<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Home\Controller\HomeController;
class FrontLiveController extends HomeController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = M('AuctionMeeting');
	$this->assign('title', '中国艺术网-拍卖');

    }

    /**
     * 列表信息
     */
    public function index() {
        //热门直播
		$this->list = $this->Model->field('id, name, thumb, agencyid, starttime, address, hits, endtime')->limit(12)->where(array('status' => 0, 'pid' => 0))->order('hits desc')->select();
		/*
		$this->page = $this->pages($where);
        $this->list = $this->Model->field('id, name, thumb, agencyid, starttime, address, hits')->limit($this->p->firstRow . ', ' . $this->p->listRows)->where($where)->order($order)->select();
		*/
		//未开始
		$time = time();//当前时间
		$this->list2 = $this->Model->field('id, name, thumb, agencyid, starttime, address, hits')->limit(12)->where("status = 0 && pid = 0 && UNIX_TIMESTAMP(starttime) > $time")->order('starttime asc')->select();
		//已结束
		$this->list3 = $this->Model->field('id, name, thumb, agencyid, starttime, address, hits')->limit(12)->where("status = 0 && pid = 0 && UNIX_TIMESTAMP(endtime) < $time")->order('id desc')->select();
        
		//直播合作客户
		$this->client_list = M('Auction_live_client')->field('id, name, thumb, website')->limit(16)->where("status = 0")->order('id desc')->select();
		
        $this->display('Front:live');
    }
    
	public function detail() {
		//查询出该拍卖会所有结果
		$id = I('get.id', '', 'int');
        //p($id);
		if($id){
			$data['hits'] = array('exp','hits+1');// 视屏点击量+1
			$hits_res = $this->Model->where(array('id' => $id))->data($data)->save(); // 根据条件保存修改的数据					
		} else {
			$this->error('操作错误！');
		}
		$this->list = $this->Model->where(array('id' => $id))->find();
		
		C('PAGE_NUMS', 10);
		
		$this->Model = M('Comment'); // 分页(局限于实例化$this->Model)
		
		//接受表单数据
		if (isset($_POST['content']) && !empty($_POST['content'])) { echo "<script>alert(0)</script>";
			
			session_start();
			$mid = $_SESSION['mid'];//获取用户id
			//判断用户是否登录
			if(empty($mid)) {
				$this->error('请先登录...');
			}	
				
			$data = $this->Model->create();//留言信息
			//处理数据
			if(strlen($data['content']) == 0){
				$this->error('留言不能为空！请输入留言内容...');	
			} elseif(strlen(trim($data['content'])) > 300) {
				$this->error('留言不能为超过300字！');
			} else {
				$data['mid'] = $mid;
				$data['relationid'] = I('get.id', '', 'int');
				$data['content'] = $data['content'];
				$data['model'] = 'auction';
				$data['type'] = 'detail';
				$data['status'] = 1;
				$data['addtime'] = time();
				$result = $this->Model->add($data);
				if($result) {
					$this->success('评论成功');
				} else {
					$this->error('操作失败！请重试...');
				}
			}
		} else {
			//读取相关的评论
			$where = array('relationid' =>$id, 'status'=> 1, 'model' =>'auction', 'type' => 'detail');
			$this->page = $this->pages($where);
			
			$this->msg_list = $this->Model->field('id, mid, content, addtime, relationid')->where($where)->order('id DESC')->limit($this->p->firstRow . ',' . $this->p->listRows)->select();
	        if($this->msg_list) {
	        	$this->num = count($this->msg_list);
			} else {
				$this->num = 0;
			}
			$this->display('Front:detail');
		}
	}

	public function common_msg() {
		session_start();
		$mid = $_SESSION['mid'];//获取用户id
		//判断用户是否登录
		if(empty($mid)) {
			$this->error('请先登录...');
		}
		
		$id = I('get.id', '', 'int');//当前数据的ID
		$this->comment = M('Comment');//评论表名
		$this->common_msg = I('post.common_msg');//留言信息
		
		if(strlen(trim($this->common_msg)) == 0){
			$this->error('留言不能为空！请输入留言内容...');	
		} elseif(strlen(trim($this->common_msg)) > 300) {
			$this->error('留言不能为超过300字！');
		} else {
			//echo 'yes';
			$data['mid'] = $mid;
			$data['relationid'] = $id;
			$data['content'] = $this->common_msg;
			$data['model'] = 'gallery';
			$data['type'] = 'visit';
			$data['status'] = 1;
			$data['addtime'] = time();
			
			$result = $this->comment->add($data);
			if($result) {
				$this->success('评论成功');
			} else {
				$this->error('操作失败！请重试...');
			}
		}
	}
	
	public function ajax() {
		$hits = M('AuctionMeeting')->field('hits')->find(I('get.id', '', 'int'));
		echo $hits['hits'];
	}
}
