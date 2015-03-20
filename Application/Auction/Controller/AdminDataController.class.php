<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Admin\Controller\AdminController;

class AdminDataController extends AdminController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionData');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
        //C('PAGE_NUMS', 3); // 重置分页条数
        //$this->page = $this->pages();

        // 分页列表
        $field = array('id', 'title', 'starttime', 'endtime', 'startprice', 'currentprice', 'endprice', 'addtime');
        //$this->list = $this->Model->field($field)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();

        $p=isset($_GET['p'])?$_GET['p']:1;
        //每页显示的数量
        $prePage=10;
        $list=$this->Model->field($field)->page($p.','.$prePage)->order('`id` DESC')->select();
        //p($list);
        $this->assign("list",$list);
        $count= $this->Model->field($field)->count();// 查询满足要求的总记录数
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


        $this->display('Admin/data_index');
    }
    
    /**
     * 根据产品的关键字 返回 下拉的产品列表
     */
    public function goodsdata(){
       
        // 搜索关键字
        $wd = $_GET['wd'];
        empty($wd) AND $this->error('请填写搜索关键字！');
        
        // 关键字为数字则检索ID和货号，否则检索名称和关键字
        if(is_numeric($wd)){
            $where['id']= array('LIKE', '%'.$wd.'%');
            $where['sn']= array('LIKE', '%'.$wd.'%');
        }else{
            $where['name']= array('LIKE', '%'.$wd.'%');
            $where['keywords']= array('LIKE', '%'.$wd.'%');
        }
        $where['_logic'] = 'OR'; // 逻辑关系
        
        $newwhere['_complex'] = $where; // 综合表达式
        $newwhere['isshow'] = 1; // 条件 产品显示
        
        // 获取已经被加入的产品信息
        $newwhere['stock'] = array('gt', 0); // 条件 库存判断
        $arrGids = $this->Model->where('')->field('gid')->select();
        if(!empty($arrGids)){
           foreach($arrGids as $val){
               $gids .= $val['gid'].',';
           }
           $gids = trim($gids, ',');
        }
        empty($gids) OR $newwhere['id'] = array('NOT IN', $gids); // 条件 未被添加到拍卖表里的产品信息  
        
        // 读取满足条件的数据
        $data = D('AuctionGoods')->field('id, name')->where($newwhere)->select();
        
        $this->ajaxReturn($data);
    }

    /**
     * 添加信息
     */
    public function add() {
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            $data = $this->Model->create();
            if ($data) {
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
            $this->display('Admin/data_add');
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
            $this->data = $this->Model->find(I('get.id', 0, 'int'));
            if ($this->data) {
                // 读取拍卖产品名称
                $arr = D('AuctionGoods')->field('`id`, `name`')->where('`isshow` = 1')->find($this->data['gid']);
                $this->option = '<option value="'.$arr['id'].'">'.$arr['name'].'</option>';
                
                $this->display('Admin/data_edit');
            } else {
                $this->error('此信息不存在或已经删除！');
            }
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
