<?php

/**
 * Description of ArtistRelationModel(关联模型，一对多)
 * @date 2014/08/05
 * @author KAIWEI SUN <663642331@qq.com>
 */

namespace Market\Model;

use Think\Model\RelationModel;

class ArtistRelationModel extends RelationModel {

    protected $tableName = 'artist_library';
    protected $_link = array(
        'artist_works' => array(
            'mapping_type' => self::HAS_MANY,
            'mapping_name' => 'artist_works',
            'foreign_key' => 'aid',
            'mapping_fields' => 'id as wid, aid, name as pname,recordid,size',
            'mapping_limit' => '2',
            'mapping_order' => 'award desc',
            'condition' => 'status=1'
        )
    );

}
