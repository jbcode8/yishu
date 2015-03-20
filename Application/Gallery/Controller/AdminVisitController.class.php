<?php

// +----------------------------------------------------------------------
// | 总后台 画廊模块 画廊访谈 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Gallery\Controller;
use Admin\Controller\AdminController;

class AdminVisitController extends AdminController {

    public function _initialize(){
        parent::_initialize();
        $this->db_table = 'GalleryVisit';
    }

    // 列表信息
    public function index(){
        $this->_list = $this->lists($this->db_table);
        $this->display();
    }

    // 添加信息
    public function add(){

        //挂载钩子
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        hook('uploadComplete', $param);
        // 添加操作
        parent::add($this->db_table);
    }

    // 编辑信息
    public function edit(){

        //挂载钩子
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        hook('uploadComplete', $param);
        parent::edit($this->db_table);
    }

    // 删除信息[单个]
    public function delete(){
        //挂载钩子
        $param['recordid'] = D('GalleryVisit')->where(array('id'=>I('get.id','','intval')))->getField('recordid');
        hook('uploadComplete', $param);
        parent::delete($this->db_table);
    }

    // 更改状态
    public function status(){
        $where['id'] = intval($_GET['id']);
        $data['status'] = intval($_GET['val']);
        if(!empty($where) && !empty($data) && D($this->db_table)->where($where)->save($data)){
            $this->success('状态更改成功!');
        }else{
            $this->error('状态更改失败!');
        }
    }

    // 批量操作
    public function batch(){

        // 检测合法性
        (!isset($_POST['ids']) || empty($_POST['ids'])) AND $this->error('请先选择数据!');
        isset($_POST['act']) OR $this->error('请选择操作方式!');

        // 获取ID值且组装条件
        $ids = implode(',', $_POST['ids']);
        $where['id'] = array('in', $ids);

        // 操作方式
        $act = intval($_POST['act']);

        // 删除
        if($act == 4){
            //挂载钩子
            for($i=0,$n=count($_POST['ids']);$i<$n;$i++){
                $param['recordid'] =  D('GalleryVisit')->where(array('id'=>$_POST['ids'][$i]))->getField('recordid');
                hook('uploadComplete', $param);
            }
            $flag = D($this->db_table)->where($where)->delete();
        }else{
            // 更改状态
            $data['status'] = intval($_POST['act']);
            $flag = D($this->db_table)->where($where)->save($data);
        }

        // 返回状态
        if($flag){
            $this->success('操作成功!');
        }else{
            $this->error('操作失败!');
        }
    }
} 