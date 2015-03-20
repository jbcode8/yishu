<?php

// +----------------------------------------------------------------------
// | 拍卖模块_自动出价_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Admin\Controller\AdminController;
class AdminAutoController extends AdminController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionAutoPrice');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
        //C('PAGE_NUMS', 10); // 重置分页条数
       // $this->page = $this->pages();

        // 分页列表
       // $this->list = $this->Model->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`price` DESC')->select();


        $p=isset($_GET['p'])?$_GET['p']:1;
        //每页显示的数量
        $prePage=10;
        $list=$this->Model->page($p.','.$prePage)->order('`price` DESC')->select();
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



        $this->display('Admin/auto_index');
    }

    /**
     * 更新状态信息
     */
    public function update() {
        // 读取信息并检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        empty($data) AND $this->error('此信息不存在或者已被删除！');
        //更新操作
        $boolean = $this->Model->where(array('id' => $data['id']))->save(array('isopen' => I('get.isopen', 0, 'int')));
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
