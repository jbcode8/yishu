<?php

namespace Message\Controller;
use Admin\Controller\AdminController;

class ContentController extends AdminController
{

    /**
     * 留言内容
     */
    public function index()
    {
        //搜索
        $condition = array();
        if($title = I('post.title')) {
            $condition['title'] = array('like', '%'.$title.'%');
        }
        $list = parent::lists('Content', $condition, 'create_time DESC');
        $this->assign('list', $list);
        $this->assign('category', D('category')->getMessageBoard());
        $this->display();
    }

    /**
     * 批量操作
     */
    public function batch()
    {
        // 检测合法性
        (!isset($_POST['ids']) || empty($_POST['ids'])) AND $this->error('请先选择数据!');
        isset($_POST['act']) OR $this->error();

        // 获取ID值且组装条件
        $ids = implode(',', $_POST['ids']);
        $where['id'] = array('in', $ids);

        // 操作方式
        $act = intval($_POST['act']);

        // 删除
        if($act == 3) {
            $flag = D('Content')->where($where)->delete();
        } else {
            // 更改状态
            $data['status'] = intval($_POST['act']);
            $flag =D('Content')->where($where)->save($data);
        }

        // 返回状态
        if($flag){
            $this->success('操作成功');
        }else{
            $this->error('操作失败！');
        }
    }

    /**
     * 搜索留言
     */
    public function search()
    {
        $this->assign('category', D('category')->getMessageBoard());
        $this->display();
    }

    /**
     * 转换留言状态
     */
    public function change()
    {
        if(D('Content')->where(array('id'=>I('get.id', '', 'intval')))->setField('status', I('get.status','','intval'))){
            $this->success('更新成功！');
        } else {
            $this->error('更新失败！');
        }
    }

    /**
     * 检查用户名
     */
    public function checkUsername(){
        $username = I('get.username','','trim');
        $result = M('ucenter_member')->where(array('username'=>$username))->getField('id');
        echo $_GET['backfunc'].'('.json_encode($result).')';
    }

}