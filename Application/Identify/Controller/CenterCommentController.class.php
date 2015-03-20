<?php

// +----------------------------------------------------------------------
// | 鉴定模块_鉴定评论_[会员中心]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
class CenterCommentController extends MemberController {

   // 定义模型
    protected $Model;

    // 初始化
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('IdentifyComment');
        
        // 判断是否登录
        session('mid') OR $this->error('请先登录！');
    }

    /**
     * 鉴定信息
     */
    public function index() {
        
        // 条件：关联此用户对应的信息
        $where['mid'] =  session('mid');
        
        $this->page = $this->pages($where);// 此处的分页局限：必须使用$this->Model定义模型
        $this->list = $this->Model->where($where)->limit($this->p->firstRow.', '.$this->p->listRows)->order('id DESC')->select();

        $this->display('Center:comment_index');
    }
    
    /**
     * 详细信息
     */
     public function detail() {
         
        // 条件：关联此用户对应的信息
        
        $field = 'C.mid AS cMid, C.content, C.createtime AS cTime, C.isopen AS cOpen, D.*';
        $where = 'C.id ='.I('get.id',0,'int').' AND C.mid = '.session('mid');
        $data = $this->Model->table('bsm_identify_comment C')->join('bsm_identify_data D ON C.identifyid = D.id')->field($field)->where($where)->find();
        
        //empty($data) AND $this->error('此信息不存在或者已被删除！');
        
        $this->data = $data;
        $this->display('Center:comment_detail');
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
        $where['mid'] =  session('mid');
        
        $boolean = $this->Model->where($where)->delete();
        if ($boolean !== false) {
            $this->success('数据删除成功！', U(__MODULE__ . '/' . __CONTROLLER__ . '/index'));
        } else {
            $this->error('数据删除失败！');
        }
    }

}
