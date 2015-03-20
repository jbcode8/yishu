<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-9
 * Time: 下午4:45
 */

namespace Paimai\Model;


use Think\Model;

class PaimaiGoodsModel extends Model{
    //自动验证
    protected $_validate = array(
        array("goods_name","require","商品名称不能为空"),
       // array("goods_starttime","0,30","专场不能大于30个字符","3","length"),
        array("goods_startprice","require","起始价不能为空"),

        array("goods_everyprice","require","加价幅度不能为空"),
		array("goods_needmoney","require","保证金不能为空"),
        array('recordid','','商品图片已经有人上传请重新上传上品图片！',0,'unique',1), // 在新增的时候验证图片recordid字段是否唯一

    );
    //自动完成
    protected $_auto = array (
        array("goods_starttime","strtotime","3","function"),
        array("goods_endtime","strtotime","3","function"),
        array("goods_createtime","time","1","function"),
        array("goods_updatetime","time","2","function"),
    );
	/*
     * 创建唯一充值单号,
     */
    public function CreateRechargeSn()
    {
        //创建唯一订单号
        $orderinfo = M("PaimaiRecharge");
        $info_sn = 'CZ' . date("Ymd") . mt_rand(10000, 99999);
        return $orderinfo->where("info_sn=$info_sn")->count() ? $this->CreateRechargeSn() : $info_sn;
    }
} 