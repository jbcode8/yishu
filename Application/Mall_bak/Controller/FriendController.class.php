<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 友情链接 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.25
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class FriendController extends AdminController {

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');
        $startTime = I('start_time', '', 'trim');
        $endTime = I('end_time', '', 'trim');
        $type = I('stype', '', 'trim');

        // 组装搜索条件
        if(!empty($kw) || !empty($startTime) || !empty($endTime) || !empty($type)){

            // 开始时间
            empty($startTime) OR $intStart = time2int($startTime, ' 00:00:00');
            $this->assign('start_time', $startTime);

            // 结束时间
            empty($endTime) OR $intEnd = time2int($endTime, ' 23:59:59');
            $this->assign('end_time', $endTime);

            // 关键字
            empty($type) AND $type = 'title';
            empty($kw) OR $condition[$type] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
            $this->assign('type', $type);

            // 时间同时具有
            if($startTime != '' && $endTime != ''){
                $condition['create_time'] = array(between, array($intStart, $intEnd));
            }else{
                // 开始时间和结束时间单个
                empty($startTime) OR $condition['create_time'] = array('gt', $intStart);
                empty($endTime) OR $condition['create_time'] = array('lt', $intEnd);
            }
        }

        $list = $this->lists('Friend', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 产品状态(0：未审核；1：审核；2：锁定)
    public function editstatus(){

        // 获取信息ID
        isset($_GET['friend_id']) AND $friend_id = intval($_GET['friend_id']);
        isset($friend_id) OR $this->error('信息不存在或者已删除！');

        // 获取状态值
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) OR $this->error('参数有误！');

        $where['friend_id'] = $friend_id;
        $field['status'] = $status;
        $field['update_time'] = time();

        if(D('Friend')->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }

    // 删除之前的判断
    public function _before_delete(){
        
        // 判断品牌ID是否存在
        $friend_id = I('get.friend_id', '', 'intval');
        empty($friend_id) AND $this->error('链接不存在或者已经被删除!');
    }

    // 添加前判断如果是图片链接则必须填写图片
    public function _before_add(){
        if(IS_POST){
            $type = I('post.type','','trim');
            $thumb = I('post.thumb','','trim');
            ($type == '1' && empty($thumb)) AND $this->error('图片链接需要填写图片地址！');
        }
    }

    // 添加前判断如果是图片链接则必须填写图片
    public function _before_edit(){
        if(IS_POST){
            $type = I('post.type','','trim');
            $thumb = I('post.thumb','','trim');
            ($type == '1' && empty($thumb)) AND $this->error('图片链接需要填写图片地址！');
        }
    }
}