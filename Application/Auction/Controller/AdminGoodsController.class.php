<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Admin\Controller\AdminController;

class AdminGoodsController extends AdminController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionGoods');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
        //C('PAGE_NUMS', 10); // 重置分页条数
        //$this->page = $this->pages();

        // 分页列表
        $field = array('id', 'name', 'isshow', 'sn', 'price', 'addtime', 'stock');
        $p=isset($_GET['p'])?$_GET['p']:1;
        $list=$this->Model->field($field)->page($p.',10')->select();
       /* echo $_GET['p'];exit;
        p($this->list);*/
       //p($list);
        $this->assign("list",$list);
       /* $this->list = $this->Model->field($field)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();*/

        $count      = $this->Model->field($field)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header','共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('end', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show       = $Page->show();// 分页显示输出

        $this->assign('page',$show);// 赋值分页输出


        $this->display('Admin/goods_index');
    }

    /**
     * 添加信息
     */
    public function add() {
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            $data = $this->Model->create();
			
            if ($data) {
            	$data['pics'] = array_images($_POST['pics']); // 组图上传需要处理
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
        	$this->display('Admin/goods_add');
        }
    }

    /**
     * 编辑信息
     */
    public function edit() {
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            $data = $this->Model->create();
            if ($data) {
                // 读取信息 并 检测信息是否存在
                $list = $this->Model->where(array('id' => $data['id']))->find();
                if ($list) {
                    $data['pics'] = array_images($_POST['pics']); // 组图上传需要处理
                    $boolean = $this->Model->save($data);
                    if ($boolean !== false) {
                        $this->success('信息编辑成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
                    } else {
                        $this->error('信息编辑失败！');
                    }
                } else {
                    $this->error('此信息不存在或已经删除！');
                }
            } else {
                $this->error($this->Model->getError());
            }
        } else {
            // 读取信息 并 检测信息是否存在
            $data_res = $this->Model->find(I('get.id', 0, 'int'));
			$this->data = $data_res;
			 
			if ($this->data) {
                $this->display('Admin/goods_edit');
            } else {
                $this->error('此信息不存在或已经删除！');
            }
        }
    }

    /**
     * 更新状态信息
     */
    public function update() {
        // 读取信息并检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        empty($data) AND $this->error('此信息不存在或者已被删除！');
        //更新操作
        $boolean = $this->Model->where(array('id' => $data['id']))->save(array('isshow' => I('get.isshow', 0, 'int')));
        if ($boolean !== false) {
            $this->success('设置成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
        } else {
            $this->error('设置失败！');
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
