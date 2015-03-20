<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-6
 * Time: 下午5:45
 */

namespace Paimai\Model;
use Think\Model;

class PaimaiGoodsTypeModel extends Model{
    //自动验证
    protected $_validate = array(
        array("goodstype_name","require","请添写分类名称"),
    );

    //自动完成
} 