<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 首页BANNER上传
// +----------------------------------------------------------------------
// | Author: jeff # 
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class AdminBannerController extends AdminController {

    public function _initialize(){
        parent::_initialize();
        $this->ModelName = 'Banner';
    }

    public function index(){

        $this->display();
    }

	
	public function upload(){
		if(IS_POST){
			isset($_POST['pics']) AND $picsUrl = $_POST['pics'];
		}
		$picArray = array();
		foreach($picsUrl as $key=>$val){
			$picArray[$key]['img_url'] = $picsUrl[$key];
			$picArray[$key]['create_time'] = time();
		}
		$bool = M('mall_banner')->addAll($picArray);
		if($bool){
			$this->success('保存成功！', U('Mall/AdminBanner/index'));
		}else{
			$this->error('保存成功！', U('Mall/AdminBanner/index'));
		}
	}

    
    
}