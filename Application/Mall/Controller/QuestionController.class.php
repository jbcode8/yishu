<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 店铺咨询 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.31
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class QuestionController extends AdminController {

    // 列表信息
    public function index(){

        // 初始条件：0为非回复信息
        $condition['q_id'] = 0;

        // 细分信息(回复|未回复)
        $is_reply = I('reply','','intval');
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

        $list = $this->lists('Question', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 读取回复信息
    public function reply(){
        $question_id = I('question_id','','intval');
        unset($_REQUEST['question_id']);
        $list = $this->lists('Question', null, null, array('q_id' => $question_id), true, false);
        $this->assign('_list', $list);
        $this->display();
    }

    // 删除前的操作
    public function _before_delete(){

        // 判断信息是否存在
        $question_id = I('question_id','','intval');
        empty($question_id) AND $this->error('信息已经被删除或者不存在！');

        // 区分删除 咨询||咨询回复
        $q_id = I('q_id','','intval');
        if(empty($q_id)){
            $info = D('Question')->field('is_reply')->where(array('question_id' => $question_id))->find();
            $info['is_reply'] > 0 AND $this->error('此咨询信息有回复信息，请先删除回复信息再删除！');
        }else{
            D('Question')->where(array('question_id' => $q_id))->setDec('is_reply'); // 回复数减去1
        }
    }
}