<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Admin\Controller\AdminController;

class AdminAgencyController extends AdminController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionAgency');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
       // $this->page = $this->pages();

        // 分页列表
        $field = array('id', 'name', 'mid', 'status', 'createtime', 'updatetime');
        //$this->list = $this->Model->field($field)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();

        $p=isset($_GET['p'])?$_GET['p']:1;
        //每页显示的数量
        $prePage=10;
        $list=$this->Model->field($field)->page($p.','.$prePage)->order('`id` DESC')->select();
        //p($list);
        $this->assign("list",$list);
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


        $this->display('Admin/agency_index');
    }

	 /**
     * 添加信息
     */
    public function add() {
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            $data = $this->Model->create();
			
            if ($data) {
				// 汉字转为拼音且获取首字母
				$Cn2py = new Cn2py();
				$pinyin = $Cn2py->doit($data['name'], 'utf-8');
				$data['pinyin'] = substr($pinyin, 0, 1);
            	//$data['pics'] = array_images($_POST['pics']); // 组图上传需要处理
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
			//获取所有的区域名，id
			$this->area_list = M('AuctionArea')->field('id, name')->select();

        	$this->display('Admin/agency_add');
        }
    }

	/**
     * 显示拍卖机构所有信息
     */
    public function show() {
        $data = $this->Model->find(I('get.id', 0, 'int'));
		empty($data) AND $this->error('此机构不存在或者已被删除！');
		$this->list = $data;
		
		//关联用户表
		$this->display('Admin/agency_show');
    }

	/* zdy 2013/12/3 */
	public function edit(){

		if(IS_POST){
			
			// 检测URL的完整性
			$id = I('post.id', 0, 'int');
			empty($id) AND $this->error('请检查URL的完整性！');

			// 检测信息是否存在
			$info = $this->Model->field('id')->find($id);
			empty($info) AND $this->error('信息不存在或者已经被删除！');

			// 创建数据对[完成自动验证和自动完成]
			$data = $this->Model->create();

			// 编辑保存操作
			$bool = $this->Model->save();
			if($bool){
				$this->success('编辑成功！');
			} else {
				$this->error('编辑失败, 请重试！');
			}

		}else{
			
			// 检测URL的完整性
			$id = I('get.id', 0, 'int');
			empty($id) AND $this->error('请检查URL的完整性！');

			// 检测信息是否存在
			$data = $this->Model->find($id);
			empty($data) AND $this->error('信息不存在或者已经被删除！');

			// 获取地区[最好是读取缓存和调用function的方法]
			$this->arrArea = M('AuctionArea')->field('id, name')->select();

			$this->data = $data;
			$this->display('Admin/agency_edit');
		}

	}

	
	/**
     * 审核拍卖机构
     */
    public function check() {
        $data = $this->Model->find(I('get.id', 0, 'int'));
		$audit = I('get.audit');
		empty($data) AND $this->error('此机构不存在或者已被删除！');
		if($audit == 'yes') {
			$data['status'] = 1;
			$res = $this->Model->where(array('id' => I('get.id', 0, 'int')))->data($data)->save();
			if($res){
				$this->success('审核成功！');
			} else {
				$this->error('审核出错, 请重试！');
			}
		} elseif($audit == 'no') {
			$data['status'] = 2;
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
		//关联用户表
		$this->display('Admin/agency_show');
    }
    
    /**
     * 删除机构
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
