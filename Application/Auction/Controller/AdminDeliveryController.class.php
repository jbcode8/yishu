<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Admin\Controller\AdminController;
use Home\Controller\HomeController;
class AdminDeliveryController extends AdminController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = M('AuctionDelivery');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
       // C('PAGE_NUMS', 10); // 重置分页条数
        //$this->page = $this->pages();

        // 分页列表
        $field = array('id', 'name', 'status', 'createtime', 'updatetime');
        //$this->list = $this->Model->field($field)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();

        //$field = array('id', 'name', 'mid', 'status', 'createtime', 'updatetime');
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


		//dump($this->list);
        $this->display('Admin/delivery_index');
    }

   /*
    *投递品详细页 
    */
   public function show() {
   		if (isset($_GET['audit']) && !empty($_GET['audit'])) {
            $data = $this->Model->find(I('get.id', 0, 'int'));
			$audit = I('get.audit');
			empty($data) AND $this->error('此信息不存在或者已被删除！');
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
        } else {
        	$this->list = $this->Model->find(I('get.id', '', 'int'));
        	$this->display('Admin/delivery_show');
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
	
}

?>
