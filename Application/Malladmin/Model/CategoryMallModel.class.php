<?php
// +----------------------------------------------------------------------
// | CategoryModel.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Model;
use Think\Model;

class CategoryMallModel extends Model {

    /**
     * 获取栏目列表
     * @return array
     */
    public function getList(){
        $list = $this->field('catid,name,title,pid,model,listorder,items')->select();
        return D('Common/Tree')->toFormatTree($list,'title','catid');
    }

    /**
     * 获取栏目树结构
     * @return string
     */
    public function getTree(){
        /* 获取所有分类 */
        $list = $this->order('listorder asc,catid asc')->getField('catid as id,name,title,pid,model,type,items,listorder,items');
        $tablelist = array();
        foreach($list as $k=>$v){
            $tablelist[$k] = $v;
            $tablelist[$k]['type'] = getCateType($v['type']);
            $tablelist[$k]['model'] = getModelName($v['model']);
            $tablelist[$k]['items'] = $v['items']==0?'':$v['items'];
            $tablelist[$k]['pid_node'] = ($v['pid']) ? 'data-tt-parent-id="'.$v['pid'].'"': '';
            $tablelist[$k]['manage'] = "<a class='fa fa-plus-square' href=\"javascript:$.dialog.open('".U('Admin/Category/add?catid='.$v['id'])."',{title:'添加子栏目'});\">添加子栏目</a>
                                        <a class='fa fa-edit' href=\"javascript:$.dialog.open('".U('Admin/Category/edit?catid='.$v['id'])."',{title:'修改栏目'});\">修改栏目</a>
                                        <a class='fa fa-trash-o confirm ajax-get' href=\"".U('Admin/Category/delete?catid='.$v['id'])."\">删除</a>";
        }
        //树型结构处理
        $menu = new \Org\Util\Tree();
        $menu->icon = array('┃','┣','┗');
        $menu->nbsp = "&nbsp;&nbsp;&nbsp;";
        $str = "<tr data-tt-id='\$id' \$pid_node>
                       <td style='width:50px;text-align: left;padding-left: 5px'><input type='text' name='listorder[\$id]' value='\$listorder' size='3'/></td>
                       <td>\$id</td>
                       <td>\$name</td>
                       <td style='text-align: left'>\$spacer \$title</td>
                       <td>\$type</td>
                       <td>\$model</td>
                       <td>\$items</td>
                       <td>\$manage</td>
                    </tr>";
        $menu->init($tablelist);
        return $menu->get_tree(0, $str);
    }

} 