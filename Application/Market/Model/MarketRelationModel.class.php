<?php

/**
 * Description of MarketRelationModel(关系模型,一对一)
 * @date 2014/08/05
 * @author KAIWEI SUN <663642331@qq.com>
 */

namespace Market\Model;

use Think\Model\RelationModel;

class MarketRelationModel extends RelationModel {

    protected $tableName = 'market_info';
    protected $_link = array(
        'market_cate' => array(
            'mapping_type' => self::BELONGS_TO,
            'mapping_name' => 'cate',
            'foreign_key' => 'cid',
            'mapping_fields' => 'name',
            'as_fields' => 'name:cname'
        )
    );

}
