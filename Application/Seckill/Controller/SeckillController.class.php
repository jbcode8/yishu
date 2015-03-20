<?php

    /**
     * Seckill 项目的前台公共控制器
     * @time 2015-1-18
     * @author zhihui
     * */
    namespace Seckill\Controller;
    use Think\Controller;

    class SeckillController extends Controller{
        protected function _initialize(){
            //加载配置参数
            C(api('Admin/Config/lists',array('group'=>1)));
            //获取用户的登录信息
            $auth = getLoginStatus();
            $this->assign('auth', $auth);
        }
    }
