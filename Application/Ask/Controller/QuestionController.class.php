<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-22
 * Time: 下午2:18
 */

namespace Ask\Controller;
use Admin\Controller\AdminController;

class QuestionController extends AdminController{
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('Question');
    }

    public function index(){
        //exit("222");
        $map = array();
        $list = parent::lists('Question', $map, 'input_time desc');
        /*echo "<pre>";
        print_r($list);exit;*/
        $this->assign('_list', $list);
        $this->display();
    }

    //设置精彩问答和热门问答
    public function hot_set(){
        $status = I('get.status');
        $id = I('get.id', '', 'intval');
        if($status AND $id){
            if($status == 'jc'){
                $data['tag'] = '2';
                $res = $this->Model->where(array('id' => $id))->save($data); // 根据条件保存修改的数据
                if($res){
                    $this->success('设置成功');
                }
            } elseif($status == 'rm') {
                $data['tag'] = '1';
                $res = $this->Model->where(array('id' => $id))->save($data); // 根据条件保存修改的数据
                if($res){
                    $this->success('设置成功');
                }
            } else {
                $this->error('参数错误！');
            }
        } else {
            $this->error('参数错误！');
        }
    }

    // 读取回复信息
    public function reply(){
        $question_id = I('id','','intval');
        unset($_REQUEST['id']);
        $list = $this->lists('Reply', null, 'input_time desc', array('question_id' => $question_id), true, false);
        $this->assign('_list', $list);
        $this->display();
    }

    /**
     * 删除问题前删除该问题的答案
     */
   /* public function _before_delete(){
        //p("eee");
        $question_id=I('id');
        D('Reply')->where(array('question' => I('get.id', '', 'intval')))->delete();
    }*/
    public function delete(){
        $question_id=I('id',0,'intval');

    #删除问题下的回答
        $reply_where=array(
            'question_id'=>$question_id,
            );
        
        //删除回复
        M('Reply')->where($reply_where)->delete();

        if(M('Question')->delete($question_id)){
            $this->success("删除成功");
        }else{
            $this->error("删除失败,错误代码：001");
        }
       
    }
} 