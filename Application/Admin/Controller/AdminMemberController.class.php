<?php
// +----------------------------------------------------------------------
// | AdminMemberController
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;
/**
 * 后台用户管理
 */
class AdminMemberController extends AdminController{


    /**
     * 管理员列表
     */
    public function index(){
        $lists   = parent::lists('Admin');
        $this->assign('_list', $lists);
        $this->display();
    }

    /**
     * 添加后台管理员
     * @param string $username
     * @param string $password
     * @param string $repassword
     * @param string $email
     */
    public function add($username = '', $password = '', $repassword = '', $email = '', $admintype='', $goods_sellerid=''){
        //商家
		$this->seller = M('PaimaiSeller')->field("seller_id,seller_name")->where("seller_isshow=0")->order("seller_id asc")->select();
        if(IS_POST){
            /* 检测密码 */
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }

            /* 调用注册接口注册用户 */
			//print_r($goods_sellerid);exit;
            $User = new UserApi;
            $uid = $User->register($username, $password, $email, '', $admintype, $goods_sellerid);
            if(0 < $uid){ //注册成功
                $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1);
                if(!M('Admin')->add($user)){
                    $this->error('管理员添加失败！');
                } else {
                    $this->success('管理员添加成功！');
                }
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
            $this->display();
        }
    }

    public function delete($model = null){
        parent::delete('Admin');
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
     * 禁用管理员
     */
    public function disable(){
        $uid = I('uid','','intval');
        if(!$uid) $this->error('参数错误!');
        $uid == C('USER_ADMINISTRATOR') && $this->error('超级管理员无法禁用!');
        $this->forbid('Admin','uid');
    }

    /**
     * 启用管理员
     */
    public function enabled(){
        $uid = I('uid','','intval');
        if(!$uid) $this->error('参数错误!');
        $uid == C('USER_ADMINISTRATOR') && $this->error('无法对超级管理员操作!');
        $this->resume('Admin','uid',array(),array('success'=>'状态启用成功!','error'=>'状态启用失败!'));
    }

    /**
     * 删除前判断是否为超级管理员
     */
    public function _before_delete(){
        $uid = I('uid','','intval');
        if(!$uid) $this->error('参数错误!');
        $uid == C('USER_ADMINISTRATOR') && $this->error('无法删除超级管理员!');
    }

    public function orgmember($username=''){
        if(IS_POST){
            $username || $this->error('用户名不能为空!');
            $UserApi = new UserApi();
            $info = $UserApi->info($username,true);
            if(!is_array($info))
                $this->error('无此用户!');
            list($uid,$username) = $info;
            $user = array('uid' => $uid, 'nickname' => $username, 'status' => 1);
            $adminModel = D('Admin');
            $returnData = $adminModel->field('uid')->find($uid);
            if($returnData['uid'] != $uid){
                if(!M('Admin')->add($user)){
                    $this->error('管理员添加失败！');
                } else {
                    $this->success('管理员添加成功！');
                }
            }else{
                $this->error('管理员'.$username.'已存在!');
            }

        }else{
            $this->display();
        }
    }
}