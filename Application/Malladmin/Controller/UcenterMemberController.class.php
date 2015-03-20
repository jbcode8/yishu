<?php
// +----------------------------------------------------------------------
// | UserController.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Controller;
use User\Api\UserApi;

class UcenterMemberController extends AdminController{

    /**
     * 修改个人信息
     */
    public function editinfo(){
        if(IS_POST){
            //获取参数
            $uid = UID;
            $nickname = I('post.nickname');
            $password = I('post.password');
            empty($nickname) && $this->error('请输入昵称');
            empty($password) && $this->error('请输入密码');

            //密码验证
            $User = new UserApi();
            $uid = $User->login($uid, $password, 4);
            ($uid == -2) && $this->error('密码不正确');

            $Admin = D('AdminMall');
            $data = $Admin->create(array('nickname'=>$nickname));
            if(!$data){
                $this->error($Admin->getError());
            }

            $res = $Admin->where(array('uid'=>$uid))->save($data);

            if($res){
                $user = session('user_auth');
                $user['username'] = $data['nickname'];
                session('user_auth', $user);
                session('user_auth_sign', data_auth_sign($user));
                $this->success('修改昵称成功！');
            }else{
                $this->error('修改昵称失败！');
            }
        }else{
            $this->nickname = M('AdminMall')->getFieldByUid(UID, 'nickname');
            $this->display();
        }

    }

    /**
     * 修改密码个人密码
     */
    public function editpwd(){
        if(IS_POST){
            //获取参数
            $uid        =   UID;
            $password   =   I('post.oldpassword');
            empty($password) && $this->error('请输入原密码');
            $data['password'] = I('post.password');
            empty($data['password']) && $this->error('请输入新密码');
            $repassword = I('post.repassword');
            empty($repassword) && $this->error('请输入确认密码');

            if($data['password'] !== $repassword){
                $this->error('您输入的新密码与确认密码不一致');
            }

            $Api = new UserApi();
            $res = $Api->updateInfo($uid, $password, $data);
            if($res['status']){
                $this->success('修改密码成功！');
            }else{
                $this->error($res['info']);
            }
        }else{
            $this->display();
        }
    }
} 