<?php
/**
 * 会员中心首页
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 yishu. All rights reserved.
 * @link          http://www.yishu.com
 */
namespace Member\Controller;

class IndexController extends MemberController {

    public function index(){
        $this->SEO = array(
            'title' => '首页',
            'keywords' => '会员中心首页',
            'description' => '中国艺术网会员中心首页',
        );
        //print_r($_SESSION);
        $this->assign('member',memberInfo(UID));
        $this->display();
    }
}