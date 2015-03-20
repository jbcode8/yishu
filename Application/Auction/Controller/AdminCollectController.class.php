<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖征集_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Admin\Controller\AdminController;

class AdminCollectController extends AdminController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionCollect');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
        //C('PAGE_NUMS', 10); // 重置分页条数
        //$this->page = $this->pages();

        // 分页列表
        //获取所有拍卖征集信息
		//$this->collect_list = $this->Model->order('id desc')->field('id, title, show, createtime, updatetime')->select();

        $p=isset($_GET['p'])?$_GET['p']:1;
        //每页显示的数量
        $prePage=10;
        $list=$this->Model->field('id, title, show, createtime, updatetime')->page($p.','.$prePage)->order('`id` DESC')->select();
        //p($list);
        $this->assign("collect_list",$list);
        $count= $this->Model->count();// 查询满足要求的总记录数
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

        $this->display('Admin/collect_index');
    }

	/**
     * 显示拍卖征集所有信息
     */
    public function show() {
        $data = $this->Model->find(I('get.id', 0, 'int'));
		empty($data) AND $this->error('此征集不存在或者已被删除！');
		$this->list = $data;
		
		$this->display('Admin:collect_show');
    }
	
	/**
     * 审核拍卖征集
     */
    public function check() {
    	//根据ID查询该征集所有信息
        $data = $this->Model->find(I('get.id', 0, 'int'));
		$audit = I('get.audit');
		empty($data) AND $this->error('此信息不存在或者已被删除！');
		if($audit == 'yes') {
			$data['show'] = 0;
			$res = $this->Model->where(array('id' => I('get.id', 0, 'int')))->data($data)->save();
			if($res){
				$this->success('审核成功！');
			} else {
				$this->error('审核出错, 请重试！');
			}
		} elseif($audit == 'no') {
			$data['show'] = 1;
			$res = $this->Model->where(array('id' => I('get.id', 0, 'int')))->data($data)->save();
			if($res){
				//dump($res);exit;
				$this->success('审核成功！');
			} else {
				$this->error('审核出错, 请重试！');
			}
		} else {
			$this->error('审核出错, 请重试！');
		}
		$this->list = $this->Model->find(I('get.id', 0, 'int'));
		$this->display('Admin:collect_show');
    }
    
	/*
	 * 添加拍卖征集
	 */
	public function add() {
    	//判断表单是否提交数据
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            //获取表单提交的数据	
            $data = $this->Model->create();
		
            if ($data) {
            	//获取征集范围名称并转为字符串
            	empty($data['range']) AND $this->error('请选择征集范围！');
				 
				$data['range'] = implode(' ', $data['range']);
				
				//添加时间
				$data['createtime'] = time();
				
			    //将结果写入数据库
                $boolean = $this->Model->add($data);
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
			
			//获取所有拍卖机构
			$this->agency_list = M('Auction_agency')->field('id, name')->select();
				
            $this->display('Admin:collect_add');
        }
    }	

	/*
	 * 编辑拍卖征集
	 */
	public function edit() {
    	//判断表单是否提交数据
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            //获取表单提交的数据	
            $data = $this->Model->create();
		
            if ($data) {
            	//获取征集范围名称并转为字符串
            	empty($data['range']) AND $this->error('请选择征集范围！');
				 
				$data['range'] = implode(' ', $data['range']);
				
				//添加时间
				$data['createtime'] = time();
				
			    //将结果写入数据库
                $boolean = $this->Model->add($data);
                if ($boolean !== false) {
                    $this->success('信息添加成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
                } else {
                    $this->error('信息添加失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }
        } else {
			// 检测URL的完整性
			$id = I('get.id', 0, 'int');
			empty($id) AND $this->error('请检查URL的完整性！');

			// 检测信息是否存在
			$data = $this->Model->find($id);
			empty($data) AND $this->error('信息不存在或者已经被删除！');
			$this->list = $data;

			//获取征集分类列表
			$this->type_list = M('Auction_category')->field('id, name')->select();
			
			//获取所有拍卖机构
			$this->agency_list = M('Auction_agency')->field('id, name')->select();
				
            $this->display('Admin:collect_edit');
        }
    }	
    /**
     * 删除征集
     */
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

?>
