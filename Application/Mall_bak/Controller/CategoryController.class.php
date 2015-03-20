<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 类别 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.24
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class CategoryController extends AdminController {

    public function index(){
        
        // 读取分类缓存数据
        $list = D('Category')->getCategoryCache();
        
        // 数组重构
        foreach($list as $v){
            $tablelist[$v['cate_id']] = $v;
            $tablelist[$v['cate_id']]['pid']   = $v['parent_id'];
            $tablelist[$v['cate_id']]['pid_node'] = ($v['parent_id']) ? 'data-tt-parent-id="'.$v['parent_id'].'"': '';
            $tablelist[$v['cate_id']]['manage'] = "".PHP_EOL.
                "<a class=\"fa fa-edit\" href=\"javascript:$.dialog.open('".U('Mall/Category/editattr?cate_id='.$v['cate_id'])."',{title:'修改商品属性',id:'doCategory'});\"> 修改商品属性</a>".PHP_EOL.
                "<a class=\"fa fa-plus-square\" href=\"javascript:$.dialog.open('".U('Mall/Category/add?parent_id='.$v['cate_id'])."',{title:'添加子类别',id:'doCategory'});\"> 添加子类别</a>".PHP_EOL.
                "<a class=\"fa fa-edit\" href=\"javascript:$.dialog.open('".U('Mall/Category/edit?cate_id='.$v['cate_id'])."',{title:'修改分类',id:'doCategory'});\"> 修改分类</a>".PHP_EOL.
                "<a class=\"fa fa-trash-o red ajax-get\" href=\"".U('Mall/Category/delete?cate_id='.$v['cate_id'])."\"> 删除</a>";
        }
        
        // 树型结构处理
        $menu = new \Org\Util\Tree();
        $menu->icon = array('┃','┣','┗');
        $menu->nbsp = "&nbsp;&nbsp;&nbsp;";
        $str = "<tr data-tt-id='\$id' \$pid_node>".PHP_EOL.
            "<td style='padding-left:10px;width:150px;text-align:left'>".
            "<input type='text' name='listorder[\$id]' value='\$listorder' style='width:20px;height:18px;padding:0 4px' /></td>".PHP_EOL.
            "<td style='width:120px'>\$id</td>".PHP_EOL.
            "<td style='text-align:left;'>\$spacer \$cate_name</td>".PHP_EOL.
            "<td style='width:300px'>\$manage".PHP_EOL."</td>".PHP_EOL."</tr>".PHP_EOL;
        
        $menu->init($tablelist);
        $this->list = $menu->get_tree(0, $str);
        $this->display();
    }

    // 添加和修改类别的属性类型
    public function editattr(){

        // 读取信息
        $cate_id = I('get.cate_id','','intval');
        $this->info = D('Category')->field('cate_id, cate_name, parent_id, attribute')->find($cate_id);
        $this->display();
    }

    // 删除之前的判断
    public function _before_delete(){
        
        // 获取删除的类别ID
        $cate_id = I('get.cate_id', '', 'intval');
        
        // 判断分类是否存在
        if(empty($cate_id)){
            $this->error('分类不存在!');
        }
        
        // 检测是否是最底层的类别
        $subIds = D('Category')->getSubCategoryId($cate_id);
        if(!empty($subIds)){
            $this->error('此分类下存在子分类，请先确定此分类下没有子分类后再删除!');
        }
        
        // 检测类别是否关联产品
        $goodsCount = D('CateGoods')->where(array('cate_id' => $cate_id))->count();
        if($goodsCount > 0){
            $this->error('此分类下关联有产品，请先确定此分类下没有产品后再删除！');
        }
        
        // 检测是否关联店铺
        
        // 检测是否关联品牌
        
        // 检测是否关联属性
    }
    
}