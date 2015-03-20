<?php
/**
 * MemberBaseController.class.php
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 sparui. All rights reserved.
 * @link          http://www.sparui.com
 * @license       http://www.sparui.com/license
 */

namespace Member\Controller;
use Home\Controller\HomeController;

class MemberController extends HomeController {

    /**
     * 后员中心初始化
     */
    public function _initialize(){
        $this->checkAccess(); //检测是否登录
        parent::_initialize();
        $this->memberMenu();
        $this->user = session('user_auth'); //用户信息
        define('UID',$this->user['uid']);
    }

    /**
     * 判断是否登录
     */
    private function checkAccess(){
        //判断是否登录
        if(!is_login()){ //判断是否登录
            $uid = cookie('uid');
            $user_auth = cookie('user_auth');
            if(!empty($uid) && !empty($user_auth)){ //判断是否存在自动登录cookie
                //存在cookie则进行验证并进行登录
                $auth = M('UcenterMember')->field('id,username,salt')->find($uid);
                $user = unserialize(ucenter_decrypt($user_auth,$auth['salt']));
                if($auth['id'] == $user['uid'] && $auth['username'] == $user['username']){
                    session('user_auth', $user);
                    session('user_auth_sign', data_auth_sign($user));
                }else{
                    redirect(U('Member/Passport/login').'?returnUrl='.getReturnUrl());
                }
            }else{ //不存在cookie则跳转登录页面
                redirect(U('Member/Passport/login').'?returnUrl='.getReturnUrl());
            }
        }
    }

    /**
     * 会员中心菜单
     */
    private function memberMenu(){
        if(!$memberMenu = S('MemberMenu')){
            $memberMenu = M('MemberMenuMall')->order('listorder asc')->getField('id,name,url,pid');
            S('MemberMenu',$memberMenu);
        }
        $this->menu = list_to_tree($memberMenu);
        foreach($memberMenu as $v){
            $urls = explode('?',$v['url']);
            $urls = explode('/',$urls[0]);
            $module = $urls[0];
            $controller = isset($urls[1])?$urls[1]:'Index';
            $action = isset($urls[2])?$urls[2]:'index';
            if($module == MODULE_NAME && $controller == CONTROLLER_NAME && $action == ACTION_NAME){
                $this->menuid = $v['pid']?$v['pid']:$v['id'];
            }
        }
    }
}