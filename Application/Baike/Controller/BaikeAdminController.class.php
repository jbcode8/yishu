<?php
// +----------------------------------------------------------------------
// | 艺术百科控制器基类
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Controller;
use Admin\Controller\AdminController;

class BaikeAdminController extends AdminController
{
    /**
     * 更改锁定状态POST提交为批量修改，GET提交为单条记录修改
     */
    public function alertlock(){
        $model = D(CONTROLLER_NAME);
        $pk = $model->getPk();
        if(IS_POST){
            $ids = I('post.ids');
            if (!$ids) {
                $this->error('没有选择任何信息');
            }
            else{
                $map[$pk] = array('IN', $ids);
                $locked = I('post.locked', '', 'intval');
                if ($locked === '') {
                    $this->error('参数有误');
                } else {
                    $data['locked'] = $locked;
                    $res = $model->where($map)->save($data);
                    if($res)
                        $this->success('操作成功');
                    else
                        $this->error('没有任何修改');
                }
            }
        }
        else{
            $pkVal = I('get.'.$pk, false, 'intval');
            $locked = I('get.locked',false,'intval');
            if($pkVal){
                $data[$pk] = $pkVal;
                $data['locked'] = $locked;
                if($model->save($data)){
                    $this->success('操作成功！');
                }
                else{
                    $this->error(' 没有任何变化！');
                }
            }
            else{
                $this->error('参数有误！');
            }
        }
    }

    /**
     * 更改版本的优秀状态
     */
    public function alertexcellent(){
        $model = D(CONTROLLER_NAME);
        $pk = $model->getPk();
        $ids = I('post.ids');
        if (!$ids) {
            $this->error('没有选择任何信息');
        }
        else{
            $map[$pk] = array('IN', $ids);
            $excellent = I('post.excellent', '', 'intval');
            if ($excellent === '') {
                $this->error('参数有误');
            } else {
                $data['excellent'] = $excellent;
                $res = $model->where($map)->save($data);
                if($res)
                    $this->success('操作成功');
                else
                    $this->error('没有任何修改');
            }
        }
    }

    /**
     * 审核方法
     */
    public function audit(){
        $model = D(CONTROLLER_NAME);
        $pk = $model->getPk();
        $ids = I('post.ids');
        if (!$ids) {
            $this->error('没有选择任何信息');
        }else{
            $map[$pk] = array('IN', $ids);
            $data['visible'] = 1;
            $res = $model->where($map)->save($data);
            if($res)
                $this->success('操作成功');
            else
                $this->error('没有任何修改');
        }
    }

    /**
     * 高级搜索对话框弹出方法
     */
    public function search(){
        $this->display();
    }

}