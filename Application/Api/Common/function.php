<?php

//截取中文字符串
function substr_CN($str, $mylen)
{
    if(ceil(strlen($str)/3)>$mylen){
        return mb_substr($str, 0, $mylen-1, 'utf-8')."...";
    }else{
        return $str;
    }
    
}
//开发函数p
function p($arr)
{
    echo "<pre>";
    print_r($arr);
    exit;
}

//格式化资金变成  **.00 **.12
function format_money($money=0,$bit=2){
    return number_format($money, $bit, '.', '');
}

/*
    些函数为获得用户信息
    $id为用户的id,
    $bit为隐藏的中间位数,默认为隐藏中间三位
    $field为要获得的字段,默认为用户名,如果要手机则传入数据库中对应手机字段就ok
    返回值为要获得的对应字段值,如果传入$bit,则为返回隐藏的字段值
*/
function getUsername($id,$bit=3,$field='username'){
	$username = M('member','bsm_','DB_BSM')->where(array('mid'=>$id))->getField($field);
    if($bit==0){
        return $username;
    }
	return hideusername($username,$bit);
}
//传入用户名进行隐藏
function hideusername($username,$bit=3){
    $str="";
    for ($i=0; $i <strlen($username) ; $i++) { 
        if($i==0||$i==1){
            $str.=$username[$i];
        }
    }
    $str.=str_repeat("*",$bit);
    for ($i=0; $i <strlen($username) ; $i++) { 
        if($i==(strlen($username)-1)||$i==(strlen($username)-2)){
            $str.=$username[$i];
        }
    }
    return $str;
}

//根据商品金额得到商品所需要的保证金
/*规则:
    1-5000:200
    5001-1W:500
    10001-2W:1000
以后，每递增1W，增加500元

*/
function getneedmoney($money = 0){
    $money = intval($money);
    $needmoney = 200;
    if($money <= 50000){
        $needmoney = 0;
    }else{
        $needmoney = floor($money/10000)*500+500;
    }
    return format_money($needmoney);
}
?>