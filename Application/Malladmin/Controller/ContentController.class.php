<?php
// +----------------------------------------------------------------------
// | ContentController.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Controller;


class ContentController extends AdminController {

    public function index(){
        $list = M('CategoryMall')->field('catid as id,pid as pId,title as name,model')->select();
        foreach($list as $k=>$v){
            $listArr[$k] = $v;
            $listArr[$k]['src'] = U('Content/clist?catid='.$v['id'].'&model='.$v['model']);
        }
        $this->assign('treelist',json_encode($listArr));
        $this->display();
    }

    /**
     * 获取各栏目数据列表
     * @param int $catid
     * @param null $model
     * @return array|false|void
     */
    public function clist($catid,$model){
        //获取分类模型
        $model_name = get_document_model($model);
        //获取模型是否继承
        $pid = get_document_model($model,'extend');
        if($pid == 0){
            $list = parent::lists($model_name);
        }else{
            $list = parent::lists('Document');
        }
        $this->assign('_list',$list);
        $this->display();
    }

    /**
     * 添加内容
     */
    public function add($catid = null,$model = null){
        $template = $this->fetch(get_document_model($model));
        $vo = array(
            'catid'=>$catid,
            'model'=>$model,
        );
        $this->assign('vo',$vo);
        $this->assign('extend',$template);
        $this->display('edit');
    }

    /**
     * 更新内容
     */
    public function edit($id = null){
        $Document = D('Document');
        $data = $Document->detail($id);
        if(!$data){
            $this->error($Document->getError());
        }
        $this->assign('vo',$data);
        $template = $this->fetch(get_document_model($data['model']));
        $this->assign('extend',$template);
        $this->display();
    }

    /**
     * 通用更新,添加方法
     */
    public function update(){
        $Document = D('Document');
        //执行添加操作
        $res = $Document->update();
        if(!$res){
            $this->error($Document->getError());
        }else{
            if($res['id']){
                $this->success('更新成功!');
            }else{
                $this->success('新增成功!');
            }
        }
    }

    /**
     * 删除数据
     * @param int $id
     * @param null $model
     */
    public function delete($id = null,$model=null){
        $Document = D('Document');
        $res = $Document->delData($id,$model);
        if($res){
            M('PositionDataMall')->where(array('id'=>$id,'model'=>$model))->delete();
            $this->success('删除成功!');
        }else{
            $this->error($Document->getError());
        }
    }

    /**
     * 审核
     * @param int $id
     */
    public function enabled($id = null){
        $this->editRow('Document','id',array('status'=>1),array('id'=>$id),array( 'success'=>'审核成功！', 'error'=>'审核失败！'));
    }

    /**
     * 取消审核
     * @param int $id
     */
    public function disable($id = null){
        $this->editRow('Document','id',array('status'=>0),array('id'=>$id),array( 'success'=>'取消审核成功！', 'error'=>'取消审核失败！'));
    }
}