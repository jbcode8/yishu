<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 配送方式模型
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Model;

class DeliveryModel extends MallBaseModel{
    
    // 自动验证
    protected $_validate = array(
        array('delivery_name', 'require', '请填写配送方式名称！', 1),
        array('delivery_name', '', '配送方式名称已存在！', 1, 'unique'),
        array('delivery_desc', 'require', '请填写配送方式描述！', 1),
        array('delivery_price', 'require', '请填写配送方式价格！', 1),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
    );

    /**
     * 根据店铺ID返回品牌数组
     * @param $sid
     * @return array
     */
    public function aryDelivery($sid = 0){
        $map['status'] = 1;
        empty($sid) OR $map['store_id'] = $sid;
        $ary = $this->field(array('delivery_id', 'delivery_name', 'listorder'))->where($map)->order('listorder ASC')->select();
        $ret = array();
        if($ary){
            foreach($ary as $rs){
                $ret[$rs['delivery_id']] = $rs;
            }
        }
        return $ret;
    }

    // 商品页面的顶级类别
    public function tagSelect($ary, $bid = 0, $tag = 'delivery_id'){
        $html = '';
        if($ary){
            $html .= '<select name="'.$tag.'" id="'.$tag.'">'.PHP_EOL.'<option value="">请选择</option>'.PHP_EOL;
            foreach($ary as $rs){
                $sel = $rs[$tag] == $bid ? ' selected="selected"' : '';
                $html .= '<option value="'.$rs[$tag].'"'.$sel.'>'.$rs['delivery_name'].'</option>'.PHP_EOL;
            }
            $html .= '</select>'.PHP_EOL;
        }
        return $html;
    }
}