<?php
/**
 * TestWidget.class.php
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 yishu. All rights reserved.
 */

namespace Home\Widget;
use Common\Controller\Widget;

class TestWidget extends Widget {

    function t(){
        $data = array(
            'test'=>1123,
            'admin'=>aaaa,
        );
        $this->assign('data',$data);
        $this->display('Home/Test/t');
    }
}