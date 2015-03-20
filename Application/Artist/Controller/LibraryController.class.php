<?php
// +----------------------------------------------------------------------
// | 大师频道艺术库控制器基类
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Controller;

class LibraryController extends ArtistAdminController{
    public function _initialize(){
        parent::_initialize();
        $this->db_table = 'ArtistLibrary';
    }

    /**
     * 艺术家管理列表
     */
    public function index(){
        $map = array();
        $list = parent::lists('Library', $map, 'createtime ASC');
        $this->assign('_list', $list);
        $this->display();
    }

    public function add(){
        //挂载钩子
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        $_POST['letter'] = firstLetter($_POST['name']);
		$_POST['jointime'] = strtotime($_POST['jointime']);
		$_POST['birthday'] = strtotime($_POST['birthday']);
        parent::add($this->db_table);
    }

    public function edit(){
        //挂载钩子
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        $_POST['letter'] = firstLetter($_POST['name']);
		$_POST['jointime'] = strtotime($_POST['jointime']);
		$_POST['birthday'] = strtotime($_POST['birthday']);
        parent::edit($this->db_table);
    }

    public function delete(){
        //挂载钩子
        $param['recordid'] = D('Library')->where(array('id'=>I('get.id','','intval')))->getField('recordid');
        hook('uploadComplete', $param);
        parent::delete($this->db_table);
    }

    /**
     * 修改艺术家的级别
     */
    public function updateType(){
        if(IS_GET){
            $this->artistEditRow();
        }
        else{
            $ids = I('post.ids');
            $type = I('post.type', '', 'intval');
            if(is_int($type)){
                $res = D('Library')->where(array('id'=>array('IN',$ids)))->save(array('type'=>$type));
                if($res !== false)
                    $this->success('修改成功');
                else
                   $this->error('数据没有任何变化');
            }
            else{
                 $this->error('操作被拒绝');
            }
        }
    }

    /**
     * 艺术家名字选择面板渲染方法，用于form表单的中选择艺术家
     * @param string $domId 表单的input标签的ID属性值
     * @param string $aid   默认选中的艺术家ID标识，用于编辑表单，添加表单可不填
     */
    public function  names($domId="_artist_name", $aid=''){
        $this->assign('domId', $domId);
        $this->assign('aid', $aid);
        $res = D('Library')->field('id,name,letter')->where('status = 1')->select();
        $az=array('A','B','C','D','E','F','G','H','J','K','L','M','N','O','P','Q','R','S','T','W','X','Y','Z','qt');
        $this->assign('az', $az);
        $name = array();
        foreach($res as $artist){
            $letter = $artist['letter'];
            if(in_array($letter, $az)){
                $key = isset($name[$letter]) && is_array($name[$letter]) ? count($name[$letter]) : 0;
                $name[$letter][$key]['aid'] = (int)$artist['id'];
                $name[$letter][$key]['name'] = $artist['name'];
            }
            else{
                $key = isset($name['qt']) && is_array($name['qt']) ? count($name['qt']) : 0;
                $name['qt'][$key]['aid'] = (int)$artist['id'];
                $name['qt'][$key]['name'] = $artist['name'];
            }
        }
        ksort($name);
        $this->assign('nameArtist', $name);
        $this->display('Library:names');
    }



}