<?php

/**
 * 后台的搜索选项下拉控件(Select)
 * @param array $arrOption 数组格式：'address' => '详细地址'
 * @param string $option 当前选项
 * @return string 返回下拉控件(Select)
 */
function searchSelect($arrOption = array('address' => '详细地址'), $option = ''){
    $str = '<select name="opt">'.PHP_EOL;
    foreach($arrOption as $k => $v){
        $selected = $k == $option ? ' selected="selected"' : '';
        $str.= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'.PHP_EOL;
    }
    $str.= '</select>'.PHP_EOL;
    return $str;
}

/**
 * 将字符串格式的时间转化为Unix时间戳
 * @param $time 字符串时间：例如(2031-03-03 或者 2013-09-09 10:20 或者 2013-09-09 10:20:20)
 * @param string $suffix 后面补齐的字符串 例如(' 00:00:00', '00', '')， 以达到(xxxx-xx-xx xx:xx:xx)这样的格式
 * @return int 返回Unix时间戳
 */
function time2int($time, $suffix = ''){
    if(empty($time)) return '';
    if(empty($suffix)){
        return strtotime($time);
    }else{
        return strtotime($time.$suffix);
    }
}

/**
 * 类别下拉菜单
 * @param int $cate_id
 * @return string
 */
function tagSelectCate($cate_id = 0){
    $tagHtml = '<select name="cate_id">';
    $arrCate = D('GalleryCategory')->getCaches();
    if(!empty($arrCate)){
        foreach($arrCate as $rs){
            $selected = $cate_id == $rs['id'] ? ' selected="selected"' : '';
            $tagHtml.= '<option value="'.$rs['id'].'"'.$selected.'>'.$rs['name'].'</option>';
        }
    }
    $tagHtml.= '</select>';
    return $tagHtml;
}

/**
 * 根据类别ID返回对应字段
 * @param $cate_id
 * @param string $field
 * @return mixed
 */
function getCate($cate_id, $field = 'name'){
    $arr = D('GalleryCategory')->getCaches();
    if(!empty($arr)){
        foreach($arr as $rs){
            if($cate_id == $rs['id']) return $rs[$field];
        }
    }
}

/**
 * 画廊下拉菜单
 * @param int $gid
 * @return string
 */
function tagSelectGallery($gid = 0){
    $tagHtml = '<select id="gid" name="gid">'.PHP_EOL.'<option value=""> -- 请选择</option>';
    $arr = D('GalleryList')->getCaches();
    if(!empty($arr)){
        foreach($arr as $rs){
            $selected = $gid == $rs['id'] ? ' selected="selected"' : '';
            $tagHtml.= '<option value="'.$rs['id'].'"'.$selected.'>'.$rs['name'].'</option>';
        }
    }
    $tagHtml.= '</select>';
    return $tagHtml;
}

/**
 * 根据画廊Id返回对应信息
 * @param $gid
 * @param string $field
 * @return mixed
 */
function getGallery($gid, $field = 'name'){
    $arr = D('GalleryList')->getCaches();
    if(!empty($arr)){
        foreach($arr as $rs){
            if($gid == $rs['id']) return $rs[$field];
        }
    }
}

/**
 * 艺术家下拉菜单
 * @param int $aid
 * @return string
 */
function tagSelectArtist($aid = 0, $gid = 0){
    $tagHtml = '<select name="aid" id="aid">';
    $arr = D('GalleryArtist')->getCaches();
    if(!empty($arr)){
        foreach($arr as $rs){
            $selected = $aid == $rs['id'] ? ' selected="selected"' : '';
            if($gid > 0){
                if($gid == $rs['gid']){
                    $tagHtml.= '<option value="'.$rs['id'].'"'.$selected.'>'.$rs['name'].'</option>';
                }
            }else{
                $tagHtml.= '<option value="'.$rs['id'].'"'.$selected.'>'.$rs['name'].'</option>';
            }
        }
    }
    $tagHtml.= '</select>';
    return $tagHtml;
}

/**
 * 根据大师Id返回对应信息
 * @param $aid
 * @param string $field
 * @return mixed
 */
function getArtist($aid, $field = 'name'){
    $arr = D('GalleryArtist')->getCaches();
    if(!empty($arr)){
        foreach($arr as $rs){
            if($aid == $rs['id']) return $rs[$field];
        }
    }
}

/**
 * 地区下拉菜单
 * @param int $region_id
 * @return string
 */
function tagSelectRegion($region_id = 0){
    $tagHtml = '<select name="region_id">';
    $arr = M('Region')->where(array('pid'=>2))->select();
    if(!empty($arr)){
        foreach($arr as $rs){
            $selected = $region_id == $rs['id'] ? ' selected="selected"' : '';
            $tagHtml.= '<option value="'.$rs['id'].'"'.$selected.'>'.$rs['name'].'</option>';
        }
    }
    $tagHtml.= '</select>';
    return $tagHtml;
}

/**
 * 根据地区ID返回地区名
 * @param $region_id
 * @param string $field
 * @return mixed
 */
function getRegion($region_id, $field = 'name'){
    $arr = M('Region')->where(array('pid'=>2))->select();
    if(!empty($arr)){
        foreach($arr as $rs){
            if($region_id == $rs['id']) return $rs[$field];
        }
    }
}

/**
 * 获取汉字的拼音首字母
 * @param $str
 * @return string
 */
function firstLetter($str){

    $firstChar = ord($str{0});
    if($firstChar >= ord('A') && $firstChar <= ord('z')) return strtoupper($str{0});

    $str1 = iconv("UTF-8","gb2312", $str);
    $str2 = iconv("gb2312","UTF-8", $str1);
    $string = $str2 == $str ? $str1 : $str;
    $asc = ord($string{0}) * 256 + ord($string{1}) - 65536;

    if($asc >= -20319 && $asc <= -20284) return "A";
    if($asc >= -20283 && $asc <= -19776) return "B";
    if($asc >= -19775 && $asc <= -19219) return "C";
    if($asc >= -19218 && $asc <= -18711) return "D";
    if($asc >= -18710 && $asc <= -18527) return "E";
    if($asc >= -18526 && $asc <= -18240) return "F";
    if($asc >= -18239 && $asc <= -17923) return "G";
    if($asc >= -17922 && $asc <= -17418) return "H";
    if($asc >= -17417 && $asc <= -16475) return "J";
    if($asc >= -16474 && $asc <= -16213) return "K";
    if($asc >= -16212 && $asc <= -15641) return "L";
    if($asc >= -15640 && $asc <= -15166) return "M";
    if($asc >= -15165 && $asc <= -14923) return "N";
    if($asc >= -14922 && $asc <= -14915) return "O";
    if($asc >= -14914 && $asc <= -14631) return "P";
    if($asc >= -14630 && $asc <= -14150) return "Q";
    if($asc >= -14149 && $asc <= -14091) return "R";
    if($asc >= -14090 && $asc <= -13319) return "S";
    if($asc >= -13318 && $asc <= -12839) return "T";
    if($asc >= -12838 && $asc <= -12557) return "W";
    if($asc >= -12556 && $asc <= -11848) return "X";
    if($asc >= -11847 && $asc <= -11056) return "Y";
    if($asc >= -11055 && $asc <= -10247) return "Z";

    return '';
}

/**
 * 组装组图
 * @param $aryPics
 * @return array
 */
function buildPics($aryPics){
    $ary = array();
    if(is_array($aryPics) && !empty($aryPics)){
        $aryUrl = $aryPics['url'];
        $aryName = $aryPics['name'];
        foreach($aryUrl as $i => $url){
            $ary[$i] = array('url' => $url, 'name'=> $aryName[$i]);
        }
    }
    return $ary;
}

function jsAryPics($str){
    return $str ? json_encode(unserialize(htmlspecialchars_decode($str))) : '[]';
}

/**
 * 测试打印输出
 * @param $v
 * @param bool $isExit
 */
function prt($v, $isExit = false){
    echo '<pre>'.PHP_EOL;
    print_r($v);
    echo PHP_EOL.'</pre>';
    if($isExit) exit;
}

/**
 * 根据aid获取作家名
 * @param integer $aid 作家aid
 * @return string      作家名
 */
function getName($aid)
{
    return  M('GalleryArtist')->where(array('id'=>$aid))->getField('name');
}

/**
 * 根据gid获取画廊名
 * @param integer $gid 画廊gid
 * @return string      画廊名
 */
function getGname($gid)
{
    return  M('GalleryList')->where(array('id'=>$gid))->getField('name');
}

/**
 * 根据region_id获取地区名称
 * @param integer $region_id 地区id
 * @return string      地区名
 */
function getCity($region_id){
	$cityName = M('Region')->field('name')->where(array('id'=>$region_id))->find();
	return $cityName['name'];

}

/**
 * 根据uid获取用户名
 * @param integer $uid 会员id
 * @return string      会员名
 */
function getUsername($uid)
{
    return  M('UcenterMember')->where(array('id'=>$uid))->getField('username');
}