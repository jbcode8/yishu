<?php

// +----------------------------------------------------------------------
// | 鉴定模块_鉴定信息_[会员中心]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
class CenterDataController extends MemberController {

   // 定义模型
    protected $Model;

    // 初始化
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('IdentifyData');
    }

    /**
     * 鉴定信息
     */
    public function index() {
        
        // 条件：关联此用户对应的信息
        $where['mid'] =  session('mid');
        
        $this->page = $this->pages($where);// 此处的分页局限：必须使用$this->Model定义模型
        $this->list = $this->Model->where($where)->limit($this->p->firstRow.', '.$this->p->listRows)->order('id DESC')->select();

        $this->display('Center:data_index');
    }
    
    /**
     * 详细信息
     */
     public function detail() {
         
        // 条件：关联此用户对应的信息
        $where['mid'] =  session('mid');
        $data = $this->Model->where($where)->find(I('get.id',0,'int'));
        
        empty($data) AND $this->error('此信息不存在或者已被删除！');
        empty($data['isok']) AND $this->error('此信息还没鉴定，无鉴定结果！');
        
        $this->data = $data;
        $this->display('Center:data_detail');
     }
     
     /**
     * 更新是否公开的状态
     */
     public function update() {
         
        // 条件：关联此用户对应的信息
        $where['mid'] =  session('mid');
        $data = $this->Model->where($where)->find(I('get.id', 0, 'int'));
        
        empty($data) AND $this->error('此信息不存在或者已被删除！');
        
        //更新操作
        $boolean = $this->Model->where(array('id' => $data['id']))->filter('strip_tags')->save(array('isopen' => I('get.isopen', 0, 'int')));
        if ($boolean !== false) {
            $this->success('状态更新成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
        } else {
            $this->error('状态更新失败！');
        }
     }

    /**
     * 添加信息
     */
    public function add() {

        if (isset($_POST['submit']) && !empty($_POST['submit'])) {

            $data = $this->Model->create();

            if ($data) {
                
                // 处理组图
                if($data['pics']){
                    $data['pics'] = array_images($data['pics']);
                }

                $boolean = $this->Model->filter('strip_tags')->add($data);
                if ($boolean !== false) {
                    $this->success('数据保存成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
                } else {
                    $this->error('数据保存失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }
            
        } else {

            $eid = I('get.eid','int');
            $this->eid = $eid;
            $this->display('Center:data_add');
        }
    }

    /**
     * 编辑信息
     */
    public function edit() {

        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            $data = $this->Model->create();
            if ($data) {
                
                // 条件：关联此用户对应的信息
                $where['mid'] =  session('mid');
                $info = $this->Model->where($where)->find($data['id']);

                empty($info) AND $this->error('此信息不存在或者已被删除！');
                empty($info['isok']) OR $this->error('此信息已经鉴定完毕，将不可编辑！');

				// 处理组图
                if($data['pics']){
                    $data['pics'] = array_images($data['pics']);
                }
                
                $boolean = $this->Model->filter('strip_tags')->save($data);
                if ($boolean !== false) {
                    $this->success('数据编辑成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
                } else {
                    $this->error('数据编辑失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }
            
        } else {
            
            // 条件：关联此用户对应的信息
            $where['mid'] =  session('mid');
            $data = $this->Model->where($where)->find(I('get.id',0,'int'));
            
            empty($data) AND $this->error('此信息不存在或者已被删除！');
            empty($data['isok']) OR $this->error('此信息已经鉴定完毕，将不可编辑！');
            
            $this->data = $data;
            $this->display('Center:data_edit');
        }
    }

    /**
     * 删除信息
     */
    public function delete() {

        // 条件：关联此用户对应的信息
        $where['mid'] =  session('mid');
        $data = $this->Model->where($where)->find(I('get.id',0,'int'));

        empty($data) AND $this->error('此信息不存在或者已被删除！');
        empty($data['isok']) OR $this->error('此信息已经鉴定完毕，将不可删除！');
        
        $boolean = $this->Model->delete($data['id']);
        if ($boolean !== false) {
            $this->success('信息删除成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
        } else {
            $this->error('信息删除失败！');
        }
    }
    
    /**
     * 批量操作
     */
    public function batch() {
        
        // 获取批量操作的ID集合
        $ids = I('post.ids','','trim');
        
        // 判断是否是空值
        empty($ids) AND $this->error('请至少选择一项数据！');
        
        // 删除操作
        $where['id'] = array('in', $ids);
        $where['isok'] = 0;
        $where['mid'] =  session('mid');
        
        $boolean = $this->Model->where($where)->delete();
        if ($boolean !== false) {
            $this->success('数据删除成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
        } else {
            $this->error('数据删除失败！');
        }
    }


    /**
     *评论添加鉴定评论
     */
    public function comment(){
        $content = I('content','','strip_tags');
        if (strlen($content)>5){
            $identifyid = I('goodsid');
            $model = D('IdentifyComment');
            $model->mid = session('mid');
            if($model->where(array('mid'=>$model->mid,'content'=>$content,'identifyid'=>$identifyid))->find()){
                $this->success('评论成功');
            }
            else{
                $model->content = $content;
                $model->identifyid = $identifyid;
                $model->createtime = time();
                $model->mid = session('mid');
                $model->isopen = 1;
                if ($model->add()){
                    $this->success('评论成功');
                }
                else{
                    $this->error("评论失败");
                }
            }
        }
        else{
            $this->error("评论不少于6个字符");
        }
    }
}
