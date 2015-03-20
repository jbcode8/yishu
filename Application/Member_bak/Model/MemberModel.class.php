<?php
// +----------------------------------------------------------------------
// | 用户模型
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Member\Model;
use Think\Model\RelationModel;
use User\Api\UserApi;
use User\Model\UcenterMemberModel;

/**
 * 用户模型
 * @package Home\Model
 */
class MemberModel extends RelationModel {

    protected $_link = array(
        'UcenterMember'=>array(
            'mapping_type'    => HAS_ONE,
            'class_name'    =>'UcenterMember',
            'foreign_key'=>'id',
            'as_fields'=>'username,email,mobile',
        ),
    );


    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @param bool $remember 记住密码
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($uid,$remember=false){
        /* 检测是否在当前应用注册 */
        $user = $this->field(true)->find($uid);
        if(!$user || 1 != $user['status']) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }

        /* 登录用户 */
        $this->autoLogin($user,$remember);
        return true;
    }

    /**
     * 前台用户注册
     * @param $username
     * @param $password
     * @param $email
     * @param $mobile
     * @param string $nickname
     * @return int|mixed|string
     */
    public function register($username,$password,$email=null,$mobile='',$nickname=''){
        $UcenterMember = new UserApi();
        $uid = $UcenterMember->register($username,$password,$email,$mobile);
        if($uid > 0){
            $data['uid'] = $uid;
            $data['nickname'] = $nickname;
            if($this->add($data)){
                return $uid;
            }else{
                return $this->getError();
            }
        }else{
            return $uid;
        }
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('[destroy]');
        cookie('uid',null);
        cookie('user_auth',null);
    }

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     * @param $remember 记住密码
     */
    private function autoLogin($user,$remember){
        /* 更新登录信息 */
        $data = array(
            'uid'             => $user['uid'],
            'login'           => array('exp', '`login`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->save($data);

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid'             => $user['uid'],
            'username'        => memberInfo($user['uid'],'username'),
            'nickname'        => $user['nickname'],
            'last_login_time' => $user['last_login_time'],
        );
        if($remember){
            $UcenterMember = new UcenterMemberModel();
            $UcMe = $UcenterMember->field('salt')->find($user['uid']);
            cookie('uid',$user['uid'],array('expire'=>3600*24*7));
            cookie('user_auth',ucenter_encrypt(serialize($auth),$UcMe['salt']),array('expire'=>3600*24*7));
        }
        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
    }

    public function getNickName($uid){
        return $this->where(array('uid'=>(int)$uid))->getField('nickname');
    }

}
