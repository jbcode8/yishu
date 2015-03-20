<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 商铺模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.30
// +----------------------------------------------------------------------

namespace Mall\Model;

class StoreModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('store_name', 'require', '请填写店铺名称！', 2),
        array('store_name', '', '店铺名称已存在！', 2, 'unique'),
        array('cate_id', 'require', '请选择店铺分类！', 1),
        array('store_logo', 'require', '请选择店铺Logo！', 1),
        array('store_banner', 'require', '请选择店铺Banner！', 1),
        array('region_id', 'require', '请选择地区！', 1),
        array('store_address', 'require', '请填写详细地址！', 1),
        array('store_owner_name', 'require', '请填写店主真实姓名！', 1),
        array('store_owner_id', 'require', '请填写店主身份证号码', 1),
        array('store_desc', 'require', '请填写店铺描述！', 1),
        array('store_tel', 'require', '请填写联系电话！', 1),
        array('im_qq', 'require', '请填写在线QQ！', 1),
        array('pay_id', 'require', '请选择支付方式！', 1)
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 2, 'function'),
    );
}
