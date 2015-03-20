<?php

// +----------------------------------------------------------------------
// | 总后台 画廊模块 画廊作品 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Gallery\Controller;
use Admin\Controller\AdminController;

class AdminWorksController extends AdminController {

    public function _initialize(){
        parent::_initialize();
        $this->db_table = 'GalleryWorks';
    }

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');
        empty($kw) OR $this->kw = $kw;

        $startTime = I('start_time', '', 'trim');
        $endTime = I('end_time', '', 'trim');

        $opt = I('opt', '', 'trim');
        empty($opt) OR $this->opt = $opt;

        // 搜索条件初始化
        $condition = array();

        // 组装搜索条件
        if(!empty($kw) || !empty($startTime) || !empty($endTime)){

            // 开始时间
            empty($startTime) OR $intStart = time2int($startTime, ' 00:00:00');
            $this->assign('start_time', $startTime);

            // 结束时间
            empty($endTime) OR $intEnd = time2int($endTime, ' 23:59:59');
            $this->assign('end_time', $endTime);

            // 关键字
            empty($opt) AND $opt = 'name';
            empty($kw) OR $condition[$opt] = array('like', '%'.$kw.'%');

            // 时间同时具有
            if($startTime != '' && $endTime != ''){
                $condition['create_time'] = array(between, array($intStart, $intEnd));
            }else{
                // 开始时间和结束时间单个
                empty($startTime) OR $condition['create_time'] = array('gt', $intStart);
                empty($endTime) OR $condition['create_time'] = array('lt', $intEnd);
            }
        }

        $this->_list = $this->lists($this->db_table, $condition);
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
        $param['recordid'] = D('GalleryWorks')->where(array('id'=>I('get.id','','intval')))->getField('recordid');
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
                $param['recordid'] =  D('GalleryWorks')->where(array('id'=>$_POST['ids'][$i]))->getField('recordid');
                hook('uploadComplete', $param);
            }
            $flag = D($this->db_table)->where($where)->delete();
            // 删除图片...
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

    // [ajax]根据画廊ID动态获取艺术家信息
    public function artistlist(){
        isset($_GET['gid']) AND $gid = intval($_GET['gid']);
        if(isset($gid)){
            $list = S('GalleryArtist');
            $data = array();
            if(is_array($list) && !empty($list)){
                foreach($list as $rs){
                    $rs['gid'] == $gid AND $data[] = $rs;
                }
            }
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>$data)) .')');
        }
    }

} 