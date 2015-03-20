<?php
namespace Ask\Model;
use Think\Model;
class HomeQuestionModel extends Model {
    /**
     * 问答
     * @param  integer $hot   是否热门
     * @param  integer $resolve   是否解决 1.未解决2.已解决3.零解决
     * @param  integer $limit   记录条数
     * @return array
     */
    public function getQuestions($hot,$resolve,$limit){
        $where_str = ' 1=1 ';
        if($hot){
            $where_str.=' and tag=1 ';
        }
        if($resolve){
            $where_str.=' and status='.$resolve;
        }
        $questions = $this->query("select id,title from ".$this->tablePrefix."ask_question where ".$where_str." order by input_time desc limit ".$limit);
        if(count($questions)>0){
            foreach($questions as $key=>&$val){
                $arr_reply = $this->query("select content from ".$this->tablePrefix."ask_reply where question_id=".$val['id']." order by input_time desc limit 1");
                $val['reply'] = '';
                if(count($arr_reply)>0){
                    $val['reply'] = $arr_reply[0]['content'];
                }
            }
        }
        return $questions;
    }
}