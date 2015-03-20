<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 产品模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.30
// +----------------------------------------------------------------------

namespace Mall\Model;

class GoodsModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('goods_name', 'require', '请填写产品名称！'),
        array('goods_desc', 'require', '请填写产品描述！'),
        array('default_img', 'require', '请上传产品封面图！'),
        array('goods_price', 'require', '请填写产品价格！'),
        array('market_price', 'require', '请填写市场价格！'),
        array('cate_id', 'require', '请选择类别！'),
        array('brand_id', 'require', '请选择品牌！'),
        array('pay_id', 'require', '请选择支付方式！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 2, 'function'),
    );
}
