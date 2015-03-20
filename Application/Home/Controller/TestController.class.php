<?php
// +----------------------------------------------------------------------
// | IndexController.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

class TestController extends Controller{

    public function index(){

        $dd = D('Document');
        echo $this->aa();

        $_model = new TestModel();
    }


}