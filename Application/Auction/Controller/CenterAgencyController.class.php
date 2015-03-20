<?php

// +----------------------------------------------------------------------
// | 拍卖模块_自动出价_[会员中心]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;

use Member\Controller\MemberController;

class CenterAgencyController extends MemberController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionAgency');//实例化拍卖机构表
    }

    /**
     * 列表信息
     */
    public function index() {
    	
		/*
		 *判断用户是否拥有机构
		 */
		$where['mid'] =  session('mid');
		$this->mem_data = $this->Model->where($where)->find();

        $this->display('Center:agency_index');
    }
	
    //申请机构的模板页面
	public function add() {
		
		//根据用户ID，获取之前填写的信息
	 	$this->list = $this->Model->where(array('mid' => session('mid')))->find();
		
		$this->area_list = M('AuctionArea')->field('id, name')->select();
		$this->display('Center:agency_apply');
	}
	
	//获取申请机构的模板页面的数据，并写入数据库！！
	public function insert() {
		$res = $this->Model->create();
		if($res) {

			//用户id
			$res['mid'] = session('mid');
			
			// 汉字转为拼音且获取首字母
	        $Cn2py = new Cn2py();
	        $pinyin = $Cn2py->doit($res['name'], 'utf-8');
	        $res['pinyin'] = substr($pinyin, 0, 1);
			
			//判断该会员是否已经有机构
			$mem_data = $this->Model->where(array('mid' => session('mid')))->select();
			
			if($mem_data) {
				//创建时间
				$res['updatetime'] = time();
				
				//将审核状态改为0
				$res['status'] = 0;
				
				//将结果数组写入数据库
				$boolean = $this->Model->where(array('mid' => session('mid')))->data($res)->filter('strip_tags')->save();
	            if ($boolean !== false) {
	                $this->success('数据提交成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
	            } else {
	                $this->error('数据提交失败！');
	            }
			} else {
				//创建时间
				$res['createtime'] = time();
				
				//将结果数组写入数据库
				$boolean = $this->Model->add($res);
	            if ($boolean !== false) {
	                $this->success('数据保存成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
	            } else {
	                $this->error('数据保存失败！');
	            }
			}	
		} else {
            $this->error($this->Model->getError());
        }
		
	}

    /**
     * 进入更新修改机构信息页面
     */
     public function show() {
     	
   		$id = I('get.ggid','','int');
		$where = array('mid' => session('mid'), 'id' => $id);
		
		//获取该机构所有信息
		$data_list = $this->Model->where($where)->select();
		$this->data = $data_list;
		
		//获取该机构所在区域名称
		$this->area_name = M('AuctionArea')->where(array('id' => $data_list[0]['areaid']))->field('id, name')->select();
		 
		//获取所有的区域名，id
		$this->area_list = M('AuctionArea')->field('id, name')->select();
		
		if($this->data) {
			$this->display('Center:agency_update');
		} else {
			$this->error('操作错误！');
		}
 
     }
	 
	 //保存更新信息
	 public function update() {
		$res = $this->Model->create();
		if($res) {
			//修改时间
			$res['updatetime'] = time();
			
			// 汉字转为拼音且获取首字母
	        /*
	        $Cn2py = new Cn2py();
	        $pinyin = $Cn2py->doit($res['name'], 'utf-8');
	        $res['pinyin'] = substr($pinyin, 0, 1);
			*/
			//将结果数组写入数据库
			$con =  array('mid' => session('mid'));
			$boolean = $this->Model->where($con)->data($res)->filter('strip_tags')->save();
            if ($boolean !== false) {
                $this->success('数据保存成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
            } else {
                $this->error('数据保存失败！');
            }
		} else {
            $this->error($this->Model->getError());
        }
		
	 }
	 
}
