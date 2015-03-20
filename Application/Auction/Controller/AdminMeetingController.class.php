<?php

// +----------------------------------------------------------------------
// | 拍卖模块_自动出价_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Admin\Controller\AdminController;

class AdminMeetingController extends AdminController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionMeeting');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
        //C('PAGE_NUMS', 10); // 重置分页条数
        
        $where = array('pid' => 0);
        //$this->page = $this->pages($where);

        // 获取所有拍卖会分页列表
        //$this->list = $this->Model->where($where)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();



        $p=isset($_GET['p'])?$_GET['p']:1;
        //每页显示的数量
        $prePage=10;
        $list=$this->Model->where($where)->page($p.','.$prePage)->order('`id` DESC')->select();
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

        $this->display('Admin/meeting_index');
    }
	
	/*
	 *添加拍卖会 模板页面
	 * 
	 */
	public function meeting_add() {
		//获取所有的机构名称
		$this->Model = M('AuctionAgency');
		$this->agency_list = $this->Model->field('id, name')->select();
		
		//获取所有的一级城市名称
		$this->area_list = M('Area')->field('id, name')->where('id < 36')->select();
		
		$this->display('Admin/meeting_add');
	}
	
	//处理添加的拍卖会传递的数据
	public function madd() {
		if (isset($_POST['submit']) && !empty($_POST['submit'])) {		
			$data = $this->Model->create();
			//dump($data);exit;
			if($data){
				//判断数据库中是否有此拍卖会名称存在
				$where = array('name'=> $data['name']);	
				$check_name = $this->Model->where($where)->select();
				if($check_name) {
					$this->error('拍卖会名称已经存在！！');
				} else {
					$data['pid'] = 0;
					$data['createtime'] = time();		
				    $res = $this->Model->add($data);
					if($res) {
						$this->success('拍卖会添加成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
					} else {
						$this->error('添加失败！！请重试！！');
					}
				}
			} else {
                $this->error($this->Model->getError());
            }
				
		}
	}
	/*
	 *添加拍卖专场 模板页面
	 * 
	 */
	public function meeting_special_add() {
		//获取所有拍卖会名称
		$this->Model = M('AuctionMeeting');
		$this->meeting_list = $this->Model->where(array('pid' => 0))->field('id, name')->select();
		$this->display('Admin/meeting_special_add');
	}


	//处理添加的拍卖专场传递的数据
	public function sadd() {
		if (isset($_POST['submit']) && !empty($_POST['submit'])) {
			$data = $this->Model->create();
			if($data) {
				//判断拍卖会是否为空
				if($data['name']) {
					//判断数据库中是否有此拍卖专场名称存在
					$where = array('name' => $data['name'],'pid' => $data['pid']);	
					$check_name = $this->Model->where($where)->select();
					
					if($check_name) {
						$this->error('该拍卖会中已经存在该拍卖专场！！');
					} else {
						$starttime = strpos($data['starttime'], '00:00');
						$endtime = strpos($data['endtime'], '00:00');
						if($starttime) {
							$data['starttime'] = str_replace(' 00:00', '', $data['starttime']);
						}
						if($endtime) {
							$data['endtime'] = str_replace(' 00:00', '', $data['endtime']);
						}
						//根据拍卖会ID查询出该机构ID
						$agencyid = $this->Model->field('agencyid, areaid, address')->where(array('id' => $data['pid']))->find();
						$data['createtime'] = time();	
						$data['agencyid'] = $agencyid['agencyid'];
						$data['areaid'] = $agencyid['areaid'];
						$data['address'] = $agencyid['address'];
						$res = $this->Model->add($data);
						if($res) {
							$this->success('拍卖专场添加成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
						} else {
							$this->error('添加失败！！请重试！！');
						}
					}
					
				} else {
					$this->error('拍卖专场名称不能为空！');
				}
			} else {
                $this->error($this->Model->getError());
            }
		}
	}
		
	//显示拍卖会信息页--进入拍卖会信息编辑模板
	public function meeting_show() {
		//获取拍卖会ID	
		$id = I('get.id', 0, 'int');
		
		//获取该拍卖会信息
		$list_res = $this->Model->where(array('id' => $id))->select();
		$this->list = $list_res;

		//获取该拍卖会所属机构名称
		$this->agency_name = M('AuctionAgency')->where(array('id' => $list_res[0]['agencyid']))->field('id, name')->select();
		 
		//获取所有的机构名，id
		$this->agency_list = M('AuctionAgency')->field('id, name')->select();
		
		//获取所有的一级城市名称
		$this->area_list = M('Area')->field('id, name')->where('id < 36')->select();
		
		
		$this->display('Admin/meeting_show');
	}
	
    /**
     * 更新拍卖会状态信息
     */
    public function meeting_update() {
        // 读取信息并检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        empty($data) AND $this->error('此信息不存在或者已被删除！');
        //更新操作
        $data = $this->Model->create();
		if($data) {
			$data['pid'] = 0;
			$data['updatetime'] = time();	

			$res = $this->Model->where(array('id' => I('get.id', 0, 'int')))->data($data)->save();
			if($res) {
				$this->success('拍卖会信息更新成功！');
			} else {
				$this->error('更新失败！！请重试！！');
			}
			
		} else {
                $this->error($this->Model->getError());
        }
    }
	
	/*
	 *显示某个拍卖会下的所有专场 
	 */
	public function special_show() {
		//获取拍卖会ID	
		$id = I('get.id', 0, 'int');
		
		//获取该拍卖会下的所有专场信息
		$list_res = $this->Model->where(array('pid' => $id))->select();
		$this->list = $list_res;

		//获取该专场所属拍卖会名称
		$this->meeting_name = $this->Model->where(array('id' => $id))->field('id, name')->select();

        //p($this->meeting_name)

        //exit("22");
		 /*
		//获取所有的拍卖会名称，id
		$this->meeting_list = $this->Model->where(array('pid' => 0))->field('id, name')->select();
		*/
		$this->display('Admin/special_show');
	}
	
	/*
	 * 专场信息编辑
	 */
	public function special_edit() {
        //获取该专场信息
        $data = $this->Model->find(I('get.id', 0, 'int'));
		$this->list = $data;
		
		//获取该专场所属拍卖会名称
		$this->meeting_name = $this->Model->where(array('id' => $data['pid']))->field('id, name')->select();
		
		//获取所有的拍卖会名称，id
		$this->meeting_list = $this->Model->where(array('pid' => 0))->field('id, name')->select();
        $this->display('Admin/special_edit');
    }
	
	/**
     * 更新拍卖会-专场-状态信息
     */
    public function special_update() {
        // 读取专场信息并检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        empty($data) AND $this->error('此信息不存在或者已被删除！');
        //获取新的内容
		$data = $this->Model->create();
		if($data) {
			//判断数据库中是否有此拍卖专场名称存在
			$where = array('name' => $data['name'],'pid' => $data['pid']);	
			$check_name = $this->Model->where($where)->select();
			
			if($check_name) {
				$this->error('该拍卖会中已经存在该拍卖专场！！');
			}
			
			$agencyid = $this->Model->field('agencyid, areaid, address')->where(array('id' => $data['pid']))->find();
			$data['updatetime'] = time();
			$data['areaid'] = $agencyid['areaid'];
			$data['agencyid'] = $agencyid['agencyid'];	
			$data['address'] = $agencyid['address'];
			$res = $this->Model->where(array('id' => I('get.id', 0, 'int')))->data($data)->save();
			if($res) {
				$this->success('拍卖专场信息更新成功！');
			} else {
				$this->error('更新失败！！请重试！！');
			} 
		} else {
                $this->error($this->Model->getError());
        }
    }
    /**
     * 删除信息
     */
    public function delete() {
        // 读取信息并检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));

        if ($data) {
            $boolean = $this->Model->delete($data['id']);
            if ($boolean !== false) {
                $this->success('信息删除成功！');
            } else {
                $this->error('信息删除失败！');
            }
        } else {
            $this->error('此信息不存在或已经删除！');
        }
    }

	/**
     * 删除拍卖会前
     */
    public function _before_delete() {
        // 删除拍卖会下属专场
		$this->Model->where(array('pid' => I('get.id', 0, 'int')))->delete();
    }

}
