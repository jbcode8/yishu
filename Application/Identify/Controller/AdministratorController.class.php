<?php

/**
 * 后台基础类
 * @author             Ethan Lu <838777565@qq.com>
 * @createdate		2013-6-2
 */
namespace Identify\Controller;
class AdministratorController extends AppframeController {

    public function _initialize() {
        self::checkLogin();
        self::checkAccess();
        define('IN_ADMIN', true);
        C('PAGE_NUMS',20);
        C('URL_MODEL',0);
        C('URL_ROUTER_ON',false);
        C('APP_SUB_DOMAIN_DEPLOY',false);
    }

    /**
     * 检查后台是否登录 (如admin/login/index登录页面则不进行验证,否则跳转登录页面)
     * @return boolean
     */
    final public function checkLogin() {
        if (__MODULE__ == 'Admin' && __CONTROLLER__ == 'Login' && in_array(__ACTION__, array('dologin','index'))) {
            return true;
        } else {
            if (!session('userid') || !session('roleid'))
                $this->redirect('Admin/Login/index');
        }
    }

    /**
     * 权限判断
     */
    final public function checkAccess() {
        //判断是否为登录页面
        if (__MODULE__ == 'Admin' && in_array(__CONTROLLER__, array('Index','Login')))
            return true;
        //判断是否为超级管理员组 1为系统固定权限组
        if (session('roleid') == 1)
            return true;
        $db = M('RolePriv');
        $return = $db->where(array('app' => __MODULE__, 'mod' => __CONTROLLER__, 'act' => __ACTION__, 'roleid' => session('roleid')))->select();
        if (!$return)
            $this->error('您没有此操作的权限!');
    }
    
    /**
     * 管理菜单权限过虑
     */
    final public static function admin_menu(){
        //判断缓存是否存在
        if(!S('Menu')){
            D('Menu')->caches();
        }
        //获取菜单缓存
        $menu = S('Menu');
        //判断是否超级管理员,如是则返回所有菜单
        if(session('roleid')==1)return $menu;
        //否则进行权限过虑
        $role = D('RolePriv')->where(array('roleid' => session('roleid')))->select();
        foreach ($menu as $k => $v) {
            foreach ($role as $kr => $vr) {
                if($vr['app']==$v['app'] && $vr['mod'] == $v['mod'] && $vr['act'] == $v['act'] && $vr['data'] == $v['data']){
                    $menus[$k] = $v;
                }
            }
        }
        return $menus;
    }
    /**
     * 添加数据
     */
    public function add() {
        if(isset($_POST['submit'])){
            $Model = $this->Model?$this->Model:D($this->getActionName());
            if ($v = $Model->create()) {
                $list = $Model->add();
                if ($list !== false) {
                    $this->success('数据保存成功！',U(__MODULE__.'/'.__CONTROLLER__.'/index'));
                } else {
                    $this->error('数据保存失败！');
                }
            } else {
                $this->error($Model->getError());
            }
        }else{
            $this->display();
        }
    }
    /**
     * 删除记录  get单条记录,post多条记录批量删除
     */
    public function delete(){
        $Model = $this->Model?$this->Model:D($this->getActionName());
        $pk = $Model->getPk();
        if(IS_POST){
            $map[$pk] = array('IN',I('post.'.$pk));
            if(I('post.'.$pk)){
                if($Model->where($map)->delete() !== FALSE){
                    $this->success('删除成功!');
                }else{
                    $this->error('删除失败!');
                }
            }else{
                $this->error('请选中需要删除的记录!');
            }
        }else{
            $id = I('get.'.$pk);
            if (!empty($id)) {
                $result = $Model->delete(intval($id));
                if (false !== $result) {
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            }else{
                $this->error('参数错误!');
            }
        }
    }
    
    /**
     * 编辑记录
     * @param int $id
     */
    public function edit() {
        if(isset($_POST['submit'])){
            $this->update();
        }else{
            $Model = $this->Model?$this->Model:D($this->getActionName());
            $pk = $Model->getPk();
            $id = I('get.'.$pk);
            if (!empty($id)) {
                $vo = $Model->find($id);
                if ($vo) {
                    $this->v = $vo;
                    $this->display();
                } else {
                    $this->error('数据不存在！');
                }
            } else {
                $this->error('参数有误!');
            }
        }
    }
    
    /**
     * 更新记录
     */
    public function update() {
        $Model = $this->Model?$this->Model:D($this->getActionName());
        if ($v = $Model->create()) {
            $list = $Model->save();
            if ($list !== false) {
                $this->success('数据更新成功！',U(__MODULE__.'/'.__CONTROLLER__.'/index'));
            } else {
                $this->error("没有更新任何数据!");
            }
        } else {
            $this->error($Model->getError());
        }
    }
    
    /**
     * 更新排序
     */
    public function listorder() {
        $model = $this->Model?$this->Model:D($this->getActionName());
        $pk = $model->getPk();
        foreach ($_POST['listorder'] as $id => $v) {
            $condition = array($pk => $id);
            $model->where($condition)->setField('listorder', $v);
        }
        $this->success('更新排序成功');
    }
}