<?php
/**
 * Description of QuestionViewModel
 * @author Kaiwei Sun date:2014/07/30
 */
namespace Ask\Model;
use Think\Model\RelationModel;

class QuestionRelationsModel extends RelationModel {
    protected $tableName = 'reply';
	protected $_link = array(
            'comment' => array(
                'mapping_type'   => self::HAS_MANY,
                'mapping_name'   => 'comment',
                'foreign_key'    => 'reply_id',
                'mapping_fields' => 'content as mcontent,inputime, user_id',
                'mapping_order'  => 'inputime desc',
                //'mapping_limit'  => '10'
            )
           
	);
}
