<?php
/**
 * InfoController.class.php
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 sparui. All rights reserved.
 * @link          http://www.sparui.com
 * @license       http://www.sparui.com/license
 */

namespace Member\Controller;


class InfoController extends MemberController{
    public function index(){
        $mUser = D('Member')->field('status,last_login_time,last_login_ip',true)->relation(true)->find(UID);
        $this->assign('muser',$mUser);
        $this->display();
    }

    public function base(){
        $this->display();
    }
} 