<?php
// +----------------------------------------------------------------------
// | ModelController.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Controller;


class ModelController extends AdminController {

    /**
     *模型列表
     */
    public function index(){
        $map = array('status'=>array('gt',-1));
        $list = $this->lists('Model',$map);
        $this->assign('_list', $list);
        $this->display();
    }

    /**
     * 添加前可继承模型列表
     */
    public function _before_add(){
        $extendList = D('ModelMall')->getExtendList();
        $this->assign('extendList',$extendList);
    }

    /**
     * 编辑前可继承模型列表
     */
    public function _before_edit(){
        $extendList = D('ModelMall')->getExtendList();
        $this->assign('extendList',$extendList);
    }

    /**
     * 禁用模型
     */
    public function disable(){
        parent::forbid('Model','id');
    }

    /**
     * 启用模型
     */
    public function enabled(){
        parent::resume('Model','id');
    }
} 