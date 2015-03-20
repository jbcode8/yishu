<?php
// +----------------------------------------------------------------------
// | 系统公共库文件
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

const ADDON_PATH   =   './Addons/';



//加价幅度规则:0-1000元（含）:50元     1001元-5000元（含）:200元     5001元-20000元（含）:500元       20001元-100000元（含）:1000元     100000元以上:5000元
function geteveryprice($money=0){
    $money=intval($money);
    $everyprice=0;
    if($money>=0 && $money<=1000){
        $everyprice=50.00;
    }elseif($money>1000 && $money<=5000){
        $everyprice=200.00;
    }elseif($money>5000 && $money<=20000){
        $everyprice=500.00;
    }elseif($money>20000 && $money<=100000){
        $everyprice=1000.00;
    }elseif($money>100000){
        $everyprice=5000.00;
    }else{
        $everyprice=200.00;
    }
    return $everyprice;
}


//通过PID查询出子类的集合
function catebaike($cate, $name='child', $pid = 0) {
	$arr = array();
	foreach ($cate as $v) {
		if ($v['pid'] == $pid) {
			$v[$name] = catebaike($cate, $name, $v['cid']);
			$arr[] = $v;
		}
	}
	return $arr;
}
/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 (单位:秒)
 * @return string
 */
function ucenter_encrypt($data, $key, $expire = 0) {
    $key  = md5($key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char =  '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x=0;
        $char  .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data,$i,1)) + (ord(substr($char,$i,1)))%256);
    }
    return str_replace('=', '', base64_encode($str));
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是ucenter_encrypt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string
 */
function ucenter_decrypt($data, $key){
    $key    = md5($key);
    $x      = 0;
    $data   = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data   = substr($data, 10);
    if($expire > 0 && $expire < time()) {
        return '';
    }
    $len  = strlen($data);
    $l    = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char  .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 生成随机字符串
 * @param int $len 生成位数,默认6个字符
 * @param int $type 1所有,2英文,3数字
 * @return string
 */
function salt($len = 6,$type=1){
    switch($type){
        case 1:
            $chars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()';
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            break;
        case 3:
            $chars = '1234567890';
            break;
    }
    $str = '';
    for( $i = 0; $i < $len; $i++ ){
        $str .= $chars[mt_rand( 0, strlen($chars) -1 )];
    }
    return $str;
}

/**
 * 检测权限
 * @param $rule
 * @param $type
 * @param string $mode
 * @return bool
 */
function checkRule($rule, $type=AuthRuleModel::RULE_URL, $mode='url'){
    if(IS_ROOT){
        return true;//管理员允许访问任何页面
    }
    static $Auth    =   null;
    if (!$Auth) {
        $Auth       =   new \Org\Util\Auth();
    }
    if(!$Auth->check($rule,UID,$type,$mode)){
        return false;
    }
    return true;
}

/**
 * 检查是否登录
 * @return int
 */
function is_login(){
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}

/**
 * 检查是否登录
 * @return int
 */
function admin_is_login(){
    $user = session('admin_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('admin_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
    }
}


/**
 * 判断是否为超级管理员用户
 * @param int $uid
 * @return bool
 */
function is_administrator($uid = null){
    $uid = is_null($uid) ? admin_is_login() : $uid;
    return $uid && (intval($uid) === C('USER_ADMINISTRATOR'));
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string $name 格式 [模块名]/接口名/方法名
 * @param  array|string $vars 参数
 * @return mixed
 */
function api($name,$vars=array()){
    $array     = explode('/',$name);
    $method    = array_pop($array);
    $classname = array_pop($array);
    $module    = $array? array_pop($array) : 'Common';
    $callback  = $module.'\\Api\\'.$classname.'Api::'.$method;
    if(is_string($vars)) {
        parse_str($vars,$vars);
    }
    return call_user_func_array($callback,$vars);
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data) {
    //数据类型检测
    if(!is_array($data)){
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 字符串转换为数据,主要第二个参数请根据你需要分割的符号进行调整,默认,
 * @param string $str  要分割的字符串
 * @param string $glue 分割符
 * @return array
 */
function str2arr($str, $glue = ','){
    return explode($glue, $str);
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param  array  $arr  要连接的数组
 * @param  string $glue 分割符
 * @return string
 */
function arr2str($arr, $glue = ','){
    return implode($glue, $arr);
}


/**
 * 对查询结果集进行排序
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param string $sortby 排序类型   asc 正向排序   desc 逆向排序  nat 自然排序
 * @return array|bool
 */
function list_sort_by($list,$field, $sortby='asc') {
    if(is_array($list)){
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ( $refer as $key=> $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pk  主键
 * @param string $pid 父 id 字段
 * @param string $child 子集
 * @param int $root
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[$data[$pk]] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][$data[$pk]] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 获取菜单深度
 * @param $id 起始 id 值
 * @param array $array 需要计数的数组
 * @param int $i 计数开始数
 * @param string $pk 主键 id 字段
 * @param string $parentid 父级 id 字段
 * @return int
 */
function get_level($id, $array = array(), $i = 0,$pk='id',$parentid='pid') {
    foreach ($array as $value) {
        if ($value[$pk] == $id) {
            if ($value[$parentid] == '0')
                return $i;
            $i++;
            return get_level($value[$parentid], $array, $i,$pk,$parentid);
        }
    }
}

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook,$params=array()){
    \Think\Hook::listen($hook,$params);
}

/**
 * 获取插件类的类名
 */
function get_addon_class($name){
    $class = "Addons\\{$name}\\{$name}Addon";
    return $class;
}

/**
 * 获取插件类的配置文件
 */
function get_addon_config($name){
    $class = get_addon_class($name);
    if(class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    }else {
        return array();
    }
}
/**
 * 基于数组创建目录和文件
 * @param $files
 */
function create_dir_or_files($files){
    foreach ($files as $key => $value) {
        if(substr($value, -1) == '/'){
            mkdir($value);
        }else{
            @file_put_contents($value, '');
        }
    }
}

/**
 * widget里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * @return string
 */
function addons_url($url, $param = array()){
    $url        = parse_url($url);
    $case       = C('URL_CASE_INSENSITIVE');
    $addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if(isset($url['query'])){
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = array(
        '_addons'     => $addons,
        '_controller' => $controller,
        '_action'     => $action,
    );
    $params = array_merge($params, $param); //添加额外参数

    return U('Addons/execute', http_build_query($params));
}

/**
 * 生成select树
 * @param $array
 * @param $select_id
 * @param string $value
 * @param string $title
 * @return string
 */
function select_tree($array,$select_id=0,$value='catid',$title='title'){
    //树型结构处理
    $tree = new \Org\Util\Tree();
    $tree->icon = array('┃','┣','┗');
    $tree->nbsp = "&nbsp;&nbsp;&nbsp;";
    $str = "<option value='\$$value' \$selected>\$spacer\$$title</option>";
    $tree->init($array);
    return $tree->get_tree(0, $str,$select_id);
}


function _U($url='',$vars='',$suffix=true,$domain=true) {
    // 解析URL
    $info   =  parse_url($url);
    $url    =  !empty($info['path'])?$info['path']:ACTION_NAME;
    if(isset($info['fragment'])) { // 解析锚点
        $anchor =   $info['fragment'];
        if(false !== strpos($anchor,'?')) { // 解析参数
            list($anchor,$info['query']) = explode('?',$anchor,2);
        }
        if(false !== strpos($anchor,'@')) { // 解析域名
            list($anchor,$host)    =   explode('@',$anchor, 2);
        }
    }elseif(false !== strpos($url,'@')) { // 解析域名
        list($url,$host)    =   explode('@',$info['path'], 2);
    }
    // 解析子域名
    if(isset($host)) {
        $domain = $host.(strpos($host,'.')?'':strstr($_SERVER['HTTP_HOST'],'.'));
    }elseif($domain===true){
        $domain = $_SERVER['HTTP_HOST'];
        if(C('APP_SUB_DOMAIN_DEPLOY') ) { // 开启子域名部署
            $domain = $domain=='localhost'?'localhost':'www'.strstr($_SERVER['HTTP_HOST'],'.');
            // '子域名'=>array('模块[/控制器]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                $rule   =   is_array($rule)?$rule[0]:$rule;
                if(false === strpos($key,'*') && 0=== strpos($url,$rule)) {
                    $domain = $key.strstr($domain,'.'); // 生成对应子域名
                    $url    =  substr_replace($url,'',0,strlen($rule));
                    break;
                }
            }
        }
    }

    // 解析参数
    if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
        parse_str($vars,$vars);
    }elseif(!is_array($vars)){
        $vars = array();
    }
    if(isset($info['query'])) { // 解析地址里面参数 合并到vars
        parse_str($info['query'],$params);
        $vars = array_merge($params,$vars);
    }

    // URL组装
    $depr = C('URL_PATHINFO_DEPR');
    if($url) {
        if(0=== strpos($url,'/')) {// 定义路由
            $route      =   true;
            $url        =   substr($url,1);
            if('/' != $depr) {
                $url    =   str_replace('/',$depr,$url);
            }
        }else{
            if('/' != $depr) { // 安全替换
                $url    =   str_replace('/',$depr,$url);
            }
            // 解析模块、控制器和操作
            $url        =   trim($url,$depr);
            $path       =   explode($depr,$url);
            $var        =   array();
            $var[C('VAR_ACTION')]       =   !empty($path)?array_pop($path):ACTION_NAME;
            $var[C('VAR_CONTROLLER')]   =   !empty($path)?array_pop($path):CONTROLLER_NAME;
            if($maps = C('URL_ACTION_MAP')) {
                if(isset($maps[strtolower($var[C('VAR_CONTROLLER')])])) {
                    $maps    =   $maps[strtolower($var[C('VAR_CONTROLLER')])];
                    if($action = array_search(strtolower($var[C('VAR_ACTION')]),$maps)){
                        $var[C('VAR_ACTION')] = $action;
                    }
                }
            }
            if($maps = C('URL_CONTROLLER_MAP')) {
                if($controller = array_search(strtolower($var[C('VAR_CONTROLLER')]),$maps)){
                    $var[C('VAR_CONTROLLER')] = $controller;
                }
            }
            if(C('URL_CASE_INSENSITIVE')) {
                $var[C('VAR_CONTROLLER')]   =   parse_name($var[C('VAR_CONTROLLER')]);
            }
            $module =   '';

            if(!empty($path)) {
                $var[C('VAR_MODULE')]    =   array_pop($path);
            }else{
                if(C('MULTI_MODULE')) {
                    if(MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST')){
                        $var[C('VAR_MODULE')]=   MODULE_NAME;
                    }
                }
            }
            if($maps = C('URL_MODULE_MAP')) {
                if($_module = array_search(strtolower($var[C('VAR_MODULE')]),$maps)){
                    $var[C('VAR_MODULE')] = $_module;
                }
            }
            if(isset($var[C('VAR_MODULE')])){
                $module =   $var[C('VAR_MODULE')];
                unset($var[C('VAR_MODULE')]);
            }

        }
    }

    if(C('URL_MODEL') == 0) { // 普通模式URL转换
        $url        =   __APP__.'?'.C('VAR_MODULE')."={$module}&".http_build_query(array_reverse($var));
        if(C('URL_CASE_INSENSITIVE')){
            $url    =   strtolower($url);
        }
        if(!empty($vars)) {
            $vars   =   http_build_query($vars);
            $url   .=   '&'.$vars;
        }
    }else{ // PATHINFO模式或者兼容URL模式
        if(isset($route)) {
            $url    =   __APP__.'/'.rtrim($url,$depr);
        }else{
            $module =   defined('BIND_MODULE') ? '' : $module;
            $url    =   __APP__.'/'.($module?$module.MODULE_PATHINFO_DEPR:'').implode($depr,array_reverse($var));
        }
        if(C('URL_CASE_INSENSITIVE')){
            $url    =   strtolower($url);
        }

        if(!empty($vars) && C('URL_VARS')) {
            foreach ($vars as $var => $val){
                if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
            }
        }

        if($suffix) {
            $suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
            if($pos = strpos($suffix, '|')){
                $suffix = substr($suffix, 0, $pos);
            }
            if($suffix && '/' != substr($url,-1)){
                $url  .=  '.'.ltrim($suffix,'.');
            }
        }

        if(!empty($vars) && !C('URL_VARS')) { // 添加参数
            $url .= '?'.http_build_query($vars);
        }
    }
    if(isset($anchor)){
        $url  .= '#'.$anchor;
    }
    if($domain) {
        $url   =  (is_ssl()?'https://':'http://').$domain.$url;
    }
    return $url;
}

/**
 * 字符串截取，支持中文和其他编码
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function new_msubstr($str, $start=0, $length, $charset="utf-8", $suffix=false){
    return Org\Util\String::msubstr($str,$start,$length,$charset,$suffix);
}

/**
 * 字符截取 支持UTF8/GBK
 * @param type $string
 * @param type $length
 * @param type $charset
 * @return type
 */
function str_cut_my($str, $start=0, $length, $charset="utf-8", $suffix=false) {
    return Org\Util\String::msubstr($str,$start,$length,$charset,$suffix);
}
/**
 * 获取文章作者
 * @param  integer  $uid 作者ID
 */
function get_author($uid)
{
    return M('Admin')->where(array('id'=>$uid))->getField('nickname');
}

//获取登录状态
function getLoginStatus(){

    // if(!empty($_COOKIE['_userid'])){
    //     $arr = array();
    //     $arr['mid'] = $_COOKIE['_userid'];
    //     $arr['username'] = $_COOKIE['_username'];
    //     return $arr;
    // }

    if(!empty($_COOKIE['mid'])){
        return $_COOKIE;
    }
       
    return false;   
}
//根据客户端IP返回相应城市
function get_ip_city(){
    $ip = new Org\Net\IpLocation('UTFWry.dat'); 
    $area = $ip->getlocation(get_client_ip());
    $cityname = iconv('GBK', 'UTF-8', $area['country']);
if($cityname == '本机地址' || $cityname == '局域网'){
        $cityname = '上海';
    }
    return $cityname;
}

//返回尾部导航
function getFooterNav(){
    $footNav = D('Home/Document')->footerNav();
    return $footNav;
}

function check_verify($code, $id = 1){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}
/**
     * 加密字符串
     * @access static
     * @param string $str 字符串
     * @param string $key 加密key
     * @return string
     */
    function encrypt($str,$key,$toBase64=false){
        $r = md5($key);
        $c=0;
        $v = "";
        $len = strlen($str);
        $l = strlen($r);
        for ($i=0;$i<$len;$i++){
            if ($c== $l) $c=0;
            $v.= substr($r,$c,1) .(substr($str,$i,1) ^ substr($r,$c,1));
            $c++;
        }
        if($toBase64) {
            return base64_encode(self::ed($v,$key));
        }else {
            return self::ed($v,$key);
        }

    }

    /**
     * 解密字符串
     * @access static
     * @param string $str 字符串
     * @param string $key 加密key
     * @return string
     */
    function decrypt($str,$key,$toBase64=false) {
        if($toBase64) {
            $str = self::ed(base64_decode($str),$key);
        }else {
            $str = self::ed($str,$key);
        }
        $v = "";
		$len = strlen($str);
        for ($i=0;$i<$len;$i++){
         $md5 = substr($str,$i,1);
         $i++;
         $v.= (substr($str,$i,1) ^ $md5);
        }
        return $v;
    }


   function ed($str,$key) {
      $r = md5($key);
      $c=0;
      $v = "";
	  $len = strlen($str);
	  $l = strlen($r);
      for ($i=0;$i<$len;$i++) {
         if ($c==$l) $c=0;
         $v.= substr($str,$i,1) ^ substr($r,$c,1);
         $c++;
      }
      return $v;
   }
    /**
     * 获取注册返现金额
     * @param string $uid 用户id
     * 
     */
    function return_amount($uid = '') {
        //查询是否过期 ，如果过期，修改状态
        //查询条件
        $where = array(
            'uid' => $uid,
            'endtime' => array('LT', time()),//判断返现金额是否过期
            'type' => '1'//1为注册返现
            );
        $temp = M('coupons')->where($where)->select();
        if($temp) {
            foreach($temp as $v){
                M('coupons')->where(array('id'=>$v['id']))->setField('status', 0);
            }
        }
        //查询注册返现金额条件
        $where = array(
            'uid' => $uid,
            'type' => 1,//1为注册返现
            'status' => 1//1为未使用
            );
        if($temp = M('coupons')->field('amount')->where($where)->find()){
            return $temp['amount'];
        } else {
            return 0;
        }
    }

    /**
     * 注册返现修改状态
     * @param string $uid 用户id
     * 
     */
    function change_status_false($uid = '') {
		////注册返现不修改状态，只有真正购买商品时才使用
		return true;
        //$where = array(
            //'uid' => $uid,
            //'type' => 1,//1为注册返现
            //'status' => 1//1为未使用
            //);
		
        //if($temp = M('coupons')->where($where)->setField('status', '2')){
            ////获取注册返现金额
            ////$amount = M('coupons')-where(array('uid' => $uid,'type'=>1,'status'=>2))->find('amount');
            //return 1;//成功
        //} else {
            //return 0;//失败
        //}
    }

    
    
    
    
    