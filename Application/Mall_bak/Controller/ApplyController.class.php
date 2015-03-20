<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 会员申请店铺 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.11.02
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Home\Controller\HomeController;

class ApplyController extends HomeController {

    public function index(){

        // 登录用户ID
        $loginUid = 3;

        $Model = D('Store');

        // 待补充：验证此UID是否已经注册申请
        $data = $Model->where(array('uid' => $loginUid))->find();

        if($data){

            $this->info = $data;
            $this->display('Apply/edit');

        }else{

            if(IS_POST){



            }else{

                $this->display();
            }
        }
    }

    public function edit(){

        // 编辑信息
        if(IS_POST){

            $Model = D('Store');

            // 先判断信息是否存在
            $info = $Model->where(array('store_id'=>I('post.store_id')))->find();
            empty($info) AND $this->error('信息不存在或者已经被删除！');

            // 构建数据
            $data = $Model->create();
            if($data){

                if ($Model->save() !== false) {
                    $this->success('编辑成功！');
                } else {
                    $this->error('编辑失败！');
                }

            } else {
                $this->error($Model->getError());
            }

        }
    }
}