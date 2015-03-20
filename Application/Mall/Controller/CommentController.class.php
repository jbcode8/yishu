<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 评论 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.30
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class CommentController extends AdminController {

    public function index(){
        $is_reply = I('reply','','intval');
        $condition['c_id'] = 0;
        if($is_reply === 0){
            $condition['is_reply'] = 0;
        }else if($is_reply > 0){
            $condition['is_reply'] = array('gt', 0);
        }

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');
        $startTime = I('start_time', '', 'trim');
        $endTime = I('end_time', '', 'trim');

        // 组装搜索条件
        if(!empty($kw) || !empty($startTime) || !empty($endTime)){

            // 开始时间
            empty($startTime) OR $intStart = time2int($startTime, ' 00:00:00');
            $this->assign('start_time', $startTime);

            // 结束时间
            empty($endTime) OR $intEnd = time2int($endTime, ' 23:59:59');
            $this->assign('end_time', $endTime);

            // 关键字
            empty($kw) OR $condition['content'] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);

            // 时间同时具有
            if($startTime != '' && $endTime != ''){
                $condition['create_time'] = array(between, array($intStart, $intEnd));
            }else{
                // 开始时间和结束时间单个
                empty($startTime) OR $condition['create_time'] = array('gt', $intStart);
                empty($endTime) OR $condition['create_time'] = array('lt', $intEnd);
            }
        }

        $list = $this->lists('Comment', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 读取回复信息
    public function reply(){
        $comment_id = I('comment_id','','intval');
        unset($_REQUEST['comment_id']);
        $list = $this->lists('Comment', null, null, array('c_id' => $comment_id), true, false);
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除前的操作
    public function _before_delete(){

        // 判断信息是否存在
        $comment_id = I('comment_id','','intval');
        empty($comment_id) AND $this->error('信息已经被删除或者不存在！');

        // 区分删除评论 和 评论回复
        $c_id = I('c_id','','intval');
        if(empty($c_id)){
            $info = D('Comment')->field('is_reply')->where(array('comment_id' => $comment_id))->find();
            $info['is_reply'] > 0 AND $this->error('此评论有回复信息，请先删除回复信息再删除！');
        }else{
            // 如果是删除评论的回复信息则先重置此评论信息的回复次数(减一)
            D('Comment')->where(array('comment_id' => $c_id))->setDec('is_reply');
        }
    }
} 