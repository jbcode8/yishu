<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-19
 * Time: 下午2:35
 */
//+------------------------------------------------
//|	问答模块_积分兑换表_模型(CURD)
//+------------------------------------------------
//|	Author: tangyong <695841701@qq.com>
//-------------------------------------------------


namespace Ask\Model;

class CreditGoodsModel extends AskModel{
    /**
     *	自动验证
     */
    protected $_validate = array(
        array('name', 'require', '商品名称不能为空!', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('credit_price', 'require', '兑换积分不能为空!', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('total', 'require', '总数不能为空!', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('sponsor_name', 'rqeuire', '供应商名称不能为空!', self::MUST_VALIDATE, 'regex', selft::MODEL_BOTH),
    );

    /**
     *	自动完成
     */
    protected $_auto = array(
        array('input_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_UPDATE),
    );
} 