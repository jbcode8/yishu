<?php

// +----------------------------------------------------------------------
// | 鉴定模块_鉴定类别_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
class AdminCategoryController extends AdministratorController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('IdentifyCategory');
    }

    /**
     * 列表信息
     */
    public function index() {
        
        // 分页信息
        C('PAGE_NUMS', 3); // 重置分页条数
        $this->page = $this->pages();
        
        // 分页列表
        $field = array('id', 'name', 'createtime');
        $this->list = $this->Model->field($field)->limit($this->p->firstRow.', '.$this->p->listRows)->order('`id` DESC')->select();

        $this->display('Admin/category_index');
    }
    
    /**
     * 添加信息
     */
    public function add() {
        
        if(isset($_POST['submit']) && !empty($_POST['submit'])){
            
            $data = $this->Model->create();
            
            if($data){
                
                $boolean = $this->Model->add($data);
                
                if ($boolean !== false) {  
                    $this->success('信息添加成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
                } else {
                    $this->error('信息添加失败！');
                }
                
            }else{
                $this->error($this->Model->getError());                
            }
            
        }else{
            
            $this->display('Admin/category_add');
        }
    }
    
    
    /**
     * 编辑信息
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
                        $this->success('信息编辑成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
                    } else {
                        $this->error('信息编辑失败！');
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
                $this->display('Admin/category_edit');
            }else{
                $this->error('此信息不存在或已经删除！');
            }
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
