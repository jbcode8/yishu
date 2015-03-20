<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Home\Controller\HomeController;

class FrontCollectController extends HomeController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
        $this->assign('title', '中国艺术网-拍卖');
        $this->Model = M('AuctionCollect');
    }

    /**
     * 列表信息
     */
    public function index() {

    	// 焦点图
		$nowDate = time();
		$where['spaceid'] = 95;
		$where['startdate'] = array('elt', $nowDate);
		$where['enddate'] = array('egt', $nowDate);

#跨库$User = M('user.User','other_');
//    	/$this->focus = M(C('DB_V9').'.poster', 'v9_')->where($where)->field('`setting`')->order('listorder desc')->limit(4)->select();
    	$this->focus = M()->db(1,'DB_V9')->table("v9_poster")->where($where)->field('`setting`')->order('listorder desc')->limit(4)->select();
        //p($this->focus);

#跨库		// 右下角广告
		$where['spaceid'] = 83;// 重置广告编号
    	$this->adImg = M('DB_V9.poster', 'v9_')->where($where)->field('`setting`')->order('listorder desc')->limit(4)->select();
//        /p($this->adImg);
		
		// 热门征集
        $this->hot_list= $this->Model->field('id, agencyid, endtime, range')->where(array('show' => 0))->order('hits desc')->limit(4)->select();
        //p($this->hot_list);
        //p($hot_res);exit;
		//$this->hot_list = array_chunk($hot_res, 2);
		
		// 最新征集
		$this->new_collect = $this->Model->field('id, agencyid, range, endtime')->where(array('show' => 0))->order('createtime desc')->limit(4)->select();
		
		// 拍卖分类
		$this->auctionCate = S('AuctionCategory');
		
		//分类征集
		$where['show'] = 0;
		$range = urldecode(I('get.rg', '', 'trim'));
		$this->rg = $range;
		empty($range) OR $where['range'] = array('like', "%$range%");
        $res=$this->Model->count();
		$this->list = $this->Model->field('id, title, range, endtime, agencyid')->where($where)->limit(12)->order('endtime desc')->select();

		
		//获取作品分类
		$this->works_type = M('ArtistCategory')->field('id, name')->select();
		
		//获取最新作品
		$con['disable'] = 1;
		$type = I('get.type');
		empty($type) OR $con['category'] = array("like", "%$type%");
       //p($con);disable
        //p(M('ArtistWorks')->where($con)->count());
		//$this->new_works = M('ArtistWorks')->field('id, name, artistid, thumb, createtime, size, category')->where($con)->order('createtime desc')->limit(4)->select();
		$res=M('ArtistWorks')->where("status=1")->order("id desc")->limit(4)->select();
        foreach ($res as $k => $v) {
            $res[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        //p($res);

        //p($res);
        //正则取出第一张图片的路径/Uploads/Artist/2014-01-23/52e0bd2bbed84.jpg
        $reg='/\/Uploads[\.a-z0-9A-Z-\/]+/';
        foreach($res as $k=>$v){
           $reg_res=preg_match($reg,$v['pictureurls'],$pre);
            $res[$k][pictureurls]=$pre[0];

        };
        $this->assign("new_works",$res);
       //p($res);
		// 拍卖行点击排行
		$this->meeting_top10 = M('AuctionMeeting')->field('id, hits, name')->where(array('status' => 0, 'pid' => 0))->order('hits desc')->limit(8)->select();
		
		// 品牌推荐
		//$this->works_pptj = M('AuctionMeeting')->field('id, thumb, name')->where('status = 0 && pid = 0')->order('hits desc')->limit(8)->select();
		$this->works_pptj = M('AuctionAgency')->field('id, thumb, name')->where("status = 1 && thumb<>''")->limit(8)->select();
        //p($this->works_pptj);
		// 上传量最多的类别
        //p($this->works_pptj);
		$sql = "select count(id), category from yishu_auction_exhibit group by category order by count(id) desc";
		$this->type_list = M('AuctionExhibit')->query($sql);
        //p($this->type_list);
        //分配body css
        $this->assign("css", 'selling-panel auction-collect');
        $this->display('Front:collect');
    }
	/*
	 * 征集详细页
	 */
	public function detail() { 
		//根据征集ID查询出该征集信息
		$collect_list = $this->Model->where(array('id' => I('get.id', '', 'int')))->find();
		$this->list = $collect_list;
        //p($this->list);
		$data['hits'] = array('exp','hits+1');// 拍品点击量+1
		M('AuctionCollect')->where(array('id' => I('get.id', 0, 'int')))->save($data); // 根据条件保存修改的数据
		
		//根据机构D查询出该机构信息
		$this->agency_res = M('AuctionAgency')->where(array('id' => $collect_list['agencyid']))->find();
		//p($this->agency_res);
        //分配body css
        $this->assign("css", 'selling-panel selling-more');
        $this->display('Front:collect_detail');
	}
	
	/*
	 * 搜索
	 */
	public function search() {
		//拍卖资讯
		$this->news_list = M(C('DB_V9').'.news', 'v9_')->field('id, title, thumb, description, url')->where(array('catid' => 40, 'status' => 99))->order('`listorder` DESC')->limit(5)->select();
		
		//热门拍品
		$this->hot_data = M('AuctionExhibit')->field('id, thumb, name')->order('id desc')->limit(6)->select();
		
		//拍卖预展
		$time = time();
    	$con = "UNIX_TIMESTAMP(starttime) > $time && pid = 0 && status = 0";
		$this->meeting_list = M('AuctionMeeting')->field('id, name, agencyid, starttime')->where($con)->limit(10)->order('starttime asc')->select();
		//dump($this->meeting_list);
		
		//获取所有拍品分类
		$this->category_list = M('AuctionCategory')->field('id, name')->select();
		
		//获取所有机构
		$this->agency_list = M('AuctionAgency')->where('status = 1')->field('id, name')->select();
		
		C('PAGE_NUMS', 10); // 重置分页条数
		//默认条件
		$condition = "`show` = 0";
		
		$starttime = strtotime(trim(I('get.starttime')));
		$endtime = strtotime(trim(I('get.endtime')));

		if($starttime && $endtime) {   
			$condition .=" AND UNIX_TIMESTAMP(endtime) >= $starttime && UNIX_TIMESTAMP(endtime) <= $endtime"; 
		}
		
		//获取前台传递的分类
		$type = I('get.fl');
    	if($type) {   
			//$condition .= " AND `range` like '%$type%' ";
			$condition .= " AND `range` like '%".mysql_real_escape_string($type)."%' "; 
		}
		
		//获取前台传递的机构
    	if(I('get.jg')) {   
			$condition .= " AND agencyid = '".I('get.jg')."'"; 
		}
		//dump($condition);
		//==================前台搜索框--拍品搜索开始=======================================
		if(I('get.gname')) {
			$condition = "title like '%".mysql_real_escape_string(I('get.gname'))."%'&& `show` = 0";	
		}
		//==================前台搜索框--拍品搜索结束=======================================
		
		$order = "endtime asc";
		
		if(I('get.order')) {
			if(I('get.order') == 'gzd') {
				$order = 'hits desc';
			}
		}
		
		$this->page = $this->pages($condition);
		//搜索结果数目
		$this->list_num = $this->Model->where($condition)->count();
    	
		//查询结果
    	$this->list = $this->Model->field('id, agencyid, title, endtime, range')->where($condition)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order($order)->select();
		
		$this->display('Front:collect_search');
	}
	
	/*
	 * 投递藏品
	 */
	public function delivery() {
		if (isset($_POST) && !empty($_POST)) {
			$this->Model = D('AuctionDelivery');
          	$data = $this->Model->create();
			if ($data) {
				//上传者ID
				if(!session('mid')) {
					$this->error('请先登录！');
				}
				$data['memid'] = session('mid');
				$data['createtime'] = time();
				
				//手机和电话2必选1
				if($data['phone'] == '' && $data['tel'] == '') {
					$this->error('手机和电话不能都为空！');
				}
				
				//根据名称查询该藏品是否已经添加
				$res = $this->Model->where(array('name' => $data['name']))->select();
				if($res) {
					$this->error('该藏品已经投递！');
				}
				 
                $boolean = $this->Model->add($data);
                if ($boolean !== false) {
                    $this->success('信息添加成功！');
                } else {
                    $this->error('信息添加失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }

		} else {

			if(!session('mid')) {
				$this->error('您还没有登录！请先登录！');
			}
			//获取该机构的ID
			$this->agencyid = I('get.agencyid', '', 'int');
			
			//获取所有展品分类
			$this->category_list = M('AuctionCategory')->field('id, name')->select();
			
			//获取省级名称
			$this->city_list = M('Region')->where('pid = 2')->select();
           // p($this->city_list);
			//我的送拍
			//C('PAGE_NUMS', 10); // 重置分页条数
        	$this->Model = M('AuctionDelivery');
			$where = array('memid' => session('mid'));
        	//$this->page = $this->pages($where);
			
			//$this->list = $this->Model->field('id, name, status, createtime, updatetime')->where($where)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();


            $p=isset($_GET['p'])?$_GET['p']:1;
            //每页显示的数量
            $prePage=10;
            $list=$this->Model->field('id, name, status, createtime, updatetime')->where($where)->page($p.','.$prePage)->order('`id` DESC')->select();
            //p($list);
            $this->assign("list",$list);
            $count= $this->Model->where($where)->count();// 查询满足要求的总记录数
            $Page= new \Think\Page($count,$prePage);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('header','共%TOTAL_ROW%条记录');
            $Page->setConfig('prev', '上一页');
            $Page->setConfig('next', '下一页');
            $Page->setConfig('first','首页');
            $Page->setConfig('last', '尾页');

            $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $show       = $Page->show();// 分页显示输出
            //p($show);
            $this->assign('page',$show);// 赋值分页输出

            //分配body css
            $this->assign("css", 'selling-panel myselling');
            $this->display('Front:collect_delivery');
		}
	}
	
	public function ajax() {
		if(I('get.pid', '', 'int') > 0){
			$result = M('Region')->field('id, name')->where(array('parentid' => I('get.pid', '', 'int')))->select();
			echo json_encode($result);	
		} 
	}
	
	public function upload() {
		//用户id  
		if(!session('mid')){
			$this->error('您还没有登录！请先登录！');
		}
		$uid = session('mid');
		$savePath = './statics/collect_head_image/';  //图片存储路径
		$savePicName = $uid.'-'.time();  //图片存储名称
		
		$file_src = $savePath.$savePicName."_src.jpg";
		$filename162 = $savePath.$savePicName."_162.jpg"; 
		$filename48 = $savePath.$savePicName."_48.jpg"; 
		$filename20 = $savePath.$savePicName."_20.jpg";    
		
		$src=base64_decode($_POST['pic']);
		$pic1=base64_decode($_POST['pic1']);   
		$pic2=base64_decode($_POST['pic2']);  
		$pic3=base64_decode($_POST['pic3']);  
		
		if($src) {
			file_put_contents($file_src,$src);
		}
		
		file_put_contents($filename162,$pic1);
		file_put_contents($filename48,$pic2);
		file_put_contents($filename20,$pic3);
		$rs['status'] = 1;
		$rs['picUrl'] = $savePicName;

		print json_encode($rs);		
	}
	
	/**
     * 编辑信息
     */
    public function edit() {
        if (isset($_POST) && !empty($_POST)) {
			$this->Model = D('AuctionDelivery');
          	$data = $this->Model->create();
			if ($data) {
				//上传者ID
				if(!session('mid')) {
					$this->error('请先登录！');
				}
				$data['memid'] = session('mid');
				$data['updatetime'] = time();
				
				//手机和电话2必选1
				if($data['phone'] == '' && $data['tel'] == '') {
					$this->error('手机和电话不能都为空！');
				}
				
				//根据id查询该投递信息
				$res = $this->Model->where(array('id' => I('get.id', '', 'int'),'memid' => session('mid')))->find();
				if(!$res) {
					$this->error('操作出错！');
				}
				 
                $boolean = $this->Model->where(array('id' => I('get.id', '', 'int')))->data($data)->save();
                if ($boolean !== false) {
                    $this->success('信息修改成功！');
                } else {
                    $this->error('信息修改失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }
		} else {
			if(!session('mid')) {
				$this->error('您还没有登录！请先登录！');
			}		
			//获取所有展品分类
			$this->category_list = M('AuctionCategory')->field('id, name')->select();
			
			//获取省级名称
			$this->city_list = M('Region')->where('parentid = 0')->select();

			//获取该送拍的信息
			$this->Model = M('AuctionDelivery');
			$this->list = $this->Model->find(I('get.id', '', 'int'));

			
			$this->display('Front:delivery_edit');
		}
    }
	
	/*
	 * 删除信息
	 */
	public function delete() {
        // 读取信息并检测信息是否存在
        $this->Model = M('AuctionDelivery');
        $data = $this->Model->find(I('get.id', 0, 'int'));
        if ($data) {
            $boolean = $this->Model->delete($data['id']);
            if ($boolean !== false) {
                $this->success('信息删除成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
            } else {
                $this->error('信息删除失败！');
            }
        } else {
            $this->error('此信息不存在或已经删除！');
        }
    }
    
	/*
	 *  首页企业征集和个人投递处理方法 
	 */
	 public function jump() {
		if(!session('mid')) {
			$this->error('请先登录！');
		}
			
		if(I('get.status')) {
			if(I('get.status') == 'qy') {//判断是否是企业
				$this->success('请登录会员中心操作...', U('Member/Passport/login'));
			} elseif(I('get.status') == 'gr') {//判断是否是个人
				$this->success('请稍后...', U('Auction/FrontCollect/delivery'));
			} else {
				$this->error('参数有误！');
			}
		} else {
			$this->error('参数有误！');
		}
	 }
}
