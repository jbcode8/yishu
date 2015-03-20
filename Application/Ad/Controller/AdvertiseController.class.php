<?php
// +----------------------------------------------------------------------
// | AdController.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Ad\Controller;
use Admin\Controller\AdminController;

class AdvertiseController extends AdminController
{
    /**
     * 广告列表信息
     */
    public function index()
    {
        //搜索
        $condition = array();
        if($title = I('post.title')) {
            $condition['title'] = array('like', '%'.$title.'%');
        }
        $list = parent::lists('Advertise', $condition, 'starttime DESC');
        //广告位整理
        $space = D('Space')->getSpaceCache();
        foreach($space as $key=>$v) {
            $spacename[$v['id']] = $v['spacename'];
        }
        $this->assign('spacename', $spacename);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 添加广告
     */
    public function add()
    {
        //获取广告位
        $spacename = D('Space')->getSpace();
        $this->assign('space', $spacename);
        $this->display('edit');
    }

    /**
     * 修改广告
     */
   public function edit($id = null)
   {
       $advertise = D('Advertise');
       $data = $advertise->find($id);
       if(!$data){
           $this->error($advertise->getError());
       }
       //获取广告位
       $spacename = D('Space')->getSpace();
       $this->assign('space', $spacename);
       $this->assign('data',$data);
       $this->display();
   }

    /**
     * 通用更新,添加方法
     */
    public function update()
    {
        $advertise = D('Advertise');
        //挂载钩子
        if(I('post.type','','intval') == 1 ) {
            $param['recordid'] = I('post.recordid');
            $param['sourceid'] = I('post.sourceid');
            hook('uploadComplete', $param);
        }
        $res = $advertise->update();
        if(!$res){
            $this->error($advertise->getError());
        }else{
            if($res['id']){
                $this->success('更新成功！');
            }else{
                $this->success('新增成功!');
            }
        }
    }

    /**
     * 删除广告
     */
    public function delete()
    {
        //修改所属广告位下的广告数
        $space = D('Space')->where(array('id'=>I('get.sid')))->setDec('adcount');
        $advertise = D('Advertise');
        //钩子参数
        $param['recordid'] = $advertise->where(array('id'=>I('get.id')))->getField('recordid');
        hook('uploadComplete', $param);
        parent::delete();
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
            $advertise = D('Advertise');
            for($i=0,$n=count($_POST['ids']);$i<$n;$i++){
                $sid = $advertise->getSid($_POST['ids'][$i]);
                //钩子参数
                $param['recordid'] = $advertise->where(array('id'=>$_POST['ids'][$i]))->getField('recordid');
                hook('uploadComplete', $param);
                D('Space')->where(array('id'=>$sid))->setDec('adcount');
            }
            $flag = D('Advertise')->where($where)->delete();
        } else {
            // 更改状态
            $data['status'] = intval($_POST['act']);
            $flag = D('Advertise')->where($where)->save($data);
        }

        // 返回状态
        if($flag){
            $this->success('操作成功');
        }else{
            $this->error('操作失败！');
        }
    }

    /**
     * 搜索广告
     */
    public function search()
    {
        $space= D('Space')->getsPace();
        $this->assign('data',$space);
        $this->display();
    }

    /**
     * 转换广告状态
     */
    public function change()
    {
        if(D('Advertise')->where(array('id'=>I('get.id', '', 'intval')))->setField('status', I('get.status','','intval'))){
            $this->success('更新成功！');
        } else {
            $this->error('更新失败！');
        }
    }

}
?>