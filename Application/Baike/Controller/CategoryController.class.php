<?php
// +----------------------------------------------------------------------
// | 艺术百科分类控制器文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Controller;
use Admin\Controller\AdminController;


class CategoryController extends AdminController{
    /**
     * 百科分类的树形结构表
     */
    public function index(){
        //分类导航判断
        $where = I('get.navigation',0,'intval') ? array('navigation' => 1) : array();

        $list = $this->lists('Category',$where,'listorder','',true,false);
        $tablelist = array();
        foreach($list as $k=>$v){
            $manage= "<a class='fa fa-edit' href=\"javascript:$.dialog.open('".U('Baike/Category/edit?cid='.$v['cid'])."',{title:'编辑分类',lock:true})\"> 修改</a> <a href='".U('Baike/Category/delete?cid='.$v['cid'])."' class='ajax-get confirm fa fa-trash-o'> 删除</a>";
            //加入转移/子类操作
            $mobile = " <a href=\"javascript:$.dialog.open('".U('Baike/Category/move?pid='.$v['pid'].'&cid='.$v['cid'].'&name='.$v['name'])."',{title:'转移分类',lock:true})\" class='fa fa-move'> 分类转移</a>";
            $add = " <a class=\"fa fa-plus-square\" href=\"javascript:$.dialog.open('".U('Baike/Category/add?pid='.$v['cid'])."',{title:'添加分类',lock:true})\"> 添加子类</a>";
            $manage .= $v['pid'] == 0 ? $add : $mobile;
            //加入导航操作
            $addNavigation = U("Baike/Category/alternavigation?cid=".$v['cid']."&navigation=1");
            $delNavigation = U("Baike/Category/alternavigation?cid=".$v['cid']."&navigation=0");
            $manage .= $v['navigation'] ? " <a href=\"".$delNavigation."\"  class='ajax-get fa fa-times'> 取消导航</a>" : " <a href=\"".$addNavigation."\" class='ajax-get fa fa-check'> 设为导航</a>";

            $tablelist[$v['cid']] = $v;
            $tablelist[$v['cid']]['pid_node'] = ($v['pid']) ? 'data-tt-parent-id="'.$v['pid'].'"': '';
            $tablelist[$v['cid']]['manage'] = $manage;
        }
        //树型结构处理
        $menu = new \Org\Util\Tree();
        $menu->icon = array('┃','┣','┗');
        $menu->nbsp = "&nbsp;&nbsp;&nbsp;";
        $str = "<tr data-tt-id='\$cid' \$pid_node>
                       <td style='padding-left:10px;width:150px;text-align: left'><input type='text' name='listorder[\$id]' value='\$listorder' size='3'/></td>
                       <td style='width:80px'>\$cid</td>
                       <td style='width:120px; text-align: left;'>\$spacer \$name</td>
                       <td style='width:100px;'>\$docs</td>
                       <td style='text-align: left;'>\$description</td>
                       <td style='width: 260px'>\$manage</td>
                </tr>";
        $menu->init($tablelist);
        $this->list = $menu->get_tree(0, $str);
        $this->display();
    }

    /**
     * 导航设置方法
     */
    public function alternavigation(){
        $data['cid'] = I('get.cid');
        $data['navigation'] = I('get.navigation');
        if(D('Category')->save($data))
            $this->success('修改成功！');
        else
            $this->error('修改失败！');
    }

    /**
     * 分类转移模板调用方法
     */
    public function _before_move(){
        If(!IS_POST){
            $this->pid = I('get.pid','','intval');
            $this->cid = I('get.cid',0,'intval');
            $this->name = I('get.name');
            $this->category = D('Category')->supercate();
            $this->display();
        }
    }

    /**
     * 分类转移执行方法
     */
    public function move(){
        if(IS_POST){
            parent::edit();
        }
    }

    /**
     * 分类删除
     */
    public function delete(){
        $cid = I('get.cid', 0, 'intval');
        if($cid){
            $childs = category_childids(D('Category')->category(),$cid);
            if($childs){
                $this->error('该类别下还有其他子级类别，不能删除，请先删除子类别');
                exit;
            }
            $docs = D('Category')->where(array('cid'=>$cid))->getField('docs');
            if($docs > 0){
               $this->error('该分类下还有 '.$docs.' 词条，不能删除');
            }
            else{
              if(D('Category')->delete($cid))
                $this-> success('删除成功');
              $this->error('删除失败');
            }
        }
        else{
            $this->error('参数错误');
        }
    }

}