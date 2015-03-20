<?php
/**
 * InfoController.class.php
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 sparui. All rights reserved.
 * @link          http://www.sparui.com
 * @license       http://www.sparui.com/license
 */

namespace Member\Controller;


class InfoController extends MemberController{
    public function index(){
        $mUser = D('Member')->field('status,last_login_time,last_login_ip',true)->relation(true)->find(UID);
        $this->muser = $mUser;
        $this->city = $this->_exarray($mUser['city']);
        $this->one = $this->_exarray($mUser['cate']);
        $this->two = $this->_exarray($mUser['hobby']);
        $this->twoother = ! is_numeric($this->two[count($this->two)-1]) ? $this->two[count($this->two)-1] : '';
        $this->three = $this->_exarray($mUser['describe']);
        $this->threeother = ! is_numeric($this->three[count($this->three)-1]) ? $this->three[count($this->three)-1] : '';
        $this->display();
    }
    //基本资料
    public function userinfo(){
        header("Content-type: text/html; charset=utf-8"); 
        if(! IS_POST){
            die("<script>alert('非法操作...');history.go(-1);</script>");
        }
        if(! I('post.nickname')){
            exit("<script>alert('请输入昵称...');history.go(-1);</script>");
        }
        if(! I('post.sex')){
            exit("<script>alert('请选择性别...');history.go(-1);</script>");
        }
        if(! I('post.year') || ! I('post.month') || ! I('post.date')){
            exit("<script>alert('请选择出生日期...');history.go(-1);</script>");
        }
        if(! I('post.sel_pro') || ! I('post.sel_city')){
            exit("<script>alert('请选择所在地...');history.go(-1);</script>");
        }
        $user = M('member')->field('uid')->where(array('nickname'=>I('post.nickname')))->find();
        if($user && $user['uid'] != UID){
            exit("<script>alert('该昵称已经存在...');history.go(-1);</script>");
        }
        $data = array(
            'nickname'    => I('post.nickname'),
            'sex'         => I('post.sex'),
            'birthday'    => strtotime(I('post.year') . '-' . I('post.month') . '-' . I('post.date')), 
            'city'        => I('post.sel_pro') . ',' . I('post.sel_city') . ',' . I('post.sel_country'),
            'realname'    => I('post.realname'),
            'qq'          => I('post.qq'),
            'ucenter_member' => array(
                'username'    => I('post.nickname'),
                'email'       => I('post.email'),
                'mobile'       => I('post.mobile'),
            )
        );
        if(D('Member')->relation(true)->where(array('uid'=>UID))->save($data)){
            exit("<script>alert('修改成功...');self.location=document.referrer;</script>");
        }else{
            exit("<script>alert('修改失败或内容没有变化...');history.go(-1);</script>");
        }
    }
    //密码
    public function editpwd(){
        header("Content-type: text/html; charset=utf-8"); 
        if(! IS_POST){
            die("<script>alert('非法操作...');history.go(-1);</script>");
        }
        $password = trim(I('post.password'));
        if(! $password){
            exit("<script>alert('请输入原密码...');history.go(-1);</script>");
        }
        if(! M('ucenter_member')->where(array('id' => UID,'password' => md5($password . I('post.ss'))))->find()){
            exit("<script>alert('原密码不正确...');history.go(-1);</script>");
        }
        $password1 = trim(I('post.password1'));
        $password2 = trim(I('post.password2'));
        if(! $password1 || ! $password2){
            exit("<script>alert('请输入新密码...');history.go(-1);</script>");
        }
        if($password1 != $password2){
            exit("<script>alert('两次新密码输入不一致...');history.go(-1);</script>");
        }
        if(M('ucenter_member')->where(array('id' => UID))->setField('password', md5($password1 . I('post.ss')))){
            exit("<script>alert('修改成功...');history.go(-1);</script>");
        }else{
            exit("<script>alert('修改失败或新密码与原密码一致...');history.go(-1);</script>");
        }
    }
    //邮箱
    public function editemail(){
        header("Content-type: text/html; charset=utf-8"); 
        if(! IS_POST){
            die("<script>alert('非法操作...');history.go(-1);</script>");
        }
        $password = trim(I('post.password'));
        if(! $password){
            exit("<script>alert('请输入密码...');history.go(-1);</script>");
        }
        if(! M('ucenter_member')->where(array('id' => UID,'password' => md5($password . I('post.ss'))))->find()){
            exit("<script>alert('密码不正确...');history.go(-1);</script>");
        }
        $email = trim(I('post.email'));
        if(! preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email)){
            exit("<script>alert('邮箱格式不正确...');history.go(-1);</script>");
        }
        if(M('ucenter_member')->where(array('id' => UID))->setField('email', $email)){
            exit("<script>alert('修改成功...');history.go(-1);</script>");
        }else{
            exit("<script>alert('修改失败或原邮箱没有变化...');history.go(-1);</script>");
        }
    }
    public function editother(){
        header("Content-type: text/html; charset=utf-8"); 
        if(! IS_POST){
            die("<script>alert('非法操作...');history.go(-1);</script>");
        }
        $cate = implode(',', I('post.cate'));
        $hobby = implode(',', I('post.hobby'));
        $describe = implode(',', I('post.describe'));
        $data = array(
            'cate' => $cate,
            'hobby' => $hobby,
            'describe' => $describe,
        );
        if(M('member')->where(array('uid' => UID))->setField($data)){
            exit("<script>alert('修改成功...');self.location=document.referrer;</script>");
        }else{
            exit("<script>alert('修改失败或原数据没有变化...');history.go(-1);</script>");
        }
    }
    private function _exarray($array){
        return explode(',', $array);
    }
    public function base(){
        $this->display();
    }
}