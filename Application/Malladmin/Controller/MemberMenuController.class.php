<?php
/**
 * 会员中心菜单
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 yishu. All rights reserved.
 */

namespace Malladmin\Controller;

class MemberMenuController extends AdminController{

    public function index(){
        //获取数据
        //lists 方法使用  请查阅 AdminController 下 lists 使用说明
        $list = $this->lists('MemberMenu','','listorder','',true,false);

        foreach($list as $k=>$v){
            $tablelist[$k] = $v;
            $tablelist[$k]['pid_node'] = ($v['pid']) ? 'data-tt-parent-id="'.$v['pid'].'"': '';
            $tablelist[$k]['manage'] = "<a class='fa fa-plus-square' href=\"javascript:$.dialog.open('".U('add?pid='.$v['id'])."',{title:'添加菜单'});\">添加子菜单</a>
                                        <a class='fa fa-edit' href=\"javascript:$.dialog.open('".U('edit?id='.$v['id'])."',{title:'修改菜单'});\">修改菜单</a>
                                        <a class='fa fa-trash-o confirm ajax-get' href=\"".U('delete?id='.$v['id'])."\">删除</a>";
        }
        //树型结构处理
        $menu = new \Org\Util\Tree();
        $menu->icon = array('┃','┣','┗');
        $menu->nbsp = "&nbsp;&nbsp;&nbsp;";
        $str = "<tr data-tt-id='\$id' \$pid_node>
                       <td style='padding-left:10px;width:150px;text-align: left'><input type='text' name='listorder[\$id]' value='\$listorder' size='3'/></td>
                       <td style='width:80px'>\$id</td>
                       <td style='text-align: left;'>\$spacer \$name</td>
                       <td style='width: 200px'>\$manage</td>
                    </tr>";
        $menu->init($tablelist);
        $this->list = $menu->get_tree(0, $str);
        $this->display();
    }
} 