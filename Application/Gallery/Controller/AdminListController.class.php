<?php

// +----------------------------------------------------------------------
// | 总后台 画廊模块 画廊管理 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.20
// +----------------------------------------------------------------------

namespace Gallery\Controller;
use Admin\Controller\AdminController;

class AdminListController extends AdminController {

    public function _initialize(){
        parent::_initialize();
        $this->db_table = 'GalleryList';
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
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);

        // 入库前先获取名称的首汉字的拼音首字母
        (isset($_POST['name']) && !empty($_POST['name'])) AND $_POST['letter'] = firstLetter($_POST['name']);

        parent::add($this->db_table);
    }

    // 编辑信息
    public function edit(){
        //挂载钩子
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        // 入库前先获取名称首汉字的拼音首字母
        (isset($_POST['name']) && !empty($_POST['name'])) AND $_POST['letter'] = firstLetter($_POST['name']);

        parent::edit($this->db_table);
    }

    // 删除信息
    public function delete(){
        //挂载钩子
        $param['recordid'] = D('GalleryList')->where(array('id'=>I('get.id','','intval')))->getField('recordid');
        hook('uploadComplete', $param);
        parent::delete($this->db_table);
    }

    // 排序
    public function listorder(){
        parent::listorder($this->db_table);
    }

    // 更改状态
    public function status(){
        $where['id'] = intval($_GET['id']);
        $data['status'] = intval($_GET['val']);
        if(!empty($where) && !empty($data) && D($this->db_table)->where($where)->save($data)){
            D($this->db_table)->caches(); // 更新缓存
            $this->success('状态更改成功!');
        }else{
            $this->error('状态更改失败!');
        }
    }
} 