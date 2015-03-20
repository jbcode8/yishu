<?php
// +----------------------------------------------------------------------
// | PositionController.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Controller;

class PositionController extends AdminController {

    /**
     * 推荐位列表
     */
    public function index(){
        $list = parent::lists('Position');
        $this->assign('_list',$list);
        $this->display();
    }

    /**
     * 编辑前
     */
    public function _before_edit(){
        if(!$model = S('models')){
            $model = D('ModelMall')->getList();
            S('models',$model);
        }
        $this->assign('model',$model);
    }

    /**
     * 添加前
     */
    public function _before_add(){
        if(!$model = S('models')){
            $model = D('ModelMall')->getList();
            S('models',$model);
        }
        $this->assign('model',$model);
    }

    /**
     * 删除推荐位,并删除数据
     * @param int $posid
     */
    public function delete($posid = null){
        $res = M('PositionMall')->delete($posid);
        if($res){
            M('PositionDataMall')->where(array('posid'=>$posid))->delete();
            $this->success('删除成功!');
        }else{
            $this->error('删除失败!');
        }
    }

    /**
     * 推荐位内容列表
     * @param $posid
     */
    public function items($posid){
        $data = D('PositionDataMall')->where(array('posid'=>$posid))->select();
        parent::recordList($data);
        $this->display();
    }

    /**
     * 编辑推荐位数据
     * @param int $id
     * @param int $model_id
     * @param int $posid
     */
    public function edit_data($id = null,$model_id = null,$posid = null){
        $model = D('PositionDataMall');
        $data = $model->where(array('id'=>$id,'model_id'=>$model_id,'posid'=>$posid))->find();
        if(IS_POST){
            if($model->saveData($_POST,$data)){
                $this->success('更新成功!');
            }else{
                $this->error($model->getError());
            }
        }else{
            $this->assign('vo',$data);
            $this->display();
        }
    }

    /**
     * 删除推荐位数据
     * @param int $id
     * @param int $model_id
     * @param int $posid
     */
    public function delete_data($id = null,$model_id = null,$posid = null){
        $map = array('id'=>$id,'model_id'=>$model_id,'posid'=>$posid);
        if(M('PositionDataMall')->where($map)->delete()){
            $this->success('删除成功!');
        }else{
            $this->error('删除失败!');
        }
    }
}