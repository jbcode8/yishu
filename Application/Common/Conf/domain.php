<?php
/**
 * 子域名布署
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 sparui. All rights reserved.
 * @link          http://www.sparui.com
 * @license       http://www.sparui.com/license
 */

return array(
    'APP_SUB_DOMAIN_DEPLOY' =>  false,   // 是否开启子域名部署
    'APP_SUB_DOMAIN_RULES'  =>  array(  // 子域名部署规则
        'passport' => 'Member/Passport',   // 通行证
        'my' => 'Member', // 会员中心
        'ask' => 'Ask', // 问答
        'wiki' => 'Baike', // 百科
        'mall' => 'Mall', // 商城
        'gallery' => 'Gallery', // 画廊
    ),
);