<?php

// +----------------------------------------------------------------------
// | 画廊 会员中心 (公共)控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.02.13
// +----------------------------------------------------------------------

namespace Gallery\Controller;
use Member\Controller\MemberController;

class MemberPublicController extends MemberController {

    public function _initialize(){

        parent::_initialize();

        /**
         *  用户登录SESSION信息
         *  [user_auth] => Array (
                [uid] => 29
                [username] => zz0424
                [nickname] => zz0424
                [last_login_time] => 0
            )
         */

        // 1. 检测是否已经保存SESSION['gid'](存在)
        if(isset($_SESSION['gid']) && $_SESSION['gid'] > 0){
        }else{

            // 2. 读取数据库：查看是否存在数据
            $where['uid'] = $_SESSION['user_auth']['uid'];
            $aryInfo = D('GalleryList')->where($where)->find();

            // 3. 如果存在，检测状态
            if($aryInfo){

                // 锁定
                $aryInfo['status'] == 3 AND $this->error('很抱歉，您的画廊信息已经被锁定，暂无法操作，请联系管理员。');

                // 待审
                $aryInfo['status'] == 0 AND $this->error('您好，您的画廊信息正在审核中....',U('Member/index/index'));

                // 保存到SESSION
                $_SESSION['gid'] = $aryInfo['id'];

                // 设置为全局
                $this->gInfo = $aryInfo;

            }else{

                // 除信息管理页面，其他页面都得检测是否加入
                if(CONTROLLER_NAME != 'Minfo'){
                    $this->error('很抱歉，您未加入画廊信息，请先加入。');
                }
            }
        }
    }
} 