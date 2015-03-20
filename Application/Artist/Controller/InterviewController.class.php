<?php
// +----------------------------------------------------------------------
// | 大师频道独家专访控制器
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Controller;


class InterviewController extends  ArtistAdminController{
    public function _initialize(){
        parent::_initialize();
        $this->db_table = 'ArtistInterview';
    }

    /**
     * 大独家专访管理列表
     */
    public function index(){
        $map = array();
        $list = parent::lists('Interview', $map, 'createtime ASC');
        $this->assign('_list', $list);
        $this->display();
    }

    public function add(){
        //挂载钩子
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        hook('uploadComplete', $param);
        parent::add($this->db_table);
    }

    public function edit(){
        //挂载钩子
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        hook('uploadComplete', $param);
        parent::edit($this->db_table);
    }

    public function delete(){
        //挂载钩子
        $param['recordid'] = D('interview')->where(array('id'=>I('get.id','','intval')))->getField('recordid');
        hook('uploadComplete', $param);
        parent::delete($this->db_table);
    }

} 