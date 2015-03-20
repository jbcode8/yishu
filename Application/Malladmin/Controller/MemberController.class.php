<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Controller;


class MemberController extends AdminController {

    public function index(){
        $pre = C('DB_PREFIX');
        $model = M()->table($pre.'member_mall m')->join($pre.'ucenter_member u ON m.uid=u.id');
        $list = parent::lists($model,null,null,null,'m.uid,u.username,m.nickname,m.birthday,m.status,m.last_login_ip,m.last_login_time,m.login');
        $this->assign('_list',$list);
        $this->display();
    }

    /**
     * 添加会员
     * @param string $username
     * @param string $password
     * @param string $repassword
     * @param string $email
     */
    public function add($username = '', $password = '', $repassword = '', $email = ''){
        if(IS_POST){
            /* 检测密码 */
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }

            /* 调用注册接口注册用户 */
            $User = new \User\Api\UserApi();
            $uid = $User->register($username, $password, $email);
            if(0 < $uid){ //注册成功
                $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1);
                if(!M('MemberMall')->add($user)){
                    $this->error('会员添加失败！');
                } else {
                    $this->success('会员添加成功！');
                }
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
            $this->display('AdminMember/add');
        }
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            default:  $error = '未知错误';
        }
        return $error;
    }

    /**
     * 禁用会员
     */
    public function disable(){
        $uid = I('uid','','intval');
        if(!$uid) $this->error('参数错误!');
        $this->forbid('Member','uid');
    }

    /**
     * 启用会员
     */
    public function enabled(){
        $uid = I('uid','','intval');
        if(!$uid) $this->error('参数错误!');
        $this->resume('Member','uid',array(),array('success'=>'状态启用成功!','error'=>'状态启用失败!'));
    }
} 