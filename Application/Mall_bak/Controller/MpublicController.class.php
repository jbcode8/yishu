<?php

// +----------------------------------------------------------------------
// | 古玩城 会员中心 我的店铺 (公共)控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.09
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Member\Controller\MemberController;

class MpublicController extends MemberController {

    public function _initialize(){

        parent::_initialize();


        // 1. 检测是否已经保存SESSION['store_id'](存在)
        if(isset($_SESSION['store_id']) && $_SESSION['store_id'] > 0){
        }else{

            // 2. 读取数据库：查看是否存在数据
            $where['uid'] = $_SESSION['user_auth']['uid'];
            $aryInfo = D('MallStore')->where($where)->find();

            // 3. 如果存在，检测状态
            if($aryInfo){

                // 锁定
                $aryInfo['status'] == 2 AND $this->error('很抱歉，您的店铺会员已经被锁定，暂无法操作，请联系管理员。');

                // 待审
                $aryInfo['status'] == 0 AND $this->error('您好，您的店铺会员申请正在审核中....',U('Member/index/index'));

                // 保存到SESSION
                $_SESSION['store_id'] = $aryInfo['store_id'];

                // 设置为全局
                $this->gInfo = $aryInfo;

            }else{

                // 除信息管理页面，其他页面都得检测是否加入
                if(CONTROLLER_NAME != 'Mstore'){
                    $this->error('很抱歉，您未申请为店铺会员，请先加入。');
                }
            }
        }
    }
}