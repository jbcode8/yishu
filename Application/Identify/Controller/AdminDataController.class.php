<?php

// +----------------------------------------------------------------------
// | 鉴定模块_鉴定信息_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
class AdminDataController extends AdministratorController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('IdentifyData');
    }

    /**
     * 列表信息
     */
    public function index() {
        
        // 分页信息
        C('PAGE_NUMS', 3); // 重置分页条数
        $this->page = $this->pages();
        
        // 分页列表
        $field = array('id', 'name', 'category', 'isok', 'isopen', 'mid', 'createtime', 'ispush');
        $this->list = $this->Model->field($field)->limit($this->p->firstRow.', '.$this->p->listRows)->order('`id` DESC')->select();

        $this->display('Admin/data_index');
    }
    
    /**
     * 鉴定信息
     */
    public function detail() {
            
        // 读取信息 并 检测信息是否存在
        $this->data = $this->Model->find(I('get.id', 0, 'int'));
        if($this->data){
            if(!$this->data['isok']){
                $this->error('此信息未鉴定！');
            }else{
                $this->display('Admin/data_detail');
            }
        }else{
            $this->error('此信息不存在或已经删除！');
        }        
    }
    
    /**
     * 鉴定信息
     */
    public function edit() {
        
        if(isset($_POST['submit']) && !empty($_POST['submit'])){
                 
            $data = $this->Model->create();
            if($data){
                
                // 读取信息 并 检测信息是否存在
                $list = $this->Model->where(array('id' => $data['id']))->find();
                
                if($list){
                    
                    $boolean = $this->Model->save($data);
                
                    if ($boolean !== false) {  
                        $this->success('信息鉴定成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
                    } else {
                        $this->error('信息鉴定失败！');
                    }
                    
                }else{
                    
                    $this->error('此信息不存在或已经删除！');
                }
                
            }else{
                $this->error($this->Model->getError());
            }
            
        }else{
            
            // 读取信息 并 检测信息是否存在
            $this->data = $this->Model->find(I('get.id', 0, 'int'));
            if($this->data){
                if($this->data['isok']){
                    $this->error('此信息已经鉴定！');
                }else{
                    $this->display('Admin/data_edit');
                }
            }else{
                $this->error('此信息不存在或已经删除！');
            }
        }
    }
    
    /**
     * 更新状态信息
     */
    public function update() {
        
        // 条件：关联此用户对应的信息
        $data = $this->Model->find(I('get.id', 0, 'int'));
        
        empty($data) AND $this->error('此信息不存在或者已被删除！');
        
        //更新操作
        $boolean = $this->Model->where(array('id' => $data['id']))->save(array('ispush' => I('get.ispush', 0, 'int')));
        if ($boolean !== false) {
            $this->success('设置成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
        } else {
            $this->error('设置失败！');
        }
    }
    
    /**
     * 删除信息
     */
    public function delete() {
        
        // 读取信息 并 检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        if($data){
            $boolean = $this->Model->delete($data['id']);

            if ($boolean !== false) {  
                $this->success('信息删除成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
            } else {
                $this->error('信息删除失败！');
            }
        }else{
            $this->error('此信息不存在或已经删除！');
        }
        
    }

}

?>
