<?php
// +----------------------------------------------------------------------
// | 大师作品控制器类
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Controller;


class WorksController  extends ArtistAdminController{
    public function _initialize(){
        parent::_initialize();
        $this->db_table = 'ArtistWorks';
    }

    /**
     * 大师作品管理列表
     */
    public function index(){
           $map = array();
           $list = parent::lists('Works', $map, 'createtime ASC');
           $this->assign('_list', $list);
           $this->display();
    }

    public function add(){
        //挂载钩子
        $_POST['createtime'] = time();
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        hook('uploadComplete', $param);
        parent::add($this->db_table);
    }

    public function edit(){
        //挂载钩子
        $_POST['updatetime'] = time();
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        parent::edit($this->db_table);
    }

    public function delete(){
        //挂载钩子
        $param['recordid'] = D('works')->where(array('id'=>I('get.id','','intval')))->getField('recordid');
        hook('uploadComplete', $param);
        parent::delete($this->db_table);
    }


}