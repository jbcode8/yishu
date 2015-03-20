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
        array('im_qq', 'require', '请填写在线QQ！', 1)
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 2, 'function'),
    );

    /**
     * 根据购物车的店铺ID返回数组
     * @param $aryId
     * @param string $field
     * @return mixed
     */
    public function getCartStore($aryId, $field = 'store_id,store_name,region_id,store_owner_name,store_tel,store_code'){
        $ids = implode(',', $aryId);
        $list = $this->field($field)->where(array('store_id'=>array('in', $ids)))->select();
        if($list && is_array($list)){
            foreach($list as $rs){
                $data[$rs['store_id']] = $rs;
            }
        }
        return $data;
    }

    /**
     * 店铺特有的属性：经营类型
     */
    protected $store_type = array(
        array('id' => 1, 'name' => '公司开店'),
        array('id' => 2, 'name' => '个人全职'),
        array('id' => 3, 'name' => '个人全职'),
    );

    /**
     * 组装单选[Radio]元素(tag)
     * @param int $id
     * @return string
     */
    public function tagRadioStoreType($id = 0){
        $html = '';
        foreach($this->store_type as $rs){
            $checked = ($id == $rs['id']) ? ' checked="checked"' : '';
            $html .= '<label><input type="radio" name="store_type" value="'.$rs['id'].'"'.$checked.'> '.$rs['name'].'</label>'.PHP_EOL;
        }
        return $html;
    }

    /**
     * 店铺特有的属性：主要货源
     */
    protected $store_source = array(
        array('id' => 1, 'name' => '线下批发市场'),
        array('id' => 2, 'name' => '实体店拿货'),
        array('id' => 3, 'name' => '分销/代销'),
        array('id' => 4, 'name' => '自己生产'),
        array('id' => 5, 'name' => '代工生产'),
        array('id' => 6, 'name' => '自由公司渠道'),
        array('id' => 7, 'name' => '货源还未确定'),
    );

    /**
     * 组装单选[Radio]元素(tag)
     * @param int $id
     * @return string
     */
    public function tagRadioStoreSource($id = 0){
        $html = '';
        foreach($this->store_source as $rs){
            $checked = ($id == $rs['id']) ? ' checked="checked"' : '';
            $html .= '<label><input type="radio" name="store_source" value="'.$rs['id'].'"'.$checked.'> '.$rs['name'].'</label>'.PHP_EOL;
        }
        return $html;
    }

    /**
     * 返回[store_type | store_source]对应id的name
     * @param $id
     * @param string $field [store_type | store_source]
     * @return bool
     */
    public function get_name($id, $field = 'store_type'){

        $ary = $field == 'store_type' ? $this->store_type : $this->store_source;
        foreach($ary as $rs){
            if($id == $rs['id']){
                return $rs['name'];
            }
        }
        return false;
    }

}
