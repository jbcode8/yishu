<?php
// +----------------------------------------------------------------------
// | 用户模型
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Model;
use Think\Model;

/**
 * 用户模型
 * @package Admin\Model
 */
class AdminMallModel extends Model {

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($uid){
        /* 检测是否在当前应用注册 */
        $user = $this->field(true)->find($uid);
        if(!$user || 1 != $user['status']) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }

        /* 登录用户 */
        $this->autoLogin($user);
        return true;
    }

    /**
     * 删除管理员后自动删除对应的权限
     * @param $data
     * @param $option
     */
    protected function _after_delete($data,$option){
        $uid = $data['uid'];
        M('AuthGroupAccess')->where(array('uid'=>$uid))->delete();
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('admin_auth', null);
        session('admin_auth_sign', null);
    }

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
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
            'nickname'        => $user['nickname'],
            'last_login_time' => $user['last_login_time'],
        );

        session('admin_auth', $auth);
        session('admin_auth_sign', data_auth_sign($auth));

    }

    public function getNickName($uid){
        return $this->where(array('uid'=>(int)$uid))->getField('nickname');
    }
}
