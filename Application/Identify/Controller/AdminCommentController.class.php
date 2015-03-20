<?php

// +----------------------------------------------------------------------
// | 鉴定模块_鉴定评论_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;

class AdminCommentController extends AdministratorController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('IdentifyComment');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
        C('PAGE_NUMS', 3); // 重置分页条数
        $this->page = $this->pages();

        // 分页列表
        $field = array('id', 'mid', 'createtime', 'content', 'isopen');
        $this->list = $this->Model->field($field)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();

        $this->display('Admin/comment_index');
    }

    /**
     * 显示详细
     */
    public function detail() {

        $commentid = I('get.id', 0, 'int');

        $field = 'C.mid AS cMid, C.content, C.createtime AS cTime, C.isopen AS cOpen, D.*';
        $this->data = $this->Model->table('bsm_identify_comment C')->join('bsm_identify_data D ON C.identifyid = D.id')->field($field)->where('C.id = ' . $commentid)->find();

        if ($this->data) {
            $this->display('Admin/comment_detail');
        } else {
            $this->error('此信息不存在或已经删除！');
        }
    }

    /**
     * 删除信息
     */
    public function delete() {

        // 读取信息 并 检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        if ($data) {
            $boolean = $this->Model->delete($data['id']);

            if ($boolean !== false) {
                $this->success('信息删除成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
            } else {
                $this->error('信息删除失败！');
            }
        } else {
            $this->error('此信息不存在或已经删除！');
        }
    }
    
    /**
     * 更新状态
     */
    public function update() {

        // 读取信息 并 检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        if ($data) {
            $boolean = $this->Model->where(array('id' => $data['id']))->save(array('isopen' => I('get.isopen', 0, 'int')));

            if ($boolean !== false) {
                $this->success('状态更新成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
            } else {
                $this->error('状态更新失败！');
            }
        } else {
            $this->error('此信息不存在或已经删除！');
        }
    }

}

?>
