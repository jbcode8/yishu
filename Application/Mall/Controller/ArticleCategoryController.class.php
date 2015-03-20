<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 文章类别 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.28
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class ArticleCategoryController extends AdminController {

    public function index(){

        // 读取分类缓存数据
        $list = D('ArticleCategory')->getCategoryCache();

        // 数组重构
        foreach($list as $v){
            $tablelist[$v['cate_id']] = $v;
            $tablelist[$v['cate_id']]['pid']   = $v['parent_id'];
            $tablelist[$v['cate_id']]['pid_node'] = ($v['parent_id']) ? 'data-tt-parent-id="'.$v['parent_id'].'"': '';
            $tablelist[$v['cate_id']]['manage'] = "".PHP_EOL.
                "<a class=\"fa fa-plus-square\" href=\"javascript:$.dialog.open('".U('Mall/ArticleCategory/add?parent_id='.$v['cate_id'])."',{title:'添加子类别',id:'doArticleCategory'});\"> 添加子类别</a>".PHP_EOL.
                "<a class=\"fa fa-edit\" href=\"javascript:$.dialog.open('".U('Mall/ArticleCategory/edit?cate_id='.$v['cate_id'])."',{title:'修改分类', id:'doArticleCategory'});\"> 修改分类</a>".PHP_EOL.
                "<a class=\"fa fa-trash-o red ajax-get\" href=\"".U('Mall/ArticleCategory/delete?cate_id='.$v['cate_id'])."\"> 删除分类</a>";
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
            "<td style='width:250px'>\$manage".PHP_EOL."</td>".PHP_EOL."</tr>".PHP_EOL;

        $menu->init($tablelist);
        $this->list = $menu->get_tree(0, $str);
        $this->display();
    }

    // 删除之前的判断
    public function _before_delete(){

        // 获取删除的类别ID
        $cate_id = I('get.cate_id', '', 'intval');

        // 判断分类是否存在
        empty($cate_id) AND $this->error('分类不存在!');

        // 检测是否是最底层的类别
        $subIds = D('ArticleCategory')->getSubCategoryId($cate_id);
        empty($subIds) OR $this->error('此分类下存在子分类，请先确定此分类下没有子分类后再删除!');

        // 检测类别是否关联文章
        $isLinkArticle = D('Article')->where(array('cate_id' => $cate_id))->count();
        $isLinkArticle > 0 AND $this->error('此分类下关联有文章，请先确定此分类下没有产品后再删除！');
    }
    
}