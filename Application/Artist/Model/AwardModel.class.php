<?php
// +----------------------------------------------------------------------
// | 大师频道基类模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Model;


class AwardModel extends ArtistModel{

    protected $_validate = array(
        array('name', 'require', '奖项名称不能为空', self::VALUE_VALIDATE, '', self::MODEL_BOTH),
        array('name', '', '奖项名称不能为空', self::VALUE_VALIDATE, 'unique', self::MODEL_INSERT),
    );

    protected $_auto = array(
        array('letter', 'getFirstLetter', self::MODEL_BOTH, 'callback'),
    );

    public function getFirstLetter(){
        return get_letter($_POST['name']);
    }

} 