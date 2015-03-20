<?php

namespace Special\Controller;
use Admin\Controller\AdminController;

/**
 * 专题模板控制器
 */
class TempController extends AdminController{
    /**
     * 获取专题据列表
     * @return array
     */
    public function index()
    {
        //搜索
        $condition = array();
        if($title = I('post.title')){
            $condition['title'] = array('like', '%'.$title.'%');
        }
        $list = parent::lists('Temp', $condition, 'update_time DESC');
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 添加专题
     */
    public function add()
    {
        $this->display('edit');
    }

    /**
     * 修改专题
     */
    public function edit($id = null)
    {
        $Temp = D('Temp');
        $data = $Temp->find($id);
        if(!$data){
            $this->error($Temp->getError());
        }
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * 通用更新,添加方法
     */
    public function update()
    {
        $Temp = D('Temp');
        //挂载钩子
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        hook('uploadComplete', $param);
        //执行添加操作
        $res = $Temp->update();
        if(!$res){
            $this->error($Temp->getError());
        }else{
            if($res['id']){
                $this->success('更新成功！');
            }else{
                $this->success('新增成功!');
            }
        }
    }

    /**
     * 删除专题
     */
    public function delete($id = null)
    {
        $Temp = D('Temp');
        //挂载钩子
        $param['recordid'] = $Temp->where(array('id'=>$id))->getField('recordid');
        hook('uploadComplete', $param);
        parent::delete();
    }

    /**
     * 改变专题状态
     */
    public function change(){
        $id = I('get.id');
        if(D('Temp')-> where("id=$id")->setField('status', I('get.status','','intval'))){
            $this->success('更新成功！');
        } else {
            $this->error('更新失败！');
        }
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
        $where['id'] = array('IN', $ids);

        // 操作方式
        $act = intval($_POST['act']);

        $Temp = D('Temp');
        // 删除
        if($act == 3){
            for($i=0,$n=count($_POST['ids']);$i<$n;$i++){
                //钩子参数
                $param['recordid'] = $Temp->where(array('id'=>$_POST['ids'][$i]))->getField('recordid');
                hook('uploadComplete', $param);
            }
            $flag = $Temp->where($where)->delete();
        }else{
            // 更改状态
            $data['status'] = intval($_POST['act']);
            $flag = $Temp->where($where)->save($data);
        }

        // 返回状态
        if($flag){
            $this->success('操作成功');
        }else{
            $this->error('操作失败！');
        }
    }

}
