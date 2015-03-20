<?php

// +----------------------------------------------------------------------
// | 鉴定模块所需函数库
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------

/**
 * 鉴定分类 下拉控件
 */
function selectIdentifyCategory($cid = ''){
    
    if(!S('IdentifyCategory')){
        D('IdentifyCategory')->createIdentifyCategoryCache();
    }
    $arr = S('IdentifyCategory'); // 获取缓存的序列化数组
    
    // 组合下拉控件的HTML
    echo '<select name="category" id="category">'.PHP_EOL.'<option value="">请选择类别</option>';
    foreach ($arr as $k => $v) {
        echo '<option value="'.$v['id'].'"'.(($v['id']==$cid)?' selected="selected"':'').'>'.$v['name'].'</option>';
    }
    echo '</select>';
}

/**
 * 获取分类名称 基于缓存数组和ID
 */
function getIdentifyCategoryName($cid){
    
    if(!S('IdentifyCategory')){
        D('IdentifyCategory')->createIdentifyCategoryCache();
    }
    $arr = S('IdentifyCategory'); // 获取缓存的序列化数组    
    
    return $arr[$cid]['name'];
}

/**
 * 鉴定专家 下拉控件
 */
function selectIdentifyExpert($eid = ''){
    
    if(!S('IdentifyExpert')){
        D('IdentifyExpert')->createIdentifyExpertCache();
    }
    $arr = S('IdentifyExpert'); // 获取缓存的序列化数组
    
    // 组合下拉控件的HTML
    echo '<select name="expertid" id="expertid">'.PHP_EOL.'<option value="0">本站专家</option>';
    foreach ($arr as $k => $v) {
        echo '<option value="'.$v['id'].'"'.(($v['id']==$eid)?' selected="selected"':'').'>'.$v['username'].'</option>';
    }
    echo '</select>';
}

/**
 * 获取专家名称 基于缓存数组和ID
 */
function getIdentifyExpertName($eid){
    
    if(!S('IdentifyExpert')){
        D('IdentifyExpert')->createIdentifyExpertCache();
    }
    $arr = S('IdentifyExpert'); // 获取缓存的序列化数组   
    
    if(empty($eid)){
        return '本站专家';
    }
    return $arr[$eid]['username'];
}

/**
* 将字符串转换为数组
*
* @param	string	$data	字符串
* @return	array	返回数组格式，如果，data为空，则返回空数组
*/
function str2array($data) {
    if($data == '') return array();
    @eval("\$array = $data;");
    return $array;
}
/**
 * 定位模板文件路径
 * @param string $templateFile 文件名
 * @return string
 */
function parseTemplateFile($templateFile = '') {
    //$theme = 'Default';
    $basePath = 'Template';
    define('APP_TMPL_PATH', $basePath);
    define('THEME_PATH', $basePath);
    if ('' == $templateFile) {
        // 如果模板文件名为空 按照默认规则定位
        $templateFile = $basePath. __MODULE__  . '/' . __CONTROLLER__  . C('TMPL_FILE_DEPR') . __ACTION__ . C('TMPL_TEMPLATE_SUFFIX');
    } elseif (false === strpos($templateFile, C('TMPL_TEMPLATE_SUFFIX'))) {
        // 解析规则为 分组@模板主题:模块:操作
        if (strpos($templateFile, '@')) {
            list($group, $templateFile) = explode('@', $templateFile);
            $basePath = $basePath . $group . '/';
        } else {
            $basePath = $basePath . __MODULE__ . '/';
        }
        $path = explode(':', $templateFile);
        $action = array_pop($path);
        $module = !empty($path) ? array_pop($path).C('TMPL_FILE_DEPR') : '';

        $templateFile = $basePath . $module . $action . C('TMPL_TEMPLATE_SUFFIX');
    }
    if (!file_exists_case($templateFile))
        throw_exception(L('_TEMPLATE_NOT_EXIST_') . '[' . $templateFile . ']');
    return $templateFile;
}


function getNickName($mid){
    $data = D('Member')->field('nickname')->find($mid);
    if($data){
        return $data['nickname'];
    }
    return false;
}

function getGroupName($groupId){
    if(!S('MemberGroup'))D('MemberGroup')->caches();
    $group = S('MemberGroup');
    return $group[$groupId]['name'];
}

function get_area($pid = 0){
    S('Area') ? S('Area') : D('Area')->caches();
    $region = S('Area');
    $list = array();
    foreach ($region as $key => $value) {
        if ($pid == $value['parentid'])
            $list[] = $value;
    }
    return $list;
}

function get_avatar($size='big',$mid){
    if(!$mid)$mid = session('mid');
    return '';//service('Passport')->get_avatar($mid,$size);
}

/**
 * 多图数组处理并序列化
 * @param array $arr
 * @return string 序列化后数组
 */
function array_images($arr){
    $arr_m = array();
    foreach ($arr['fileurl'] as $key => $value) {
        $arr_m[] = array('fileurl'=>$value,'filename'=>$arr['filename'][$key]);
    }
    return serialize($arr_m);
}

/**
 * 加密解密
 * @param type $string 明文 或 密文  
 * @param type $operation DECODE表示解密,其它表示加密  
 * @param type $key 密匙  
 * @param type $expiry 密文有效期  
 * @return string 
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;
    // 密匙
    $key = md5(($key ? $key : C("AUTHCODE")));
    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确  
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // substr($result, 0, 10) == 0 验证数据有效性
        // substr($result, 0, 10) - time() > 0 验证数据有效性  
        // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性  
        // 验证数据有效性，请看未加密明文的格式  
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码  
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

function getChild($pid,$array){
    foreach ($array as $k => $v) {
        if($v['parentid'] == $pid){
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
 * 获取所有子栏目id
 * @param int $id
 * @param array $array
 * @param array $r
 */
function get_child_id($id,$array = array(),&$r){
    foreach ($array as $v) {
        if ($v['parentid'] == $id) {
            $r[] = $v['id'];
            get_child_id($v['id'],$array,$r);
        }
    }
}


/**
 * 将数组key转换成id或其它指定字段
 * @param array $array
 * @param string $field
 * @return array
 */
function array_id($array,$field='id'){
    foreach ($array as $k => $v) {
        $arr[$v[$field]] = $v;
    }
    return $arr;
}

/**
 * 生成上传附件验证
 * @param $args   参数
 */
function upload_key($args) {
    $auth_key = md5(C("AUTHCODE") . $_SERVER['HTTP_USER_AGENT']);
    $authkey = md5($args . $auth_key);
    return $authkey;
}

function url_compare($url, $route){
    ksort($url);
    $url_keys=array_keys($url);
    ksort($route);
    $route_keys=array_keys($route);
    if ($url_keys!=$route_keys) {
        return false;
    }
    $flag = true;
    foreach ($route as $_key=>$_val) {
        if (false === strpos($_val, '~')) {
            if ($url[$_key] != $_val) {
                $flag = false;
                break;
            }
        }
    }
    return $flag;
}

function parseUrl($url) {
    $depr = C('URL_PATHINFO_DEPR');
    $var  =  array();
    if(false !== strpos($url,'?')) { // [分组/模块/操作?]参数1=值1&参数2=值2...
        $info   =  parse_url($url);
        $path = explode($depr, $info['path']);
        parse_str($info['query'],$var);
    }elseif(strpos($url, $depr)){ // [分组/模块/操作]
        $path = explode($depr, $url);
    }else{ // 参数1=值1&参数2=值2...
        parse_str($url, $var);
    }
    if(isset($path)) {
        $var[C('VAR_ACTION')] = array_pop($path);
        if(!empty($path)) {
            $var[C('VAR_MODULE')] = array_pop($path);
        }
        if(!empty($path)) {
            $var[C('VAR_GROUP')]  = array_pop($path);
        }
    }
    return $var;
}

/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $options cookie参数
 * @return mixed
 */
function sitecookie($name, $value='', $option=null) {
    // 默认设置
    $config = array(
        'prefix'    =>  C('COOKIE_PREFIX'), // cookie 名称前缀
        'expire'    =>  C('COOKIE_EXPIRE'), // cookie 保存时间
        'path'      =>  C('COOKIE_PATH'), // cookie 保存路径
        'domain'    =>  C('COOKIE_DOMAIN'), // cookie 有效域名
    );
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        elseif (is_string($option))
            parse_str($option, $option);
        $config     = array_merge($config, array_change_key_case($option));
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE))
            return;
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {// 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }
    $name = $config['prefix'] . $name;
    if ('' === $value) {
        if(isset($_COOKIE[$name])){
            $value =    $_COOKIE[$name];
            if(0===strpos($value,'think:')){
                $value  =   substr($value,6);
                return array_map('urldecode',json_decode(MAGIC_QUOTES_GPC?stripslashes($value):$value,true));
            }else{
                return $value;
            }
        }else{
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie 并加密
            $value = authcode($value, "", C("AUTHCODE"));
            
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}
/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string) {
    if (!is_array($string))
        return addslashes($string);
    foreach ($string as $key => $val)
        $string[$key] = new_addslashes($val);
    return $string;
}
// 调用接口服务
function X($name, $params = array(), $domain = 'Service') {
    //创建一个静态变量，用于缓存实例化的对象
    static $_service = array();
    $app = C('DEFAULT_APP');
    //如果已经实例化过，则返回缓存实例化对象
    if (isset($_service[$domain . '_' . $app . '_' . $name]))
        return $_service[$domain . '_' . $app . '_' . $name];
    //载入文件
    $class = $name . $domain;
    import("{$domain}.{$name}{$domain}", APP_PATH . 'Lib');
    //服务不可用时 记录日志 或 抛出异常
    if (class_exists($class)) {
        $obj = new $class($params);
        $_service[$domain . '_' . $app . '_' . $name] = $obj;
        return $obj;
    } else {
        return false;
    }
}
/**
 * 使用递归的方式删除
 * @param type $value
 * @return type
 */
function stripslashes_deep($value) {
    if (is_array($value)) {
        $value = array_map('stripslashes_deep', $value);
    } elseif (is_object($value)) {
        $vars = get_object_vars($value);
        foreach ($vars as $key => $data) {
            $value->{$key} = stripslashes_deep($data);
        }
    } else {
        $value = stripslashes($value);
    }

    return $value;
}
// 实例化服务
function service($name, $params = array()) {
    if (strtolower($name) == 'passport') {
        $name = C("INTERFACE_PASSPORT");
        if (!$name) {
            $name = "Passport";
        }
    }
    return X($name, $params, 'Service');
}
/**
 * 字符截取 支持UTF8/GBK
 * @param type $string
 * @param type $length
 * @param type $charset
 * @return type
 */
function str_cut($string, $length, $charset = 'utf-8') {
	$strlen = strlen($string);
	if($strlen <= $length) return $string;
	$string = str_replace(array(' ','&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵',' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
	$strcut = '';
	if(strtolower($charset) === 'utf-8') {
		$length = intval($length-$length/3);
		$n = $tn = $noc = 0;
		while($n < strlen($string)) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}
			if($noc >= $length) {
				break;
			}
		}
		if($noc > $length) {
			$n -= $tn;
		}
		$strcut = substr($string, 0, $n);
		$strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
	} else {
		$maxi = $length - 1;
		$current_str = '';
		$search_arr = array('&',' ', '"', "'", '“', '”', '—', '<', '>', '·', '…','∵');
		$replace_arr = array('&amp;','&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;',' ');
		$search_flip = array_flip($search_arr);
		for ($i = 0; $i < $maxi; $i++) {
			$current_str = ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
			if (in_array($current_str, $search_arr)) {
				$key = $search_flip[$current_str];
				$current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
			}
			$strcut .= $current_str;
		}
	}
	return $strcut;
}

function check_email($email){
    if(preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)){
        return TRUE;
    }
    return false;
}



function parsePhpsmsUrl($catid,$id){
    return "http://www.yishu.com/show-{$catid}-{$id}-1.html";
}

function show_list($catid=20){
    return "http://www.yishu.com/index.php?m=content&c=index&a=lists&catid={$catid}";
}

function getUserName($mid){
    $data = M('Member')->field('username')->find($mid);
    return $data['username'];
}

/**
 * 获取评论数
 * @param $comment
 */
function get_list_comments($commentid) {
	global $db;
	if(!$commentid){return false;}
	$db = pc_base::load_model('comment_model');
    $r = $db->get_one(array('commentid'=>$commentid));  
	if($r){
		echo $r['total'];	
	}else{
		echo '0';
	}
}

/**
 * 组装生成ID号
 * @param $modules 模块名
 * @param $contentid 内容ID
 * @param $siteid 站点ID
 */
function id_encode($modules,$contentid, $siteid) {
	return urlencode($modules.'-'.$contentid.'-'.$siteid);
}

//获取26个英文字母
function abc(){
    $arr = array();
    for($i=1,$j='a',$n='A';$i<27;$i++){
        $arr[$i]['k'] = $j++;
        $arr[$i]['v'] = $n++;   
    }
    return $arr;
}

// 底部的导航菜单 2014/1/16 Rain.Zen
function footHtml(){
	
	$ary = M(C('PHPCMS_DB').'.category', 'v9_')->field(array('catname','url'))->where(array('parentid' => 1))->select();
	if(empty($ary)) return $html;

	$html = '<div class="f_nav">'.PHP_EOL;
	foreach($ary as $rs){
		$html.= '<a href="'.$rs['url'].'" target="_blank">'.$rs['catname'].'</a>'.PHP_EOL;
	}
	$html.= '</div>'.PHP_EOL.'<div class="block">'.PHP_EOL.'<p>Copyright Reserved 2013 &nbsp;YISHU.COM &nbsp;版权所有</p>'.PHP_EOL;
	$html.= '<p>电信与信息服务业务经营许可证 '.PHP_EOL;
	$html.= '<a href="http://www.miibeian.gov.cn/" target="_blank">沪ICP备14002523号</a></p>'.PHP_EOL.'</div>'.PHP_EOL;
	$html.= '<div style="display:none">';
	$html.= '<script type="text/javascript" src="http://s13.cnzz.com/stat.php?id=5791326&web_id=5791326"></script></div>';

	return $html;
}

// 对会员操作的表单数据过滤
function stripTags($post){
	if(is_array($post) && !empty($post)){
		foreach($post as $k => $v){
			$post[$k] = strip_tags($v);
		}
		return $post;
	}
}
