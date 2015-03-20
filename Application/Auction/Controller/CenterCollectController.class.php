<?php

// +----------------------------------------------------------------------
// | 拍卖模块_自动出价_[会员中心]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;

use Member\Controller\MemberController;

class CenterCollectController extends MemberController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = M('AuctionCollect');//实例化拍卖征集表
    }

    /**
     * 列表信息
     */
    public function index() {
    	C('PAGE_NUMS', 10); // 重置分页条数
		/*
		 *判断用户是否拥有机构
		 */
		$this->mem_data = M('AuctionAgency')->where(array('mid' => session('mid')))->find();
		if($this->mem_data) {
			//如果有机构，获取他的机构ID
			$agency_res = M('AuctionAgency')->field('id')->where(array('mid' => session('mid')))->find();
			
			$this->page = $this->pages(array('agencyid' => $agency_res['id']));
			
			//获取所有拍卖征集信息
			$this->collect_list = $this->Model->order('id desc')->field('id, title, createtime, updatetime')->where(array('agencyid' => $agency_res['id']))->limit($this->p->firstRow.', '.$this->p->listRows)->select();	
		}
       	
        $this->display('Center:collect_index');
    }
	
	/*
	 *新增拍卖征集 
	 */
    public function add() {
    	//判断表单是否提交数据
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            //获取表单提交的数据	
            $data = $this->Model->create();
			
            if ($data) {
            	//获取征集范围名称并转为字符串
				$data['range'] = implode(' ', $data['range']);
				
				//添加时间
				$data['createtime'] = time();
				
				//获取他的机构ID
				$agency_res = M('AuctionAgency')->field('id')->where(array('mid' => session('mid')))->find();
				$data['agencyid'] = $agency_res['id'];
				
				//将结果写入数据库
                $boolean = $this->Model->filter('strip_tags')->add($data);
                if ($boolean !== false) {
                    $this->success('信息添加成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
                } else {
                    $this->error('信息添加失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }
        } else {
			//获取征集分类列表
			$this->type_list = M('Auction_category')->field('id, name')->select();
			
            $this->display('Center:collect_apply');
        }
    }	
	 
	 //保存更新信息
	 public function update() {
		//判断表单是否提交数据
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            //获取表单提交的数据	
            $data = $this->Model->create();
            if ($data) {
            	//获取征集范围名称并转为字符串
				$data['range'] = implode(' ', $data['range']);
				
				//修改时间
				$data['updatetime'] = time();
				
				//获取他的机构ID
				$agency_res = M('AuctionAgency')->field('id')->where(array('mid' => session('mid')))->find();
				$data['agencyid'] = $agency_res['id'];
				
				//将结果更新到数据库
               	$boolean = $this->Model->where(array('id' => $data['id']))->data($data)->filter('strip_tags')->save();
                if ($boolean !== false) {
                    $this->success('信息修改成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
                } else {
                    $this->error('信息修改失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }
        } else {
			//获取将要编辑的拍卖征集信息
			$this->collect_res = $this->Model->where(array('id' => I('get.id', '', 'int')))->find();
			
			//获取征集分类列表
			$this->type_list = M('Auction_category')->field('id, name')->select();
			
            $this->display('Center:collect_update');
        }
	 }
	
	//删除征集
	public function delete() {
        // 读取信息并检测信息是否存在
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
	 
}
