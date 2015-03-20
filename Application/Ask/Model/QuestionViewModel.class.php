<?php
/**
 * Description of QuestionViewModel
 * @author Kaiwei Sun date:2014/07/30
 */
namespace Ask\Model;
use Think\Model\ViewModel;

class QuestionViewModel extends ViewModel {
    protected $viewFields = array(
        'question' => array(
            'id','title','input_time',
            '_type' => 'LEFT'
        ),
        'reply' => array(
            'content', 'question_id', '_on' => 'question.id = reply.question_id'
        )
    );
    public function getAll($where,$order,$limit){
        return $this->where($where)->order($order)->limit($limit)->select();
    }
}
