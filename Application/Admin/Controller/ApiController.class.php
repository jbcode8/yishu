<?php
// +----------------------------------------------------------------------
// | ApiController.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Think\Controller;

class ApiController extends Controller {

    public function _initialize(){
        if(!admin_is_login()){
            $this->error('请先登录!',U('Public/login'));
        }
    }

    /**
     * 根据modelid获取category
     * @param $modelid
     */
    public function getModelCategory($modelid){
        $data =  M('category')->where(array('model'=>$modelid,'status'=>1))->order('listorder asc,catid asc')->getField('catid,title',true);
        $this->ajaxReturn(json_encode($data));
    }
} 