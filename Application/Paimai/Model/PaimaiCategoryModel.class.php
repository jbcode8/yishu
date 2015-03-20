<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-7
 * Time: 下午3:23
 */

namespace Paimai\Model;


use Think\Model;

class PaimaiCategoryModel extends Model{
    //自动验证
    protected $_validate = array(
        array("cat_name","require","请添写商品分类名称"),
        array("cat_unit","0,5","单位字符长度不能大于5个字符","3","length"),
		array("cat_spell","require","请添写商品分类拼音名称"),
    );

} 