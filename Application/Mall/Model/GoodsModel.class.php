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
        array('default_img', 'require', '请上传产品图片！'),
        array('goods_price', 'require', '请填写产品价格！'),
        array('market_price', 'require', '请填写市场价格！'),
        array('cate_id', 'require', '请选择类别！'),
        array('brand_id', 'require', '请选择品牌！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 2, 'function'),
    );

    /**
     * 根据购物车的产品ID返回数组
     * @param $aryId
     * @param string $field
     * @return mixed
     */
    public function getCartGoods($aryId, $field = 'goods_id,goods_sn,goods_name,default_img,goods_price,goods_num'){
        $ids = implode(',', $aryId);
        $list = $this->field($field)->where(array('goods_id'=>array('in', $ids)))->select();
        if($list && is_array($list)){
            foreach($list as $rs){
                $data[$rs['goods_id']] = $rs;
            }
        }
        return $data;
    }

    public function getGoodsByCateId($cate_id){
        $cateIds = D('Category')->getSubCategoryId($cate_id);
        $list = $this->field('goods_id,goods_name,default_img,goods_price,goods_num')->where(array('cate_id'=>array('in', $cateIds),'recommend'=>1))->order('create_time desc')->limit(10)->select();
        return $list;
    }

    public function getGoodsCountByCateId($cate_id){
        $cateIds = D('Category')->getSubCategoryId($cate_id);
        $list = $this->where(array('cate_id'=>array('in', $cateIds)))->count();
        return $list;
    }

    /**
     * 产品特有的属性：售后服务
     */
    protected $goods_service = array(
        array('id' => 1, 'name' => '复鉴后再付款'),
        array('id' => 2, 'name' => '专柜联保'),
        array('id' => 3, 'name' => '店铺保修'),
        array('id' => 4, 'name' => '其它')
    );

    /**
     * 产品特有的属性：鉴定证书
     */
    protected $goods_certificate = array(
        array('id' => 1, 'name' => '市级证书'),
        array('id' => 2, 'name' => '省级证书'),
        array('id' => 3, 'name' => '国家级证书'),
        array('id' => 4, 'name' => '其它'),
        array('id' => 5, 'name' => '不带证书')
    );

    /**
     * 产品特有的属性：认证标识
     */
    protected $goods_attest = array(
        array('id' => 1, 'name' => 'CMA'),
        array('id' => 2, 'name' => 'CAL'),
        array('id' => 3, 'name' => 'CNAS/CNAL')
    );

    /**
     * 组装多选框的控件
     * @param $field
     * @param string $id
     * @return string
     */
    public function tagCheckbox($field, $id = ''){
        $html = '';
        if($field == 'goods_service'){
            $ary = $this->goods_service;
        }else if($field == 'goods_certificate'){
            $ary = $this->goods_certificate;
        }else{
            $ary = $this->goods_attest;
        }
        empty($id) OR $aryIds = explode(',', $id);
        foreach($ary as $rs){
            $checked = (isset($aryIds) && in_array($rs['id'], $aryIds)) ? ' checked="checked"' : '';
            $html .= '<label><input type="checkbox" class="'.$field.'" value="'.$rs['id'].'"'.$checked.'> '.$rs['name'].'</label>'.PHP_EOL;
        }
        $html .= '<input type="hidden" id="'.$field.'" name="'.$field.'" value="'.$id.'" />'.PHP_EOL;
        return $html;
    }

    /**
     * 返回对应的名称
     * @param $field
     * @param $id
     * @return array
     */
    public function getCheckboxName($field, $id){
        $retAry = array();
        if($field == 'goods_service'){
            $ary = $this->goods_service;
        }else if($field == 'goods_certificate'){
            $ary = $this->goods_certificate;
        }else{
            $ary = $this->goods_attest;
        }
        $aryIds = explode(',', $id);
        foreach($ary as $rs){
            in_array($rs['id'], $aryIds) AND $retAry[] = $rs['name'];
        }
        return $retAry;
    }
}
