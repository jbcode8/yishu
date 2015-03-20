<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午5:06
 */

namespace Paimai\Model;


use Think\Model;

class PaimaiSloganModel extends Model{
    //自动验证
    protected $_validate = array(
        array("slogan_name","require","请添写口号名称"),
        array("slogan_name","0,15","口号名称不能大于15个字符","3","length"),
        //array("attr_goodstypeid","0","请选择商品类型",0,'notequal'),//如果attr_goodsstypeid==0则不能通过
    );

} 