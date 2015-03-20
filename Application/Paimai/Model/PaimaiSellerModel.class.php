<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-8
 * Time: 下午7:51
 */

namespace Paimai\Model;


use Think\Model;

class PaimaiSellerModel extends Model{
    //自动验证
    protected $_validate = array(
        array("seller_name","require","品牌名不能为空"),
        //array("attr_goodstypeid","0","请选择商品类型",0,'notequal'),//如果attr_goodsstypeid==0则不能通过
    );


} 