<?php
/**
 * Description of QuestionViewModel
 * @author Kaiwei Sun date:2014/07/30
 */
namespace Ask\Model;
use Think\Model\RelationModel;

class QuestionRelationModel extends RelationModel {
    protected $tableName = 'question';
	protected $_link = array(
            'reply' => array(
                'mapping_type'   => self::HAS_MANY,
                'mapping_name'   => 'reply',
                'foreign_key'    => 'question_id',
                'mapping_fields' => 'content,input_time as retime,user_id as uid ,id as rid,praise',
                'mapping_order'  => 'bast desc',
                //'mapping_limit'  => '10'
            ),
            'category' => array(
                'mapping_type'   => self::BELONGS_TO,
                'mapping_name'   => 'cate',
                'foreign_key'    => 'cate_id',
                'mapping_fields' => 'name,short_name'
            )
	);
}
