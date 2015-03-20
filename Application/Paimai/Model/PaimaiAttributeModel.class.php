<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-7
 * Time: 下午12:53
 */

namespace Paimai\Model;


use Think\Model;

class PaimaiAttributeModel extends Model{
    //自动验证
    protected $_validate = array(
        array("attr_name","require","请添写属性名称"),
        array("attr_goodstypeid","0","请选择商品类型",0,'notequal'),//如果attr_goodsstypeid==0则不能通过
    );
} 