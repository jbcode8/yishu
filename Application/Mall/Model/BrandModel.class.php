<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 品牌模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.23
// +----------------------------------------------------------------------

namespace Mall\Model;

class BrandModel extends MallBaseModel{
    
    // 自动验证
    protected $_validate = array(
        array('brand_name', 'require', '请填写品牌名称！'),
        array('brand_name', '', '此品牌名称已存在！', 0, 'unique'),
        array('brand_logo', 'require', '请选择品牌LOGO！'),
        array('listorder', 'number', '排列顺序必须大于零(0)！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 2, 'function'),
    );

    /**
     * 根据店铺ID返回品牌数组
     * @param $sid
     * @return array
     */
    public function aryBrand($sid = 0){
        $map['status'] = 2;
        empty($sid) OR $map['store_id'] = $sid;
        $ary = $this->field(array('brand_id', 'brand_name', 'brand_logo', 'listorder'))->where($map)->order('listorder ASC')->select();
        $ret = array();
        if($ary){
            foreach($ary as $rs){
                $ret[$rs['brand_id']] = $rs;
            }
        }
        return $ret;
    }

    // 商品页面的顶级类别
    public function tagSelect($ary, $bid = 0, $tag = 'brand_id'){
        $html = '';
        if($ary){
            $html .= '<select name="'.$tag.'" id="'.$tag.'">'.PHP_EOL.'<option value="">请选择</option>'.PHP_EOL;
            foreach($ary as $rs){
                $sel = $rs['brand_id'] == $bid ? ' selected="selected"' : '';
                $html .= '<option value="'.$rs['brand_id'].'"'.$sel.'>'.$rs['brand_name'].'</option>'.PHP_EOL;
            }
            $html .= '</select>'.PHP_EOL;
        }
        return $html;
    }

}