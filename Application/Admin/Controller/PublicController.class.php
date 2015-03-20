<?php
// +----------------------------------------------------------------------
// | PublicController.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Think\Controller;
use User\Api\UserApi;

/**
 * 登录处理,验证码
 * @package Admin\Controller
 */
class PublicController extends Controller {

    /**
     * 后台系统登录入口
     * @param string $username  接收 post 提交的用户名
     * @param string $password  接收 post 提交的密码
     * @param string $verify    接收 post 验证码
     * @return void
     */
    public function login($username = null, $password = null, $verify = null){
        //是否 post 提交
        if(IS_POST){
            /*echo "<pre>";
            print_r($_POST);
            exit;*/
            if(!check_verify($verify)){
                $this->error('您输入的验证码不正确!');
            }
            /* 调用登录接口登录 */
            $User = new UserApi;
            $uid = $User->login($username, $password);
            if(0 < $uid){ //登录成功
                /* 登录用户 */
                $Admin = D('Admin');
                if($Admin->login($uid)){ //登录用户
                    $this->success('登录成功！', U('Index/index'));
                } else {
                    $this->error($Admin->getError());
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        }else{
            if(admin_is_login()){
                $this->redirect('Index/index');
            } else {
                $this->display();
            }
        }
    }

    /**
     *
     */
    public function logout(){
        if(admin_is_login()){
            D('Admin')->logout();
            session('[destroy]');
            $this->success('退出成功！', U('login'));
        } else {
            $this->redirect('login');
        }
    }

    /**
     * 验证码
     * @return image 验证码图像
     */
    public function verify(){
        $verify = new \Think\Verify;
        $verify->imageH = 25;
        $verify->imageL = 100;
        $verify->length = 4;
        $verify->fontSize = 14;
        $verify->useCurve = true;
        $verify->useNoise = false;
        $verify->entry(1);
    }
}