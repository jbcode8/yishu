<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 属性类型 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.29
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class AttributeController extends AdminController {

    // 列表信息
    public function index(){
        $list = $this->lists('Attribute');
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除之前的判断
    public function _before_delete(){

        // 判断品牌ID是否存在
        $attr_id = I('get.attr_id', '', 'intval');
        empty($attr_id) AND $this->error('属性类型不存在或者已经被删除!');

        // 检测类别是否关联产品
        $Count = D('AttrValue')->where(array('attr_id' => $attr_id))->count();
        $Count > 0 AND $this->error('此属性类型下关联有属性值，请先确定此属性类型下没有属性值后再删除！');
    }

}