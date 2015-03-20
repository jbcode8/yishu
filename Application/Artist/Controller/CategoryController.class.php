<?php
// +----------------------------------------------------------------------
// | 大师频道艺术家分类控制器
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------
namespace Artist\Controller;


class CategoryController extends ArtistAdminController{


    /**
     * 类别列表
     */
    public function index(){
        $list = $this->lists('Category','','listorder','',true,false);
        $tablelist = array();
        foreach($list as $k=>$v){
            $manage= "<a class='fa fa-edit' href=\"javascript:$.dialog.open('".U('Artist/Category/edit?id='.$v['id'])."',{title:'编辑分类',lock:true})\"> 修改</a> <a href='".U('Artist/Category/delete?id='.$v['id'])."' class='ajax-get confirm fa fa-trash-o'> 删除</a>";
            if($v['status'] == 1){
                $manage .= " <a href='".U('Artist/Category/updateStatus?id='.$v['id'].'&status=0')."' class='ajax-get error fa fa-lock'> 锁定</a>";
            }
            else{
                $manage .= " <a href='".U('Artist/Category/updateStatus?id='.$v['id'].'&status=1')."' class='ajax-get success fa fa-unlock'> 解锁</a>";
            }
            $tablelist[$v['id']] = $v;
            $tablelist[$v['id']]['manage'] = $manage;
        }
        //树型结构处理
        $menu = new \Org\Util\Tree();
        $menu->icon = array('┃','┣','┗');
        $menu->nbsp = "&nbsp;&nbsp;&nbsp;";
        $str = "<tr data-tt-id='\$id' \$pid_node>
                       <td style='padding-left:10px;width:150px;text-align: left'><input type='number' required='required' name='listorder[\$id]' value='\$listorder' min='0' max='100'/></td>
                       <td style='width:80px'>\$id</td>
                       <td style='width:120px;'>\$spacer \$name</td>
                       <td style='width: 260px'>\$manage</td>
                </tr>";
        $menu->init($tablelist);
        $this->list = $menu->get_tree(0, $str);
        $this->display();

    }



} 