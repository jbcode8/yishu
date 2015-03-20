<?php


namespace Discover\Controller;
use Admin\Controller\AdminController;

class DiscoverController extends AdminController {

    public function _initialize(){
        parent::_initialize();
        $this->db_table = 'Discover';
    }

    // 列表信息
    public function index()
    {
        $list = parent::lists('Discover', '', 'create_time DESC');
        $this->assign('list', $list);
        $this->display();
    }

    // 编辑信息
    public function edit()
    {
        parent::edit($this->db_table);
    }
    // 删除信息
    public function delete()
    {
        parent::delete($this->db_table);
    }

    public function update()
    {
        $GalleryExhibit = D('Discover');
        //执行添加操作
        $res = $GalleryExhibit->update();
        if(!$res){
            $this->error($GalleryExhibit->getError());
        }else{
            if($res['id']){
                $this->success('更新成功！');
            }else{
                $this->success('新增成功!');
            }
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
        $where['id'] = array('in', $ids);

        // 操作方式
        $act = intval($_POST['act']);

        // 删除
        if($act == 3) {
            $flag = D('Discover')->where($where)->delete();
        } else {
            // 更改状态
            $data['status'] = intval($_POST['act']);
            $flag =D('Discover')->where($where)->save($data);
        }

        // 返回状态
        if($flag){
            $this->success('操作成功');
        }else{
            $this->error('操作失败！');
        }
    }

    /**
     * 转换新闻状态
     */
    public function change()
    {
        if(D('Discover')->where(array('id'=>I('get.id', '', 'intval')))->setField('status', I('get.status','','intval'))){
            $this->success('更新成功！');
        } else {
            $this->error('更新失败！');
        }
    }

} 