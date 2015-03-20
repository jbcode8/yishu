<?php
// +----------------------------------------------------------------------
// | 大师频道公用函数文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

/**
 * 获取大师的类别
 * @param bool $html
 * @param string $name
 * @param int $cid
 * @return bool
 */
function getArtistCategory($html=true, $name='cid', $cid=0){
   $category = D('Category')->field('id,name')->where('status=1')->select();
   if(!$category) return false;
   if($html === true){
       $html = '<select name="'.$name.'">';
       $html .= '<option value="">---请选择---</option>';
       foreach($category as  $vo){
            $selected = $vo['id'] == $cid ? 'selected' : '';
            $html .= '<option value="'.$vo['id'].'" '.$selected.'>'.$vo['name'].'</option>';
        }
       $html .= '</select>';
       echo $html;
   }
   else{
       return $category;
   }
}

/**
 * 通过id获取分类的名称
 * @param $cid
 * @return bool
 */
function getArtistCategoryName($cid){
    $name = D('Category')->getFieldById($cid,'name');
    if($name)
        return $name;
    return false;
}

/**
 * @param $award
 * @return bool
 */
function getAwardName($award){
    $name = D('Award')->getFieldById($award,'name');
    if($name)
        return $name;
    return false;
}


/**
 * 通过Id获取艺术家名称
 * @param $aid
 * @return bool
 */
function getArtistName($aid){
    $name = D('Library')->getFieldById($aid,'name');
    if($name)
        return $name;
    return false;
}

/**
 * 获取汉字的拼音首字母
 * @param $str
 * @return string
 */
function firstLetter($str){

    $firstChar = ord($str{0});
    if($firstChar >= ord('A') && $firstChar <= ord('z')) return strtoupper($str{0});

    $str1 = iconv('UTF-8','gb2312', $str);
    $str2 = iconv('gb2312','UTF-8', $str1);
    $string = $str2 == $str ? $str1 : $str;
    $asc = ord($string{0}) * 256 + ord($string{1}) - 65536;

    if($asc >= -20319 && $asc <= -20284) return 'A';
    if($asc >= -20283 && $asc <= -19776) return 'B';
    if($asc >= -19775 && $asc <= -19219) return 'C';
    if($asc >= -19218 && $asc <= -18711) return 'D';
    if($asc >= -18710 && $asc <= -18527) return 'E';
    if($asc >= -18526 && $asc <= -18240) return 'F';
    if($asc >= -18239 && $asc <= -17923) return 'G';
    if($asc >= -17922 && $asc <= -17418) return 'H';
    if($asc >= -17417 && $asc <= -16475) return 'J';
    if($asc >= -16474 && $asc <= -16213) return 'K';
    if($asc >= -16212 && $asc <= -15641) return 'L';
    if($asc >= -15640 && $asc <= -15166) return 'M';
    if($asc >= -15165 && $asc <= -14923) return 'N';
    if($asc >= -14922 && $asc <= -14915) return 'O';
    if($asc >= -14914 && $asc <= -14631) return 'P';
    if($asc >= -14630 && $asc <= -14150) return 'Q';
    if($asc >= -14149 && $asc <= -14091) return 'R';
    if($asc >= -14090 && $asc <= -13319) return 'S';
    if($asc >= -13318 && $asc <= -12839) return 'T';
    if($asc >= -12838 && $asc <= -12557) return 'W';
    if($asc >= -12556 && $asc <= -11848) return 'X';
    if($asc >= -11847 && $asc <= -11056) return 'Y';
    if($asc >= -11055 && $asc <= -10247) return 'Z';

    return '';
}








/**
 * 获取标题的首字母
 * @param $string
 * @return string
 */
function get_letter($string) {
    $charlist = mb_str_split($string);
    return implode(array_map("getfirstchar", $charlist));
}
function mb_str_split($string) {
    $res = preg_split('/(?<!^)(?!$)/u', $string);
    return array($res[0]);
}
function getfirstchar($s0) {
    $fchar = ord(substr($s0, 0, 1));
    if (($fchar >= ord("a") and $fchar <= ord("z"))or($fchar >= ord("A") and $fchar <= ord("Z"))) return strtoupper(chr($fchar));
    $s = iconv("UTF-8", "GBK", $s0);
    $asc = ord($s{0}) * 256 + ord($s{1})-65536;
    if ($asc >= -20319 and $asc <= -20284)return "A";
    if ($asc >= -20283 and $asc <= -19776)return "B";
    if ($asc >= -19775 and $asc <= -19219)return "C";
    if ($asc >= -19218 and $asc <= -18711)return "D";
    if ($asc >= -18710 and $asc <= -18527)return "E";
    if ($asc >= -18526 and $asc <= -18240)return "F";
    if ($asc >= -18239 and $asc <= -17923)return "G";
    if ($asc >= -17922 and $asc <= -17418)return "H";
    if ($asc >= -17417 and $asc <= -16475)return "J";
    if ($asc >= -16474 and $asc <= -16213)return "K";
    if ($asc >= -16212 and $asc <= -15641)return "L";
    if ($asc >= -15640 and $asc <= -15166)return "M";
    if ($asc >= -15165 and $asc <= -14923)return "N";
    if ($asc >= -14922 and $asc <= -14915)return "O";
    if ($asc >= -14914 and $asc <= -14631)return "P";
    if ($asc >= -14630 and $asc <= -14150)return "Q";
    if ($asc >= -14149 and $asc <= -14091)return "R";
    if ($asc >= -14090 and $asc <= -13319)return "S";
    if ($asc >= -13318 and $asc <= -12839)return "T";
    if ($asc >= -12838 and $asc <= -12557)return "W";
    if ($asc >= -12556 and $asc <= -11848)return "X";
    if ($asc >= -11847 and $asc <= -11056)return "Y";
    if ($asc >= -11055 and $asc <= -10247)return "Z";
    return null;
}

/**
 * 获取艺术家的级别称号
 */
function getArtistType($type = false){
    if($type !== false){
        switch($type){
            case 0: return '普通'; break;
            case 1: return '精英'; break;
            case 2: return '推荐'; break;
            default: return '普通'; break;
        }
    }
    return array(
        '0'=>'普通',
        '1'=>'精英',
        '2'=>'推荐',
    );
}

/**
 * 获取艺术家的状态
 * @param bool $status
 * @return array|bool|string
 */
function getArtistStatus($status = false){
    if($status !== false){
        switch($status){
            case 0: return '已锁定'; break;
            case 1: return '未锁定'; break;
            default: return $status; break;
        }
    }
    return array(
        '0'=>'已锁定',
        '1'=>'未锁定',
    );
}
/**
 * 数组按照规则排序
 * @param array $array
 * @return array $newArray
 */
function newArray($array){
    foreach($array as $key=>$v){
       $newArray[$v['letter']][] = 
		   array(
			'name'=>$v['name'],
			'type'=>$v['type'],
			'id'=>$v['id'],
			'recordid'=>$v['recordid'],
			'birthday'=>$v['birthday'], 
			'provinceid'=>$v['provinceid'],
			'cid'=>$v['cid'],
	   );
    }
    return $newArray;
}

/**
 * 根据作品分类id获取分类名称
 * @param intval $cid
 * @return string $newArray
 */
function getCategoryName($cid){
   $category = M('ArtistCategory')->field('name')->where(array('id'=>$cid))->find();
   return $category['name'];
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
 * 根据gid获取画廊名
 * @param integer $gid 画廊gid
 * @return string      画廊名
 */
function getGname($gid)
{
    return  M('GalleryList')->where(array('id'=>$gid))->getField('name');
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
