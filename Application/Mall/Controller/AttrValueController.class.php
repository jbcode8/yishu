<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 属性名 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.29
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class AttrValueController extends AdminController {

    // 列表信息
    public function index(){
        $list = $this->lists('AttrValue',array(),'attr_id ASC');
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除之前的判断
    public function _before_delete(){

        // 判断品牌ID是否存在
        $attr_val_id = I('get.attr_val_id', '', 'intval');
        empty($attr_val_id) AND $this->error('属性值不存在或者已经被删除!');

        // 检测类别是否关联产品
        $Count = D('GoodsAttr')->where(array('attr_val_id' => $attr_val_id))->count();
        $Count > 0 AND $this->error('此属性值下关联有产品，请先确定此属性值下没有产品后再删除！');
    }

}