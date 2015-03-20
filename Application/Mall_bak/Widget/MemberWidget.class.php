<?php

namespace Mall\Widget;
use Common\Controller\Widget;

class MemberWidget extends Widget {

    function menu(){

//        $this->assign('data', $data);
        $this->display('Mall/Member/menu');
    }
}
