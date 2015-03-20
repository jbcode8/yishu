<?php

// +----------------------------------------------------------------------
// | 店铺焦点图 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain.Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class MfocusController extends MpublicController {

    public function _initialize(){

        parent::_initialize();

        $this->Model = D('StoreFocus');
        $this->aryAct = array('add', 'edit', 'delete', 'list', 'status');
        $this->where = array('store_id' => $_SESSION['store_id']);
        $this->indexId = 'id';
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
        $act == 'status' AND $this->_status($this->where);
    }

    /**
     * 更改状态
     * @param $where
     */
    private function _status($where){

        isset($_GET[$this->indexId]) AND $id = intval($_GET[$this->indexId]);
        isset($id) OR $this->error('无效信息');
        $where[$this->indexId] = $id;

        isset($_GET['val']) AND $val = intval($_GET['val']);
        $val = $val == 0 ? 0 : 1;

        if($this->Model->where($where)->save(array('status'=>$val))){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    // 列表
    private function _list($where){

        // 状态
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) AND $this->status = $status;
        isset($status) AND $where['status'] = $status;

        // 查询
        $aryList = $this->Model->where($where)->select();

        empty($aryList) OR $this->aryList = $aryList;
        $this->count = count($aryList);

        $this->display('list');
    }

    // 添加信息[私有:不可直接地址访问]
    private function _add($where){

        if(IS_POST){

            $_POST['store_id'] = $where['store_id'];

            // 数据比对，构建入库数组
            if($this->Model->create() !== false){

                if ($this->Model->add()) {
                    $this->success('操作成功', U(MODULE_NAME.__ACTION__));
                } else {
                    $this->error('操作失败');
                }

            }else{
                $this->error($this->Model->getError());
            }

        }else{

            $this->store_id = $where['store_id'];
            $this->display('add');
        }
    }

    // 编辑信息[私有:不可直接地址访问]
    private function _edit($where){

        if(IS_POST){

            $where[$this->indexId] = $_POST[$this->indexId];

            // 数据比对，构建入库数组
            if($this->Model->create() !== false){

                if ($this->Model->where($where)->save() !== false) {
                    $this->success('操作成功', U(MODULE_NAME.__ACTION__));
                } else {
                    $this->error('操作失败');
                }

            }else{
                $this->error($this->Model->getError());
            }

        }else{

            isset($_GET[$this->indexId]) AND $id = intval($_GET[$this->indexId]);
            isset($id) OR $this->error('无效信息');
            $where[$this->indexId] = $id;

            $data = $this->Model->where($where)->find();
            empty($data) AND $this->error('信息不存在或者已经删除！');

            $this->store_id = $where['store_id'];
            $this->data = $data;
            $this->display('edit');
        }
    }

    // 删除信息(ajax)[私有:不可直接地址访问]
    private function _delete($where){

        // 判断ID
        isset($_GET[$this->indexId]) AND $id = intval($_GET[$this->indexId]);
        isset($id) OR $this->error('无效的信息ID');
        $where[$this->indexId] = $id;

        // 先读取信息是否存在，还可以预读是否存在回复信息
        $data = $this->Model->where($where)->field($this->indexId)->find();
        $data[$this->indexId] == $id OR $this->error('信息不存在或者已被删除');

        $bool = $this->Model->where($where)->delete();
        if($bool){
            @unlink($data['img']);
            $this->success('信息已被删除');
        }
    }
} 