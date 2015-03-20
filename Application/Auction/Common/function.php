<?php


function p($arr){
    echo "<pre>";
    print_r($arr);exit;
}
function v($arr){
    echo "<pre>";
    print_r($arr);
}

function parsePhpsmsUrl($catid,$id){
    return "http://www.yishu.com/show-{$catid}-{$id}-1.html";
}
function str_cut($str, $start=0, $length, $charset="utf-8", $suffix=false) {
    return Org\Util\String::msubstr($str,$start,$length,$charset,$suffix);
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
//得到昵称 传入id得到昵和称
function getNickName($mid){
    $data = D('Member')->field('nickname')->find($mid);
    if($data){
        return $data['nickname'];
    }
    return false;
}
//截取中文字符串
function substr_CN($str,$mylen){
     //echo "eee";exit;
    return  mb_substr($str, 0, $mylen, 'utf-8');
}


function string2array($data) {
	if($data == '') return array();
	@eval("\$array = $data;");
	return $array;
}

// 不显示IP地址的后两段
function hideIp($ip){
	return preg_replace('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', "$1.$2.*.*", $ip);
}

/**
 * 拍卖分类 下拉控件
 */
function selectAuctionCategory($cid = ''){
    
    if(!S('AuctionCategory')){
        D('AuctionCategory')->createAuctionCategoryCache();
    }
    $arr = S('AuctionCategory'); // 获取缓存的序列化数组
    
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
function getAuctionCategoryName($cid){
    
    if(!S('AuctionCategory')){
        D('AuctionCategory')->createAuctionCategoryCache();
    }
    $arr = S('AuctionCategory'); // 获取缓存的序列化数组    
    
    return $arr[$cid]['name'];
}

/**
 * 竞拍口号 下拉控件
 */
function selectAuctionSlogan($sid = ''){
    
    $arr = D('AuctionData')->arrSlogan(); // 获取模型定义好的数组
    
    // 组合下拉控件的HTML
    echo '<select name="slogan" id="slogan">'.PHP_EOL.'<option value="">请选择你的竞拍口号</option>';
    foreach ($arr as $k => $v) {
        echo '<option value="'.$k.'"'.(($k==$aid)?' selected="selected"':'').'>'.$v.'</option>';
    }
    echo '</select>';
}

/**
 * 获取竞拍口号名称
 */
function getAuctionSloganName($aid){
    
    $arr = D('AuctionData')->arrSlogan(); // 获取模型定义好的数组
    $sloganName = empty($aid) ? '暂无口号' : $arr[$aid];
    
    return $sloganName;
}

/**
 * 数字转化为时间格式
 */
function int2time($int, $format = 0){
    $arrFormat = array('Y-m-d', 'Y-m-d H:i', 'Y-m-d H:i:m', 'Y/m/d', 'Y年m月d日');
    return date($arrFormat[$format], $int);
}

/**
* 获取出价次数
*/
function getPriceCount($aid){
    return D('AuctionPriceRecord')->where(array('aid' => $aid))->count();
}

/**
 * 获取拍卖信息的名称
 */
function getAuctionName($aid){
    $arr = D('AuctionData')->field('`title`')->find($aid);
    if(is_array($arr)){
        return $arr['title'];
    }
}

/*
 *根据机构id获取拍卖机构的名称 
 */
function getAgencyName($agencyid){
    $arr = D('AuctionAgency')->field('`name`')->find($agencyid);
    if(is_array($arr)){
        return $arr['name'];
    }
}

/*
 *根据机构id获取拍卖机构的图片
 */
function getAgencyThumb($agencyid){
    $arr = D('AuctionAgency')->field('`thumb`')->find($agencyid);
    if(is_array($arr)){
        return $arr['thumb'];
    }
}

/*
 *根据机构所属区域id获取区域名称
 */
function getAgencyArea($areaid){
    $arr = D('AuctionArea')->field('`name`')->find($areaid);
    if(is_array($arr)){
        return $arr['name'];
    }
}

/*
 *根据专场id获取拍品的专场名称
 */
function getSpecialName($specialid){
    $arr = D('AuctionMeeting')->field('`name`')->find($specialid);
    if(is_array($arr)){
        return $arr['name'];
    }
}
/*
 *根据拍卖会id获取拍品的拍卖会名称
 */
function getMeetingName($meetingid){
    $arr = D('AuctionMeeting')->field('`name`')->find($meetingid);
    if(is_array($arr)){
        return $arr['name'];
    }
}

/*
 *根据城市id获取城市的名称 
 */
function getAreaName($areaid){
    $arr = D('Region')->field('`name`')->find($areaid);
    if(is_array($arr)){
        return $arr['name'];
    }
}

// 根据产品ID返回ID对应值得数组
function resetGoods($inIds, $field){
	empty($field)?'*':$field;
	$data = D('AuctionGoods')->field($field)->where('id IN('.$inIds.')')->select();
	
	$arrGoods = array();
	if($arrGoods != ''){
		foreach($data as $row){
			$arrGoods[$row['id']] = $row;
		}
	}
	return $arrGoods;
	
}

//根据专场ID，计算拍品个数！
function s_goods_num($specialid){
	
	$arr = D('AuctionExhibit')->where(array('specialid' => $specialid, 'isshow' => 1))->count();
	//p($arr);
    return $arr;
    if($arr){
        return $arr;
    }
}

//根据专场ID，计算已成交拍品个数！
function s_data_num($specialid){
	$where['specialid'] = $specialid;
	$where['endprice'] = array('gt',0);
	$arr = D('AuctionExhibit')->where($where)->count();
	
    if($arr){
        return $arr;
    }
}	

//根据专场ID，该专场的成交额！
function s_total_num($specialid){
	$where['specialid'] = $specialid;
	$where['endprice'] = array('gt',0);
	$arr = D('AuctionExhibit')->where($where)->sum('endprice');
	
    if($arr){
        return $arr;
    }
}
//根据拍卖会ID，计算拍品个数！
function m_goods_num($meetingid){
	
	$arr = D('AuctionExhibit')->where(array('meetingid' => $meetingid))->count();
	
	if($arr){
        return $arr;
    }	
}

//根据拍卖会ID，计算专场个数！
function m_special_num($meetingid){
	$arr = D('AuctionMeeting')->where(array('pid' => $meetingid))->count();
	
	if($arr){
        return $arr;
    }	
}

//根据拍卖会ID，计算该拍卖会成交额！
function m_total_money($meetingid){
	$arr = D('AuctionExhibit')->where(array('meetingid' => $meetingid))->sum('endprice');
	
	if($arr){
        return $arr;
    }	
}

//根据拍卖会ID，计算已成交拍品个数！
function m_data_num($meetingid){
	$where['meetingid'] = $meetingid;
	$where['endprice'] = array('gt',0);
	$arr = D('AuctionExhibit')->where($where)->count();
	
    if($arr){
        return $arr;
    }
}

//根据专场ID，该专场的成交额！
function m_total_num($meetingid){
	$where['meetingid'] = $meetingid;
	$where['endprice'] = array('gt',0);
	$arr = D('AuctionExhibit')->where($where)->sum('endprice');
	
    if($arr){
        return $arr;
    }
}

/*
 *根据大师id获取大师名称
 */
function getArtistName($artistid){
    $arr = D('Artist')->field('`name`')->find($artistid);
    if(is_array($arr)){
        return $arr['name'];
    }
}




/*
 *根据大师id获取大师作品
 */
function getArtistWorks($artistid){
    $arr = D('ArtistWorks')->field('id, name, thumb')->where(array('artistid' => $artistid))->limit(2)->select();
    if(is_array($arr)){
        return $arr;
    }
}

/*
 *根据大师id获取大师简介
 */
function getArtistIntroduce($artistid){
    $arr = D('Artist')->field('`description`')->find($artistid);
    if(is_array($arr)){
        return $arr['description'];
    }
}
