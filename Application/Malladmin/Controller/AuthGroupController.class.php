<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Controller;


class AuthGroupController extends AdminController{

    public function index(){
        $map['module'] = 'admin';
        $list = parent::lists('AuthGroup',$map);
        $this->assign('_list',$list);
        $this->display();
    }

    /**
     * 给管理员分配权限组
     */
    public function authorization(){
        if(IS_POST){
            $uid = I('uid');
            $gid = I('group_id');
            if( empty($uid) ){
                $this->error('参数有误');
            }
            $AuthGroup = D('AuthGroupMall');
            if(is_numeric($uid)){
                if ( is_administrator($uid) ) {
                    $this->error('该用户为超级管理员');
                }
                if( !M('AdminMall')->where(array('uid'=>$uid))->find() ){
                    $this->error('管理员用户不存在');
                }
            }

            if( $gid && !$AuthGroup->checkGroupId($gid)){
                $this->error($AuthGroup->error);
            }
            if ( $AuthGroup->addToGroup($uid,$gid) ){
                $this->success('操作成功');
            }else{
                $this->error($AuthGroup->getError());
            }

        }else{
            $uid = I('get.uid','','intval');
            if(empty($uid)){
                $this->error('参数有误!');
            }
            $map['uid'] = $uid;
            $user_group_data = M('AuthGroupAccessMall')->where($map)->getField('group_id',true);
            $user_group = array();
            if(is_array($user_group_data)){
                foreach($user_group_data as $v){
                    $user_group[] = $v;
                }
            }
            $groupList = M('AuthGroupMall')->field('id,title')->where(array('module'=>'admin'))->select();
            $this->assign('_groupList',$groupList);
            $this->assign('user_group',implode(',',$user_group));
            $this->display();
        }
    }

    /**
     * 成员授权
     * @param $group_id
     */
    public function groupmember($group_id){
        $group = M('AuthGroupMall')->field('title')->find($group_id);

        $admin_table  = C('DB_PREFIX').'admin_mall';
        $group_table  = C('DB_PREFIX').'auth_group_access_mall';
        $model = M()->table($admin_table.' a')
            ->join($group_table.' g ON a.uid = g.uid');

        $list = parent::lists($model,array(),'a.uid asc',array('a.status'=>1),'a.uid,a.nickname,a.last_login_ip,a.last_login_time,a.status');
        $this->assign('_list',$list);
        $this->assign('groupname',$group['title']);
        $this->display();
    }

    /**
     * 解除授权
     * @param $uid 管理员id
     * @param $group_id 用户组id
     */
    public function removeauth($uid,$group_id){
        if(M('AuthGroupAccessMall')->where(array('uid'=>$uid,'group_id'=>$group_id))->delete()){
            $this->success('解除授权成功!');
        }else{
            $this->error('解除授权失败!');
        }
    }

    /**
     * 栏目授权
     * TODO 还没写完,后续需要补全!!!!!
     */
    public function column(){

    }

    /**
     * 访问授权
     */
    public function access(){
        if(IS_POST){
            $data['id'] = I('post.id','','intval');
            $data['rules'] = $_REQUEST['rules'];
            if(M('AuthGroupMall')->save($data) !== false){
                $this->success('授权成功!');
            }else{
                $this->error('授权失败!');
            }
        }else{
            $rule = M('AuthRuleMall')->field('id,pid as pId,title as name')->where(array('module'=>'admin','status'=>1))->order('listorder asc,id asc')->select();
            $groupRule = M('AuthGroupMall')->field('rules')->find((int)$_REQUEST['id']);
            $ruleArr = explode(',',$groupRule['rules']);
            foreach($rule as $k=>$v){
                if(in_array($v['id'],$ruleArr)){
                    $rule[$k]['checked'] = true;
                }
            }
            $this->assign('rules',json_encode($rule));
            $this->display();
        }
    }

    /**
     * 添加前传入module参数
     */
    public function _before_add(){
        $_POST['module'] = 'admin';
    }

    /**
     * 禁用管理员组
     */
    public function disable(){
        parent::forbid('AuthGroup','id');
    }

    /**
     * 启用管理员组
     */
    public function enabled(){
        parent::resume('AuthGroup','id');
    }
} 