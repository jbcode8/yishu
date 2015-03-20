<?php
	return array(
		'DB_FIELDS_CACHE'=>false,
        'TMPL_CACHE_ON'=>false,
        'LOAD_EXT_CONFIG' =>'route',
	    'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '172.16.0.15', // 服务器地址
        'DB_NAME'   => 'yishuv2', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'yufumysql!@#',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'yishu_', // 数据库表前缀
		//'DB_DNS'    => 'mysql://root:yufumysql!@#@'
		'DB_DNS'    =>'mysql:host=172.16.0.15;dbname=zhihui;port=3306',
		'DB_CHARSET'=>'utf8',

		'TMPL_PARSE_STRING' => array(
		'{CSS_OTHER_PATH}'=>(is_ssl()?'https://':'http://').'www.yishu.com/Public/css/',
        '{CSS_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/css/Jianding/',
        '{IMG_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/img/Jianding/',
        '{JS_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/js/Jianding/',
        '{APP_PATH}' => 'http://www.yishu.com/',
        '{PLUGIN_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/plugin/Jianding/',
        '{UPLOAD_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Uploads/Jianding/',
    ),
		);