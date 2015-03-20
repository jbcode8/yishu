<?php

return array(

    //开启模板调试模式
    'TMPL_CACHE_ON'=>false,
    
	 //路由
    /*'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
		//'/^(\w+)-(\d+)\/^(\w+)--(\d+)$/'=>'FrontHot/index?cat=:2&ct1=:4',
		'FrontInit' =>'Index/index',
		'hot' => 'FrontHot/index',//热拍首页
		'yugao' => 'FrontNotice/index',//预告
		'help' => 'FrontHelp/index',//帮助中心
		'zclist' => 'FrontLtopic/index',//专场列表
		'/goods-(\d+)$/' => 'FrontData/index?id=:1',//拍品详情页
		'/so-(.*)$/' => 'FrontSearch/index?keyword=:1',//搜索
		'/^(\w+)~(\d+)-(\d+)$/'=>'FrontHot/index?cat=:2&ct=:3',//属性筛选
		//'/^(\w+)-(\d+)-(\d+)$/'=>'FrontHot/index?cat=:2&ct=:3',//属性筛选
		'/^(\w+)$/'=>'FrontHot/index?param=:1',//分类
		'/^(\w+)-(\d+)-(\d+)$/'=>'FrontHot/index?cat=:2&attrid=:3',//属性 全部 字样
		'/zhuanchang-(\d+)$/' => 'FrontTopic/index?id=:1',//拍品详情页
        '/^(\w+)-(\d+)$/' => 'FrontHot/index?cat=:2',//热拍大分类
		
    ),*/


	'LOAD_EXT_CONFIG' =>'route,debug,db',
		
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
        '{CSS_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/css/Jishou/',
        '{IMG_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/img/Jishou/',
        '{JS_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/js/Jishou/',
        '{APP_PATH}' => 'http://www.yishu.com/',
        '{PLUGIN_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/plugin/Jishou/',
        '{UPLOAD_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Uploads/Jishou/',
    ),
		//common里面的一样的
     'ALI_CONFIG' => array(
        'partner' => '2088211696548312',   // PID；
        'key' => 'pxyp25hv3kgw2y5q3jljkxbnjo3kj8oh', // Key
        'sign_type' => strtoupper('MD5'),
        'input_charset' => 'utf-8',
        //'cacert' => getcwd().'\\cacert.pem',
        'transport' => 'http',
    ),

      'ALI_INFO' => array(

        //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email'=>'15601791750@163.com',

        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url'=>'http://www.yishu.com/guwan/pay/notifyurl',

        //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'return_url'=>'http://www.yishu.com/guwan/pay/returnurl',

        //支付成功跳转到的页面
        'successpage'=> 'http://www.yishu.com/guwan/cart/init/step/3',

        //支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
        'errorpage'=> 'http://www.yishu.com/guwan/cart/init/step/1',
    ),


      	'chinabank_config' => array(
		//商户号
		'mid'=>'23090558',
		//MD5密钥
		'key'=>'ys141017',
		 //这里是异步通知页面url，提交到项目的Index控制器的notifyurl方法；
        'notify_url' => '[url:=http://i.yishu.com/auction/pay/cb_notifyurl]',
        //这里是页面跳转通知url，提交到项目的Index控制器的returnurl方法；
        'return_url' => 'http://i.yishu.com/auction/pay/returnurl',

	),
   
);
