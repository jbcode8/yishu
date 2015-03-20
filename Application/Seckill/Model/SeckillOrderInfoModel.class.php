<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015
 * Time: 下午4:45
 */

namespace Seckill\Model;


use Think\Model;

class SeckillOrderInfoModel extends Model{
    //自动验证
    protected $_validate = array(
        array("orderinfo_provincename","require","省不能为空"),
       // array("goods_starttime","0,30","专场不能大于30个字符","3","length"),
        array("orderinfo_cityname","require","城市不能为空"),

        array("orderinfo_address","require","详细地址不能为空"),
		array("orderinfo_reciver","require","收货人姓名不能为空"),
        array("orderinfo_mobile","require","收货人手机不能为空"),

    );
    //自动完成
    protected $_auto = array (
        array("goods_starttime","strtotime","3","function"),
        array("goods_endtime","strtotime","3","function"),
        array("goods_createtime","time","1","function"),
        array("goods_updatetime","time","2","function"),
    );
	
} 