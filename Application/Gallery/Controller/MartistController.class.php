<?php

// +----------------------------------------------------------------------
// | 画廊 会员中心 画廊艺术家 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Gallery\Controller;
use Gallery\Controller\MemberPublicController;

class MartistController extends MemberPublicController {

    // 初始化
    public function _initialize(){

        parent::_initialize();
        $this->Model = D('GalleryArtist');
        $this->aryAct = array('add', 'edit', 'delete', 'list');
        $this->where = array('gid' => $_SESSION['gid']);
    }

    // 初始化入口
    public function init(){

        // 操作方式
        $act = isset($_GET['act']) ? trim($_GET['act']) : 'list';
        in_array($act, $this->aryAct) OR $this->error('不合法URL');

        // 操作函数
        $act == 'list' AND $this->_list($this->where);
        $act == 'add' AND $this->_add($this->where);
        $act == 'edit' AND $this->_edit($this->where);
        $act == 'delete' AND $this->_delete($this->where);
    }

    // 列表信息[私有:不可直接地址访问]
    private function _list($where){

        // 状态
        isset($_GET['status']) AND $where['status'] = intval($_GET['status']);

        // 查询
        $aryList = $this->Model->where($where)->select();
        empty($aryList) OR $this->aryList = $aryList;

        $this->display('list');
    }

    // 添加信息[私有:不可直接地址访问]
    private function _add($where){

        if(IS_POST){
            $_POST['gid'] = $where['gid'];
            if($this->Model->create()){
                if($this->Model->add() !== false){
                    $this->success('操作成功！');
                }else{
                    $this->error("操作失败!");
                }
            }else{
                $this->error($this->Model->getError());
            }
        }else{
            $this->display('add');
        }
    }

    // 编辑信息[私有:不可直接地址访问]
    private function _edit($where){

        if(IS_POST){

            // 增加ID
            $_POST['gid'] = $where['gid'];
            if($this->Model->create()){
                if($this->Model->save() !== false){
                    $this->success('操作成功！');
                }else{
                    $this->error("操作失败!");
                }
            }else{
                $this->error($this->Model->getError());
            }

        }else{

            // 检测ID
            isset($_GET['id']) AND $id = intval($_GET['id']);
            isset($id) OR $this->error('无效的信息ID');
            $where['id'] = $id;

            // 读取信息
            $data = $this->Model->where($where)->find();
            empty($data) AND $this->error('信息不存在或者已经被删除');

            // 赋值到模板
            $this->data = $data;
            $this->display('edit');
        }
    }

    // 删除信息(jQuery+jsonp)[私有:不可直接地址访问]
    private function _delete($where){

        isset($_GET['id']) AND $id = intval($_GET['id']);
        if(empty($id)) die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效信息')) .')');
        $where['id'] = $id;

        // 先读取信息是否存在，还可以预读是否存在回复信息
        $data = $this->Model->where($where)->field('avatar')->find();
        if(empty($data)) die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'信息不存在或者已被删除')) .')');

        // 删除
        if($this->Model->where($where)->delete()){

            // 更新缓存
            $this->Model->caches();

            echo(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>'信息已被删除')) .')');
            isset($data['avatar']) AND @unlink(trim($data['avatar'], '/'));
        }else{
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'信息删除失败')) .')');
        }
    }

}