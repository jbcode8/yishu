<?php
// +----------------------------------------------------------------------
// | 大师频道控制器类
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Controller;
use Admin\Controller\AdminController;



class ArtistAdminController extends AdminController{

    /**
     * get方式修改数据行
     */
    protected function artistEditRow($model = CONTROLLER_NAME){
       If(IS_GET){
           $model = D($model);
           $pk = $model->getPk();
           if(isset($_GET[$pk])){
               $data = array();
               foreach($_GET as $key => $value){
                    $data[$key] = addslashes(htmlspecialchars($value));
               }
               $model->save($data);
               $this->success('修改成功！');
           }
       }
    }

    /**
     * 高级搜索对话框弹出方法
     */
    public function search(){
        $this->display();
    }

    /**
     * 大师频道解锁/锁定方法
     * @param mixed|string $model
     */
    public function updateStatus($model = CONTROLLER_NAME){
        $model = D($model);
        $pk = $model->getPk();
        $status = I('status', '', 'intval');
        if (IS_POST) {
            $ids = I('post.ids');
            $where = array($pk => array('IN', $ids));
        } else {
            $id = I('get.'.$pk, '', 'intval');
            $where = array($pk => $id);
        }
        $data = array('status'=>$status);
        $res = $model->where($where)->save($data);
        if ($res > 0) {
            $this->success('修改成功');
        } else {
            $this->error('数据没有任何变化');
        }
    }

} 