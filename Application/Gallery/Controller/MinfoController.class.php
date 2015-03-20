<?php

// +----------------------------------------------------------------------
// | 画廊 会员中心 (公共)控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.02.13
// +----------------------------------------------------------------------

namespace Gallery\Controller;
use Gallery\Controller\MemberPublicController;

class MinfoController extends MemberPublicController {

    public function _initialize(){

        parent::_initialize();
        $this->Model = D('GalleryList');
    }

    /**
     * MemberPublicController公共入口类检测登录用户的状态[入驻或未入驻]
     * 如果已经注册(入驻)画廊库，则显示编辑信息
     * 反之怎转向到注册页面(画廊申请页面)
     */
    public function index(){

        // 编辑
        if($_SESSION['gid']){

            if(IS_POST){

                $data = $this->Model->create();

                // 组图的组装和系列化
                if(isset($_POST['pics'])){
                    $aryPics = buildPics($_POST['pics']);
                    empty($aryPics) OR $data['pics'] = serialize($aryPics);
                }

                // 入库前先获取名称首汉字的拼音首字母
                (isset($_POST['name']) && !empty($_POST['name'])) AND $data['letter'] = firstLetter($_POST['name']);

                if($data){
                    if($this->Model->save() !== false){
                        $this->success('操作成功！');
                    }else{
                        $this->error("操作失败!");
                    }
                }else{
                    $this->error($this->Model->getError());
                }

            }else{

                $where['id'] = $_SESSION['gid'];
                $data = $this->Model->where($where)->find();
                empty($data) OR $this->data = $data;

                $this->display('edit');
            }

        // 新增
        }else{

            if(IS_POST){

                // 组装用户ID
                $_POST['uid'] = $_SESSION['user_auth']['uid'];

                // 组图的组装和系列化
                if(isset($_POST['pics'])){
                    $aryPics = buildPics($_POST['pics']);
                    empty($aryPics) OR $_POST['pics'] = serialize($aryPics);
                }

                // 入库前先获取名称首汉字的拼音首字母
                (isset($_POST['name']) && !empty($_POST['name'])) AND $_POST['letter'] = firstLetter($_POST['name']);

                $data = $this->Model->create();

//                prt($data, true);

                if($data){
                    if(($gid = $this->Model->add()) !== false){
                        $this->success('操作成功！');
                    }else{
                        $this->error("操作失败!");
                    }
                }else{
                    $this->error($this->Model->getError());
                }

            }else{
                $this->display('add');
            }
        }
    }
}