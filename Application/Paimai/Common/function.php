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
/*//加价幅度规则:0-1000元（含）:50元     1001元-5000元（含）:200元     5001元-20000元（含）:500元       20001元-100000元（含）:1000元     100000元以上:5000元
function geteveryprice($money=0){
    $money=intval($money);
    $everyprice=0;
    if($money>=0 && $money<=1000){
        $everyprice=50;
    }elseif($money>1000 && $money<=5000){
        $everyprice=200;
    }elseif($money>5000 && $money<=20000){
        $everyprice=500;
    }elseif($money>20000 && $money<=100000){
        $everyprice=1000;
    }elseif($money>100000){
        $everyprice=5000;
    }else{
        $everyprice=200;
    }
    return $everyprice;
}
*/

//统计:用于后台商品统计,传入商品id,传入要查找的状态,返回状态的数量
function getuserbidstatus($goods_id,$status){
    $where=array(
        'bidstatus_status'=>$status,
        'bidstatus_gid'=>$goods_id,
        );
    return M('PaimaiBidstatus')->where($where)->count();
}
//根据商品金额得到商品所需要的保证金
/*规则:
    1-5000:200
    5001-1W:500
    10001-2W:1000
以后，每递增1W，增加500元

*/
function getneedmoney($money=0){
    $money=intval($money);
    $needmoney=200;
    if($money<=50000){
        $needmoney=0;
    }else{
        $needmoney=floor($money/10000)*500+500;
    }
    return format_money($needmoney);
}
function getneedmoney_bak($money=0){
    $money=intval($money);
    $needmoney=200;
    if($money<=5000){
        $needmoney=0;//修改保证金
    }elseif($money>5000 && $money<=10000){
        $needmoney=500;
    }else{
        $needmoney=floor($money/10000)*500+500;
    }
    return format_money($needmoney);
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
        if($i==(strlen($username)-1)||$i==(strlen($username)-2)||$i==(strlen($username)-3)){
            $str.=$username[$i];
        }
    }
    return $str;
}

/*
    前台hot页面商品子分类:传入大分类id,显示小分类带html

    <a href="#">国画2</a>
    <a href="#">书法</a>
    <a href="#">油画</a>
    <a href="#">玉器</a>
    <a href="#">瓷器</a>
    <a href="#">木器</a>
    <a href="#">寿山石</a>
*/
function frontgetgoodssubcat($cat_pid){
    $subcat_where=array(
        'cat_pid'=>$cat_pid,
        'cat_show_in_front'=>1,
    );
    $subcat_arr=M("PaimaiCategory")->where($subcat_where)->order("cat_grade desc")->select();
    $str="";
    foreach ($subcat_arr as $k => &$v) {
        $str.="<a href='".$v['cat_spell'].".html'>".$v['cat_name']."</a>";
    }
    return $str;
}
function goodssc($id){
	return M('paimai_collect')->where(array('collect_goodsid'=>$id))->count();
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
    exit;
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


//传入yishu_paimai_order_info中的id返回这个订单下面的商品
function getOrdergoodsByOrderinfo($orderinfo_id)
{
    $order = M("PaimaiOrder")->where('order_orderinfoid=' . $orderinfo_id)->select();
    $str = "";
    foreach ($order as $k => $v) {
        $str .= "<tr>";
        $str .= "<td>" . $v['order_goodssn'] . "</td>";
        $str .= "<td><a href='/paimai/goods-".$v['order_goodsid'].".html'>" . $v['order_goodsname'] . "</a></td>";
        $str .= "<td>" . $v['order_bidnum'] . "</td>";
        $str .= "<td>" . $v['order_goodsnowprice'] . "</td>";
        $str .= "</tr>";
    }

    return $str;
}

//
function getRegionNameByid($id)
{
    //return M("Region")->where('id=' . $id)->getField("name");
    return M("Region")->where(array('id' => $id))->getField('name');
}

//传入拍卖专场的id,返回这个专场下的拍品数
function getGoodscount($id)
{
    return M('PaimaiGoods')->where("goods_isshow =1 and goods_specialid=" . $id)->count();
}

//传入拍卖专场的id,返回这个专场下的商品的拍卖总次数
function getSpecialgoodstime($special_id)
{
    
    $sql="select sum(goods_bidtimes) count from yishu_paimai_goods where goods_specialid=".$special_id;
    
     $count=M("PaimaiGoods")->query($sql);

	 return $count[0]['count'];
}


//截取中文字符串
function substr_CN($str, $mylen)
{
    //echo "eee";exit;
    return mb_substr($str, 0, $mylen, 'utf-8');
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


function get_attr_value($goodsattr_attrid,$selectedattr_value=0,$attrid=0)
{
    //limit为前台分类对应的属性显示多少个属性值
   
    //三表联查
    $sql="select goodsattr_id,goodsattr_value,goodsattr_spell,cat_attr_catid,goodsattr_attrid,attr_spell from yishu_paimai_goodsattr left join yishu_paimai_cat_attr on goodsattr_attrid=cat_attr_attrid left join yishu_paimai_attribute on attr_id=goodsattr_attrid where goodsattr_attrid=$goodsattr_attrid and goodsattr_goodsid=0 and goodsattr_id<=140";
    $goodsattr=M()->query($sql);
	
   if($goodsattr[0]['goodsattr_attrid']==$attrid){
        $str = "<a href='".U('/paimai/'.$goodsattr[0]['attr_spell']."-".$goodsattr[0]['cat_attr_catid']."-".$goodsattr[0]['goodsattr_attrid'])."' class='check_status'>全部</a>";
    }else{
        $str = "<a href='".U('/paimai/'.$goodsattr[0]['attr_spell']."-".$goodsattr[0]['cat_attr_catid']."-".$goodsattr[0]['goodsattr_attrid'])."'>全部</a>";
    }
    foreach ($goodsattr as $k => $v) {
        //匹配属性值 加红
		
        if($selectedattr_value==$v['goodsattr_id']){
            $str .= "<a class='check_status' href='" .U("/paimai/". $v['goodsattr_spell']."~".$v['cat_attr_catid']."-".$v['goodsattr_id']) . "'>" . $v['goodsattr_value'] . "</a>";
        }else{
            $str .= "<a href='" .U("/paimai/". $v['goodsattr_spell']."~".$v['cat_attr_catid']."-".$v['goodsattr_id']) . "'>" . $v['goodsattr_value'] . "</a>";
        }
    }
    return $str;
}

/*
 * (算法)
 * 二维数组去除重复值
 */
function array_unique_fb($array2D)
{
    $arr = array();
    //把二维数组转成一维
    foreach ($array2D as $k => $v) {
        $arr[$v['goodsattr_id']] = $v['goodsattr_value'];
    }
    //去除一维数组重复值
    $temp = array_unique($arr); //去掉重复的字符串,也就是重复的一维数组
    //生成二维数组
    $tag = 0;
    foreach ($temp as $k => $v) {
        $arrarr[$tag]['goodsattr_id'] = $k;
        $arrarr[$tag]['goodsattr_value'] = $v;
        $tag++;
    }
    return $arrarr;
}

/*
 * 商品编辑页面显示商品属性
 * 通过商品类型id和商品id得到本商品对应的属性
 */
/*function getGoodsAttrlist($goodstype_id,$goods_id){
    $arr=M("PaimaiGoodsattr")->join("yishu_paimai_attribute on yishu_paimai_goodsattr.goodsattr_attrid=yishu_paimai_attribute.attr_id")->field("attr_name,goodsattr_value,attr_id")->where("attr_goodstypeid=".$goodstype_id." and goodsattr_goodsid=".$goods_id." and goodsattr_isshow=1")->select();
    $str="<table class='m_table'>";
    foreach($arr as $k=>$v){
        $str.="<tr><td>".$v['attr_name']."<input type='hidden' name=''></td>";
    }
    //p($arr);
}*/

// 不显示IP地址的后两段
function hideIp($ip)
{
    return preg_replace('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', "$1.$2.*.*", $ip);
}

//根据专场id来获得专场下面拍品图片和id,首页中用到,
function getGoodsBySpecialid($special_id)
{
    $where['goods_isshow'] = 1;
    $where['goods_specialid'] = $special_id;
    $field = array(
        'recordid',
        'goods_id'
    );
    $goods = M('PaimaiGoods')->field($field)->where($where)->order("goods_id desc")->limit(5)->select();
	
    $str = "";
    if ($goods) {
        foreach ($goods as $k => $v) {
            $goods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            $goods[$k]['url'] = U("/paimai/goods-".$goods[$k]['goods_id']);
            $str .= "<li><a href='" . $goods[$k]['url'] . "' target='_blank'><img width='40' height='40' src='" . $goods[$k]['thumb'] . "'></a></li>";
        }
    } else {

        $str .= "<li style='height:40px;'>暂时还没有拍品</li>";
    }


    return $str;
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