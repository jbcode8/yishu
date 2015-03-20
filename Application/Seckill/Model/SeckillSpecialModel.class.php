<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-16
 * Time: 上午10:24
 */

namespace Seckill\Model;


use Think\Model;

//class SeckillspecailModel extends Model{
class SeckillspecialModel extends Model{
	//自动完成
    protected $_auto = array (
        array("skspecial_starttime","strtotime","3","function"),//3为更改和添加
        array("skspecial_endtime","strtotime","3","function"),
        array("skspecial_createtime","time","1","function"),//1为添加时
        array("skspecial_updatetime","time","2","function"),//2为更改
    );
} 