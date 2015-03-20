<?php
/**
 * 用户中心
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 yishu. All rights reserved.
 * @link          http://www.yishu.com
 */

namespace Member\Controller;
use Addons\Oauthlogin\Controller\OauthloginController;
use Home\Controller\HomeController;
use User\Api\UserApi;

class PassportController extends HomeController{

    /**
     * passport通行证
     */
    public function index(){
        if(is_login()){
            $this->redirect('Member/index/index');
        }else{
            $this->redirect('Member/Passport/login');
        }
    }
    /**
     * 登录入口
     */
    public function login(){
        if(IS_POST){
            $username = $_POST['username'];
            $password = $_POST['password'];
            $remember = $_POST['remember'];
            $User = new UserApi();
            $uid = $User->login($username,$password,1);
			//exit($uid);
            if(0 < $uid){ //登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid,$remember)){ //登录用户
					
                    $returnUrl = $_GET['returnUrl']?$_GET['returnUrl']:U('Member/index/index');
					
                    redirect($returnUrl);
                } else {
					//echo "ok";exit;
                    $this->error($Member->getError());
                }
            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '系统繁忙,请稍候再试！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        }else{
            if(is_login()){
                $this->redirect('Member/index/index');
            }else{
                $this->display();
            }
        }
    }

    /**
     * 会员退出
     */
    public function logout(){
        D('Member')->logout();
        $this->redirect('Member/Passport/login');
    }

    /**
     * 注册入口
     */
    public function register(){
        if(IS_AJAX){
            $username = I('post.userName');
            $nickName = I('post.nickName');
            $password = I('post.passWord');
            $passWordRepeat = I('post.passWordRepeat');
            $verify = I('post.verify');
            if(!$password){
                $data['info'] = '请您输入密码!';
                $data['status'] = 1;
                $this->ajaxReturn($data, 'json');
            }
            
            if($password != $passWordRepeat){
                $data['info'] = '您输入的两次密码不一致!';
                $data['status'] = 2;
                $this->ajaxReturn($data, 'json');
            }
            if(!check_verify($verify)){
                $data['info'] = '您输入的验证码不正确!';
                $data['status'] = 3;
                $this->ajaxReturn($data, 'json');
            }
            /* 判断注册类型 */
            switch(login_type($username)){
                case 1:
                    $email = null;
                    $mobile = null;
                    $nickname = $nickName;
                    break;
                case 2:
                    $email = $username;
                    $return = explode('@',$username);
                    $username = salt(3,2).'_'.$return[0];
                    $nickname = $nickName;
                    break;
                case 3:
                    $mobile = $username;
                    $email = null;
                    //$nickname = preg_replace('/(\d{3})\d{4}(\d{3})/','$1***$2',$username);
                    $nickname = $nickName;
                    break;
            }
            $Member = D('Member');
            $uid = $Member->register($username,$password,$email,$mobile,$nickname);
            if($uid > 0){
                if($Member->login($uid)){
                    $data['status'] = 4;
                    $data['info'] = '注册成功!';
                    $data['url'] = U('/Member');
                    $this->ajaxReturn($data, 'json');
                }
            }else{
                switch($uid){
                    case -1:
                        $data['status'] = -1;
                        $data['info'] = $this->showRegError($uid);
                        break;
                    case -2:
                        $data['status'] = -2;
                        $data['info'] = $this->showRegError($uid);
                        break;
                    case -3:
                        $data['status'] = -3;
                        $data['info'] = $this->showRegError($uid);
                        break;
                    case -4:
                        $data['status'] = -4;
                        $data['info'] = $this->showRegError($uid);
                        break;
                }
                $this->ajaxReturn($data, 'json');
            }
        }else{
            $this->display();
        }
    }

    /**
     * 注册错误处理
     * @param $uid
     * @return string
     */
    private function showRegError($uid){
        switch($uid){
            case -1:
                return '用户名长度不合法';
                break;
            case -2:
                return '用户名禁止注册';
                break;
            case -3:
                return '用户名被占用';
                break;
            case -4:
                return '密码长度不合法';
                break;
            case -5:
                return '邮箱格式不正确';
                break;
            case -6:
                return '邮箱长度不合法,限制1-32个字符';
                break;
            case -7:
                return '邮箱禁止注册';
                break;
            case -8:
                return '邮箱被占用';
                break;
            case -9:
                return '手机格式不正确';
                break;
            case -10:
                return '手机禁止注册';
                break;
            case -11:
                return '手机号被占用';
                break;
            default:
                return '未知错误';
        }
    }
    /**
     * 社会化登录
     */
    public function auth(){
        $auth = new OauthloginController();
        $auth->login();
    }

    /**
     * 社会化登录回调
     */
    public function callback($type,$code){
        $auth = new OauthloginController();
        $auth->callback($type,$code);
    }

	//找回密码
    public function forgetpwd(){
        if(I('get.step') == 'one'){
            if(IS_AJAX){
                if(!check_verify(I('post.verify'))){
                    $data['info'] = '您输入的验证码不正确!';
                    $data['status'] = 1;
                    $this->ajaxReturn($data, 'json');
                }
                if($userinfo = $this->_email('username', I('post.userName'))){
                    if(! $userinfo['email']){
                        $data['status'] = 4;
                        $data['info'] = '此用户没有设置安全邮箱,请联系管理员!';
                    }else{
                        session('username', $userinfo['username']);
                        session('email', $userinfo['email']);
                        $data['status'] = 3;
                        $data['url'] = U('/Member/Passport/forgetpwd').'?step=two';
                    }
                    $this->ajaxReturn($data, 'json');
                }else{
                    $data['info'] = '用户信息不存在!';
                    $data['status'] = 2;
                    $this->ajaxReturn($data, 'json');
                }
            }
            $this->display('forgetpwd_one');
        }else if(I('get.step') == 'two'){
            if(session('username') && session('email')){
                $username = session('username');
                $email = session('email');
                $this->username = substr_replace($username, '***' , 2, 3);
                $this->email = substr_replace($email, '****' , 2, 4);
            }else{
                echo "<script>alert('链接已失效,请重新操作!');history.go(-1);</script>";
            }
            $this->display('forgetpwd_two');
        }else if(I('get.step') == 'three'){
            $this->status = '验证邮件已发送，请您 登录邮箱 完成认证。';
            $this->display('forgetpwd_three');
        }else if(I('get.step') == 'four'){
            if($key = I('get.key')){
                $key = explode('^', urldecode(base64_decode($key)));
                $name = $key[0];
                $time = date('i', (time() - $key[1]));
                if($time > 30){
                    echo "<script>alert('链接已过期，请重新发送邮件!');location.href='/Member/Passport/forgetpwd.html?step=two';</script>";
                }
                if(IS_AJAX){
                    if(session('username') == $name){
                        $username = session('username');
                    }else{
                        $username = $name;
                    }
                    $userinfo = $this->_email('username', $username);
                    $password = I('post.passWord');
                    $passwords = I('post.passWordRepeat');
                    if(!$password){
                        $data['info'] = '请您输入密码!';
                        $data['status'] = 0;
                        $this->ajaxReturn($data, 'json');
                    }
                    if($password != $passwords){
                        $data['info'] = '您输入的两次密码不一致!';
                        $data['status'] = 0;
                        $this->ajaxReturn($data, 'json');
                    }
                    if(M('ucenter_member')->where(array('id'=>$userinfo['id']))->save(array('password'=>md5($password.$userinfo['salt'])))){
                        $data['status'] = 1;
                        $data['url'] = '/Member/Passport/forgetpwd.html?step=five';
                        $this->ajaxReturn($data, 'json');
                    }else{
                        $data['info'] = '修改失败,新密码不能和原密码相同,请重试!';
                        $data['status'] = 0;
                        $this->ajaxReturn($data, 'json');
                    }
                }else{
                    $this->user_name = $name;
                }
            }else{
                $data['info'] = '链接失效或参数错误,请检查URL是否完整或重新操作!';
                $data['status'] = 0;
                $this->ajaxReturn($data, 'json');
            }
            $this->display('forgetpwd_four');
        }else if(I('get.step') == 'five'){
            $this->display('forgetpwd_five');
        }else{
            $data['url'] = '/Member/Passport/forgetpwd.html?step=one';
            header("location:" . $data['url']);
        }
    }
    //发送邮件
    public function sendmail(){
        if(IS_AJAX){
            if(session('email')){
                $sendmail = $this->_send_eamil('feiniutest@163.com', 'abcd123', session('email'));
                if($sendmail){
                    $data['url'] = U('/Member/Passport/forgetpwd').'?step=three';
                    $data['status'] = 1;
                    $this->ajaxReturn($data, 'json');
                }else{
                    $data['info'] = '发送失败!';
                    $data['status'] = 0;
                    $this->ajaxReturn($data, 'json');
                }
                
            }else{
                $data['info'] = '链接已过期!';
                $data['status'] = 0;
                $this->ajaxReturn($data, 'json');
            }
        }else{
            $data['info'] = '非法操作!';
            $data['status'] = 0;
            $this->ajaxReturn($data, 'json');
        }
    }
    
    //检验邮箱是否存在
    private function _email($string, $condition){
        $useremail = M('ucenter_member')->field('id,username,salt,email,mobile')->where(array($string => $condition))->find();
        return $useremail;
    }
    private function _send_eamil($user = 'feiniutest@163.com', $pwd = 'abcd123', $tomail = '663642331@qq.com') {
        $mail = new \Org\Net\Email();
        $config['smtp_pass'] = 25; //本机发送忽略
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'smtp.163.com';
        $config['smtp_user'] = $user; //本机发送忽略
        $config['smtp_pass'] = $pwd; //本机发送忽略
        $config['mailtype'] = "html";
        $config['charset'] = 'utf-8';
        $mail->initialize($config);
        //发送到指定邮箱
        //$mail->from('service1@service.feiniu.com', 'FriendLink');//本机发送忽略
        $mail->from($user, '中国艺术网');
        //$this->reply_to('you@example.com', 'Your Name');//邮件回复地址. 如果没有提供这个信息，将会使用"from()"函数中的值
        $mail->to($tomail); //多个用逗号分开,最后一个逗号要去掉
        //$mail->cc($tomail);//抄送
        //$mail->bcc();//暗送
        $mail->subject('中国艺术网找回密码邮件');
        $name = session('username');
        $key = urlencode(base64_encode($name.'^'.time()));
        $url = U('/Member/Passport/forgetpwd')."?step=four&key=$key";
        $time = date('Y-m-d H:i:s');
        $content = "
        您好，$name ：<br /><br />
        请点击下面的链接来重置您的密码。<br /><br />
        $url <br /><br />
        如果您的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。<br /><br />
        该验证邮件有效期为30分钟，超时请重新发送邮件。<br /><br />
        发件时间：$time<br /><br />
        此邮件为系统自动发出的，请勿直接回复。<br /><br />
        ";
        $mail->message($content);
        $email = $mail->send();
        //输出结果
        //echo $mail->print_debugger();
        return $email;
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