<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-16
 * Time: 上午10:24
 */

namespace Seckill\Model;


use Think\Model;

class SeckillgoodsModel extends Model{
	//自动完成
    protected $_auto = array (
        array("skgoods_starttime","strtotime","3","function"),//3为更改和添加
        array("skgoods_endtime","strtotime","3","function"),
        array("skgoods_createtime","time","1","function"),//1为添加时
        array("skgoods_updatetime","time","2","function"),//2为更改
    );
} 