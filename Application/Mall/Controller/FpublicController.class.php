<?php

// +----------------------------
// | 前端人口 (公共)控制器文件
// +----------------------------
// | Author: Rain Zen
// | modify: Kaiwei Sun (增加31-40行) 663642331@qq.com date: 2014/07/15
// +----------------------------

namespace Mall\Controller;
use Home\Controller\HomeController;

class FpublicController extends HomeController {

    public function _initialize(){

        parent::_initialize();

        // 搜索关键字
        $this->aryKey = D('Keywords')->field(array('key_id AS id','words'))->where(array('status'=>1))->limit(6)->order('listorder ASC,hits DESC')->select();

        // 历史记录
        $this->aryHistory = aryHistory();

        // 登录用户信息
        if(isset($_SESSION['user_auth']['uid']) && $_SESSION['user_auth']['uid'] > 0){
            $uid = $_SESSION['user_auth']['uid'];
            $aryUser = M('Member')->where(array('uid'=>$uid))->find();
            empty($aryUser) OR $this->aryUser = $aryUser;
        }
        //切换城市
        if(session('city_name')){
            $this->city_name = session('city_name');
        }else{
            if(get_ip_city() != '本机地址'){
                $this->city_name = get_ip_city();
            }else{
                $this->city_name = '上海';
            }
        }
        // 顶级类别
        $this->category = D('Category')->resetCategore(0,0,1);

        // 底部导航
        $this->bottomNav = D('Article')->where(array('cate_id'=>17))->select();
    }
}