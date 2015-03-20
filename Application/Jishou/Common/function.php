<?php

// +----------------------------------------------------------------------
// | 拍卖模块所需函数库
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
/*
    传入管理员id返回其它信息
*/
function getadmin($uid){
    $admin=M('Admin')->find($uid);
    return $admin['nickname'];
}
function getrecruiter($recruiter_id){
    $recruiter=M('PaimaiRecruiter')->find($recruiter_id);
    return $recruiter['recruiter_name'];
}
function getseller($seller_id){
    $seller=M('PaimaiSeller')->find($seller_id);
    return $seller['seller_name'];
}

/*规则end*/
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



function menus_cate ($cate, $name='child', $pid = 0) {
	$arr = array();
	foreach ($cate as $v) {
		if ($v['pid'] == $pid) {
			$v[$name] = menus_cate($cate, $name, $v['id']);
			$arr[] = $v;
		}
	}
	return $arr;
}
function cates($cate, $html = '┗', $pid = 0, $level = 0) {
	$arr = array();
	foreach ($cate as $v) {
		if ($v['pid'] == $pid) {
			$v['level'] = $level + 1;
			$v['html'] = str_repeat($html,$level);
			$arr[] = $v;
			$arr = array_merge($arr, cates($cate, $html, $v['id'], $level + 1));
		}
	}
	return $arr;
}

function catename($id){
	return M('paimai_cate')->where(array('id'=>$id))->getField('name');
}

//开发函数p
function p($arr)
{
    echo "<pre>";
    print_r($arr);
    echo '</pre>';
}

function img($img){
	$m = explode(',', $img);
	return $m[0].$m[1];
}
//开发函数v
function v($arr)
{
    echo "<pre>";
    print_r($arr);
}

//格式化资金变成  **.00 **.12
function format_money($money=0,$bit=2){
	return number_format($money, $bit, '.', '');
}


//截取中文字符串
function substr_CN($str, $mylen)
{
    //echo "eee";exit;
    return mb_substr($str, 0, $mylen-1, 'utf-8')."...";
}

//得到昵称 传入id得到昵和称

function getNickName($mid)
{
	 //$= M()->db(1, 'yishu')->table("yishuv2.yishu_paimai_order")->where(array('order_uid' => $uid, 'order_status' => array('NEQ',2)))->select();
    $data = D('Member')->field('nickname')->find($mid);
	//$data=M()->table("bsm.bsm_member")->field('nickname')->find($mid);;
	//p(data);
    if ($data) {
        return $data['nickname'];
    }
    return false;
}



//取得explode后的第一个元素值
function explode_one($cut ,$str){
	$str_arr = explode($cut, $str);
	return $str_arr[0];
}

/*
 *根据用户id获取用户手机号码
 */
function get_mobile($uid){
    if($mobile = M()->table('bsm.bsm_member')->where(array('mid' => $uid))->getField('mobile')){
        return $mobile;
    } else {
        return false;
    }
}

/*
 * 发送邮件方法
 */
function alipay_send_email($user = 'feiniutest@163.com', $pwd = 'abcd123', $tomail = '663642331@qq.com',$url, $arr = array()) {
    Vendor('Email.Email');
    $mail = new \Email();
    $config['smtp_pass'] = 25; //本机发送忽略
    $config['protocol'] = 'smtp';
    $config['smtp_host'] = 'smtp.163.com';
    $config['smtp_user'] = $user; //本机发送忽略
    $config['smtp_pass'] = $pwd; //本机发送忽略
    $config['mailtype'] = "html";
    $config['charset'] = 'utf-8';
    $mail->initialize($config);
    //发送到指定邮箱
    //$mail->from('service1@service.feiniu.com', 'FriendLink');//本机发送忽略
    $mail->from($user, '中国艺术网');
    //$this->reply_to('you@example.com', 'Your Name');//邮件回复地址. 如果没有提供这个信息，将会使用"from()"函数中的值
    $mail->to($tomail); //多个用逗号分开,最后一个逗号要去掉
    //$mail->cc($tomail);//抄送
    //$mail->bcc();//暗送
    $mail->subject('中国艺术网邮件');
    $key = urlencode(base64_encode($tomail.'^'.time()));
    //$url = U('member/index/editemail');
    $time = date('Y-m-d H:i:s');

    $content = "
        您好，".$tomail." ：<br /><br />
        恭喜你已经拍得".$arr['goods_name'].", 请在".$arr['goods_endtime']."之前到用户中心付款,逾期将扣除保证金。<br /><br /><a href=".$url.">点击进入用户中心</a><br /><br />
        发件时间：$time<br /><br />
        此邮件为系统自动发出的，请勿直接回复。<br /><br />";
    $mail->message($content);
    $email = $mail->send();
    //输出结果
    //echo $mail->print_debugger();

    return $email;
}

/*
 * 发送邮件方法
 */

function get_email($uid){
    if($email = M()->table('bsm.bsm_member')->where(array('mid' => $uid))->getField('email')){
        return $email;
    } else {
        return false;
    }
}
//银行信息
function bank_info($id){
	return M('paimai_bank')->where(array('id'=>$id))->find();
}

//获取资金流类型
function get_recharge_style($style_id){
	switch($style_id){
		case 0:return '充值';break;
		case 1:return '支付宝支付';break;
		case 2:return '没拍到拍品返回保证金';break;
		case 3:return '拍品到期没付款扣除保证金';break;
		case 4:return '拍商品扣除保证金';break;
		case 5:return '随价格上涨补充保证金';break;
		case 6:return '银联在线支付';break;
		case 7:return '提现记录';break;
		default:break;
	}
}