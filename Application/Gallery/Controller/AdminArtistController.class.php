<?php

// +----------------------------------------------------------------------
// | 总后台 画廊模块 画廊大师 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Gallery\Controller;
use Admin\Controller\AdminController;

class AdminArtistController extends AdminController {

    public function _initialize(){
        parent::_initialize();
        $this->db_table = 'GalleryArtist';
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

    // 删除信息
    public function delete(){
        //挂载钩子
        $param['recordid'] = D('GalleryArtist')->where(array('id'=>I('get.id','','intval')))->getField('recordid');
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