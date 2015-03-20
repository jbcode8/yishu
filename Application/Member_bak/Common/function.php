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
function hiddenMobile($mobile)
{
    return preg_replace('/(\d{3})[\d]{4}(\d{4})/', '$1****$2', $mobile);
}

//截取中文字符串,订单显示商品名称用到
function substr_CN($str, $mylen)
{
    //echo "eee";exit;
    return mb_substr($str, 0, $mylen, 'utf-8');
}

/***************************开发函数,上线可以删除start*************************/
function p($arr)
{
    echo "<pre>";
    print_r($arr);
    exit;
}

function v($arr)
{
    echo "<pre>";
    print_r($arr);
}
/***************************开发函数,上线可以删除end*************************/