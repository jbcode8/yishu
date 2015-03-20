<?php
/**
 * Created by PhpStorm.
 * User: jeff
 * 首页静态缓存
 */
return array(
    'HTML_CACHE_ON'     =>    false, // 开启静态缓存
    'HTML_CACHE_TIME'   =>    30,   // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX'  =>    '.html', // 设置静态缓存文件后缀
    'HTML_CACHE_RULES'  =>     array(  // 定义静态缓存规则     // 定义格式1 数组方式
        //'静态地址'    =>     array('静态规则', '有效期', '附加规则'),      // 定义格式2 字符串方式
        //'静态地址'    =>     '静态规则',
        'index:index' =>array('{:action}',30)
    )
);