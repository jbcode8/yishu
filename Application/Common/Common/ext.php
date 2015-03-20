<?php
/**
 * 扩展函数库
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 yishu. All rights reserved.
 */

/**
 * 获取返回url路径
 */
function getReturnUrl(){
    if(isset($_GET['returnUrl'])){
        $returnUrl = $_GET['returnUrl'];
    }else{
        $referer = $_SERVER['HTTP_REFERER'];
        if($referer){
            $returnUrl = $_SERVER['HTTP_REFERER'];
        }else{
            $returnUrl = (is_ssl()?'https://':'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        }
    }
    return urlencode($returnUrl);
}

/**
 * 判断是否为email
 * @param $email
 * @return mixed
 */
function is_email($email){
    return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$email);
}

/**
 * 判断是否为手机号码
 * @param $mobile
 * @return int
 */
function is_mobile($mobile){
    return preg_match('/^1[358]\d{9}$/',$mobile);
}

/**
 * 登录类型
 * 返回1:帐号,2:邮箱,3:手机号码,4:ID
 */
function login_type($username){
    if(is_email($username)){
        return 2;
    }elseif(is_mobile($username)){
        return 3;
    }
    return 1;
}


/**
 * 获取用户信息
 * @param $uid
 * @param bool $is_username
 * @param null $tag
 * @return array  id,username,email,mobile
 */
function memberInfo($uid,$tag = null,$is_username = false){
    $ucMember = new \User\Model\UcenterMemberModel();
    $info = $ucMember->info($uid,$is_username);
    $mInfo = array('id'=>$info[0],'username'=>$info[1],'email'=>$info[2],'mobile'=>$info[3]);
    if($tag == null){
        return $mInfo;
    }else{
        return $mInfo[$tag];
    }
}

/**
 * 获取店铺信息
 * @param $store_id 必需。商铺id
 * @param string $field 可选。默认是全部字段(*)；可为单一字段；也可为多字段(逗号分开)
 * @return string|array 返回结果取决于参数$field：为单一字段则返回此字段的值(string)，而为空或多字段则返回数组(array)
 */
function mallStoreInfo($store_id, $field = '*'){
    if(empty($store_id)) return '';
    $storeModel = new \Mall\Model\StoreModel();
    $info = $storeModel->field($field)->find($store_id);
    if(empty($info)) return '';
    if($field === '*' || strpos($field, ',') !== false){
        return $info;
    }else{
        return $info[$field];
    }
}

/**
 * 获取产品信息
 * @param $goods_id 必需。产品
 * @param string $field 可选。默认是全部字段(*)；可为单一字段；也可为多字段(逗号分开)
 * @return string|array 返回结果取决于参数$field：为单一字段则返回此字段的值(string)，而为空或多字段则返回数组(array)
 */
function mallGoodsInfo($goods_id, $field = '*'){
    if(empty($goods_id)) return '';
    $goodsModel = new \Mall\Model\GoodsModel();
    $info = $goodsModel->field($field)->find($goods_id);
    if(empty($info)) return '';
    if($field === '*' || strpos($field, ',') !== false){
        return $info;
    }else{
        return $info[$field];
    }
}

function currentSelect($url,$class = 'select'){
    $urls = explode('?',$url);
    $urls = explode('/',$urls[0]);
    $module = $urls[0];
    $controller = isset($urls[1])?$urls[1]:'Index';
    $action = isset($urls[2])?$urls[2]:'index';
    if($module == MODULE_NAME && $controller == CONTROLLER_NAME && $action == ACTION_NAME){
        return $class;
    }
}