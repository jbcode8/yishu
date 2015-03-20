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
        'ucenter_member'=>array(
            'mapping_type'    => self::HAS_ONE,
            'class_name'    =>'ucenter_member',
            'foreign_key'=>'id',
            'as_fields'=>'username,email,mobile,salt',
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
        //$user = $this->field(true)->find($uid);
		
        //$user = M('Member')->field(true)->where("uid=".$uid)->find();
		$user=M()->query("select * from yishu_member where uid=$uid limit 1");
		$user=$user[0];
		//echo "<pre>";
		//print_r($user);
		//echo "ee";
		//echo $this->getLastSql();exit;
        if(!$user || 1 != $user['status']) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }

        /* 登录用户 */
		//print_r($remember);exit;
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
		//echo "ee";
		//print_r($user);exit;
        //$this->save($data);
		//echo $this->getLastSql();exit;
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid'             => $user['uid'],
            'username'        => memberInfo($user['uid'],'username'),
            'nickname'        => $user['nickname'],
            'last_login_time' => $user['last_login_time'],
        );
		/*$auth = array(
            'uid'             => $user['id'],
            'username'        => memberInfo($user['id'],'username'),
            'nickname'        => $user['nickname'],
            'last_login_time' => $user['last_login_time'],
        );*/
		//print_r($auth);exit;
        if($remember){
            $UcenterMember = new UcenterMemberModel();
            $UcMe = $UcenterMember->field('salt')->find($user['uid']);
			//echo $this->getLastSql();exit;
            cookie('uid',$user['uid'],array('expire'=>3600*24*7));
            cookie('user_auth',ucenter_encrypt(serialize($auth),$UcMe['salt']),array('expire'=>3600*24*7));
        }
        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
		//print_r(session('user_auth'));exit;
    }

    public function getNickName($uid){
        return $this->where(array('uid'=>(int)$uid))->getField('nickname');
    }

}
