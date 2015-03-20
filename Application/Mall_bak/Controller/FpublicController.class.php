<?php

// +----------------------------------------------------------------------
// | 古玩城 会员中心 前端人口 (公共)控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.18
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Home\Controller\HomeController;

class FpublicController extends HomeController {

    public function _initialize(){

        parent::_initialize();

        // 搜索关键字
        $this->arykey = D('Keywords')->field(array('key_id AS id','words'))->where(array('status'=>1))->limit(6)->order('listorder ASC,hits DESC')->select();

    }

} 