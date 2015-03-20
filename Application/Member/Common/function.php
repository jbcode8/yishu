<?php
/**
 * 会员中心函数库
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 sparui. All rights reserved.
 * @link          http://www.sparui.com
 * @license       http://www.sparui.com/license
 */



/**
 * 隐藏手机号码中间4位号码
 * @param $mobile 手机号码
 * @return mixed 返回隐藏中间的4位
 */
function hiddenMobile($mobile){
    return preg_replace('/(\d{3})[\d]{4}(\d{4})/','$1****$2',$mobile);
}