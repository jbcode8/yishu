<?php
// +----------------------------------------------------------------------
// | 系统级配置
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

return array(
    /* 加载扩展配置文件 */
    //'SHOW_ERROR_MSG'        =>  true,  
    'LOAD_EXT_CONFIG' => 'domain,route',
 
    "LOAD_EXT_FILE"=> 'ext',

    /* 图片上传相关配置 */
    'UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Public/upload/', //保存根路径
    ),

    'FILE_UPLOAD_TYPE' => 'local', //上传驱动类型   ftp,sae,local,qiniu,....
    'UPLOAD_TYPE_CONFIG' => array(
        'host'     => '192.168.8.221', //服务器
        'port'     => 21, //端口
        'timeout'  => 90, //超时时间
        'username' => 'yishu', //用户名
        'password' => '123123', //密码
        'domain'   => 'http://img.yishu.com',
    ),

    /* 后台管理员设置 */
    'USER_ADMINISTRATOR' => 1, //超级管理员用户ID

    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Addons' => ADDON_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Home',
    'MODULE_ALLOW_LIST'  => array('Home','Artist','wenda','Ask','baike','guwan','paimai','admin','malladmin','member','mall','Grab','Exhibit','Rebot','Task','ReturnMoney','Jinrong','jishou','mobile','news','Seckill'),
    'MODULE_DENY_LIST'   => array('Common', 'User'),

    /* 调试配置 */
    //'SHOW_PAGE_TRACE' => true,

    /* Cookie设置 */
    'COOKIE_EXPIRE'         =>  0,    // Cookie有效期
    'COOKIE_DOMAIN'         =>  '.yishu.com',      // Cookie有效域名
    'COOKIE_PATH'           =>  '/',     // Cookie路径
    'COOKIE_PREFIX'         =>  '',      // Cookie前缀 避免冲突

    /* SESSION设置 */
    'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session
    'SESSION_OPTIONS'       =>  array(   // session 配置数组 支持type name id path expire domain 等参数
        'domain' => '.yishu.com',
    ),
    'SESSION_TYPE'          =>  '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'        =>  '', // session 前缀
    //'VAR_SESSION_ID'      =>  'session_id',     //sessionID的提交变量



    /* URL配置 */
    'URL_CASE_INSENSITIVE' => false, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 数据库配置 */

    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '172.16.0.15', // 服务器地址
    'DB_NAME'   => 'yishuv2', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'yufumysql!@#',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'yishu_', // 数据库表前缀

    /*
    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => 'localhost', // 服务器地址
    'DB_NAME'   => 'yishu', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'yishu_', // 数据库表前缀
    */

    'DB_V9' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '172.16.0.15', // 服务器地址
        'DB_NAME'   => 'phpcms_v2', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'yufumysql!@#',  // 密码3712601
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'v9_', // 数据库表前缀
    ),
    'DB_MALL' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '172.16.0.15', // 服务器地址
        'DB_NAME'   => 'yishu_mall', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'yufumysql!@#',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'yishu_', // 数据库表前缀
    ),
    'DB_ULTRAX' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '172.16.0.15', // 服务器地址
        'DB_NAME'   => 'ultrax', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'yufumysql!@#',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'pre_', // 数据库表前缀
    ),
	'DB_BSM' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '172.16.0.15', // 服务器地址
        'DB_NAME'   => 'bsm', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'yufumysql!@#',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'bsm_', // 数据库表前缀
    ),
	//第三方 御府御灵御翡梵
	'DB_YUFU' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '172.16.0.15', // 服务器地址
        'DB_NAME'   => 'yufu_163yu', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'yufumysql!@#',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'jade_', // 数据库表前缀
    ),
	'DB_YULIN' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '172.16.0.15', // 服务器地址
        'DB_NAME'   => 'zhubao_mall', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'yufumysql!@#',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'zb_', // 数据库表前缀
    ),
	'DB_YUFEIFAN' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '172.16.0.15', // 服务器地址
        'DB_NAME'   => 'zhonggouyu', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'yufumysql!@#',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'zg_', // 数据库表前缀
    ),
	'DB_LANPO' => array(
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '172.16.0.15', // 服务器地址
        'DB_NAME'   => 'hupo', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => 'yufumysql!@#',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'hp_', // 数据库表前缀
    ),


    /* MongoDB 配置 */
    'DB_MONGO'  => array(
        'DB_TYPE'   => 'mongo', // 数据库类型
        'DB_HOST'   => '192.168.8.221', // 服务器地址
        'DB_NAME'   => 'yishu', // 数据库名
        'DB_USER'   => 'yishu', // 用户名
        'DB_PWD'    => '123123',  // 密码
        'DB_PORT'   => '27017', // 端口
        'DB_PREFIX' => 'yishu_', // 数据库表前缀
    ),

    /* Memcache缓存设置 */
    'MEMCACHE_HOST'     => '192.168.8.221',
    'MEMCACHE_PORT'     => '11211',

    /* 数据缓存设置 */
//    'DATA_CACHE_TYPE'       =>  'Memcache',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator

    /* 模板解析定义 */
    'TMPL_PARSE_STRING' => array(
        /*
        '{CSS_PATH}' => (is_ssl()?'https://':'http://').'test.yishu.com/Public/css/',
        '{IMG_PATH}' => (is_ssl()?'https://':'http://').'test.yishu.com/Public/img/',
        '{JS_PATH}' => (is_ssl()?'https://':'http://').'test.yishu.com/Public/js/',
        '{APP_PATH}' => 'http://test.yishu.com/',
        '{PLUGIN_PATH}' => (is_ssl()?'https://':'http://').'test.yishu.com/Public/plugin/',
        '{UPLOAD_PATH}' => (is_ssl()?'https://':'http://').'test.yishu.com/Uploads/',
        */

        '{CSS_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/css/',
        '{IMG_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/img/',
        '{JS_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/js/',
        '{APP_PATH}' => 'http://www.yishu.com/',
        '{PLUGIN_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Public/plugin/',
        '{UPLOAD_PATH}' => (is_ssl()?'https://':'http://').'www.yishu.com/Uploads/',
    ),

    'APP_AUTOLOAD_PATH' => '@.TagLib',
    //表示tag类搜索的路径
    'TAGLIB_PRE_LOAD' => 'ys',
    //预加载的tag名
    'TAGLIB_BUILD_IN' => 'ys,cx', //作为内置标签引入
    //BBS相关
    'BBS_URL' => 'http://bbs.yishu.com/',
    'BBS_UPFX' => 'thread-',
    'BBS_USFX' => '-1-1.html',

    //主站地址
    'WEB_URL' => 'http://www.yishu.com/',
	'WEB_URL_R' => 'http://www.yishu.com',
    //古玩城地址
    'MALL_URL' => 'http://www.yishu.com/guwan/',
    //藏品商城
    'CANG_URL' => 'http://cang.yishu.com/',
	'ARTIST_URL' => 'http://artist.yishu.com/',
    'GALLERY_URL' => 'http://gallery.yishu.com/',
    'AUCTION_URL' => 'http://auction.yishu.com/',
    'MEMBER_URL' => 'http://i.yishu.com/',
    
    'URL_MODULE_MAP' => array(
        'wenda' => 'Ask',
		'guwan' => 'Mall',

    ),
    //发送邮件服务
    'email_must_be_array' => '电子邮件确认方式必须传入一个数组.',
    'email_invalid_address' => '无效的电子邮件地址: %s',
    'email_attachment_missing' => '无法定位此电子邮件附件: %s',
    'email_attachment_unreadable' => '无法打开此附件: %s',
    'email_no_recipients' => '必须包含收件人(To)、抄送人(Cc)或暗送人(Bcc)',
    'email_send_failure_phpmail' => '无法使用PHP mail()发送电子邮件. 您的服务器可能没有配置用此方法发邮件.',
    'email_send_failure_sendmail' => '无法使用PHP Sendmail发送邮件. 您的服务器可能没有配置用此方法发邮件.',
    'email_send_failure_smtp' => '无法用PHP SMTP发送邮件. 您的服务器可能没有配置用此方法发邮件.',
    'email_sent' => '您的信件已经被成功的发送了,所使用的协议是: %s',
    'email_no_socket' => '无法打开套接字(socket)以使用Sendmail. 请检查设置.',
    'email_no_hostname' => '您没有指定SMTP主机名.',
    'email_smtp_error' => '出现SMTP错误: %s',
    'email_no_smtp_unpw' => '错误: 您必须指定SMTP用户名和密码.',
    'email_failed_smtp_login' => '发送AUTH LOGIN命令失败. 错误为: %s',
    'email_smtp_auth_un' => '验证用户名失败. 错误为: %s',
    'email_smtp_auth_pw' => '验证密码失败. 错误为: %s',
    'email_smtp_data_failure' => '无法发送数据: %s',
    'email_exit_status' => '退出状态代码为: %s',

	/** 支付宝配置参数 */
    'ALI_CONFIG' => array(
        'partner' => '2088211696548312',   // PID；
        'key' => 'pxyp25hv3kgw2y5q3jljkxbnjo3kj8oh', // Key
        'sign_type' => strtoupper('MD5'),
        'input_charset' => 'utf-8',
        //'cacert' => getcwd().'\\cacert.pem',
        'transport' => 'http',
    ),

    /** 支付宝信息 */
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

	/* 银行卡配置 */
	'BANK_CONF' => array(
		1=>'中国银行',
		2=>'中国工商银行',
		3=>'中国建设银行',
		4=>'中国农业银行',
		5=>'交通银行',
		6=>'招商银行',
		7=>'北京银行',
		8=>'浦发银行'
	),
);