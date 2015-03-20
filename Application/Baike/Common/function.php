<?php
// +----------------------------------------------------------------------
// | 艺术百科公用方法文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

/**
 * 返回所有分类按层级关系排序的一维数组
 * @param  array  $cate   需要整理的数据源
 * @param  string $html   填充字符串
 * @param  int    $pid    父级ID标识
 * @param  int    $level  当前分类的等级  0 表示顶级
 * @return array
 */
function category_table($cate, $html = '', $pid = 0, $level = 0){
    $arr = array();
    foreach($cate as $v){
        if($v['pid'] == $pid){
            $v['level'] = $level + 1;
            $v['html'] = str_repeat($html, $level);
            $arr[] = $v;
            $arr = array_merge($arr, category_table($cate, $html, $v['cid'], $level+1));
        }
    }
    return $arr;
}

/**
 * 返回所有分类层级关系的多维数组
 * @param  array   $cate   需要整理的数据源
 * @param  string  $name   子级数组键名
 * @param  int     $pid    父级ID标识
 * @return array
 */
function category_tree($cate, $name = 'child', $pid = 0){
    $arr = array();
    foreach($cate as $v){
        if($v['pid'] == $pid){
            $v[$name] = category_tree($cate, $name = 'child', $v['cid']);
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
 * 返回所有的父级类别
 * @param  array    $cate   需要整理的数据源
 * @param  string   $id     当期分类ID标识
 * @param  bool     $isIds  是否只返回父级cid  缺省值为 false
 * @return array
 */
function category_parents($cate, $id, $isIds=false){
    $arr = array();
    foreach($cate as $v){
        if($v['cid'] == $id){
            $arr[] = $isIds ? $v['cid'] : $v;
            $arr = array_merge(category_parents($cate, $v['pid'], $isIds), $arr);
        }
    }
    return $arr;
}

/**
 * 返回所有的子级类别
 * @param  array    $cate    需要整理的数据源
 * @param  string   $pid     当期分类ID标识
 * @return array
 */
function category_childids($cate, $pid){
    $arr = array();
    foreach($cate as $v){
        if($v['pid'] == $pid){
            $arr[] = $v['cid'];
            $arr = array_merge($arr,category_childids($cate, $v['cid']));
        }
    }
    return $arr;
}



/**
 * 返回所有的子级类别
 * @param  array    $cate    需要整理的数据源
 * @param  string   $pid     当期分类ID标识
 * @return array
 */
function category_childs($cate, $pid){
    $arr = array();
    foreach($cate as $v){
        if($v['pid'] == $pid){
            $arr[] = $v;
            $arr = array_merge($arr,category_childs($cate, $v['cid']));
        }
    }
    return $arr;
}

/**
 * 获取默认所有子级的第一个元素
 * @param $cate
 * @param $pid
 * @return array
 */
function category_childs_one($cate, $pid){
    $arr = array(); $search = array();
    foreach($cate as $v){
        if($v['pid'] == $pid && !in_array($pid, $search)){
            $search[] = $pid;
            $arr[] = $v;
            $arr = array_merge($arr, category_childs($cate, $v['cid']));
        }
    }
    return $arr;
}


/**
 * 返回顶级分类的ID标识和分类名称
 * @param  array  $cate
 * @param  int    $cid
 * @return array
 */
function getParentIdName($cate, $cid){
    foreach($cate as $v){
        if($v['cid'] == $cid){
            if($v['pid'] == 0){
               $res['cid'] = $v['cid'];
               $res['name'] = $v['name'];
               return $res;
            }else{
               $res = getParentIdName($cate, $v['pid']);
               if($res) return $res;
            }
        }
    }
}

/**
 * 调试数据方法
 * @param $arr
 */
function pr($arr){
    echo "<pre>";
        print_r($arr);
    echo "</pre>";
}

/**
 * 获取下拉列表的树形菜单，默认为获取一级分类
 * @param $cid
 * @param bool  $super
 * @return string
 */
function getMenu($cid = 0, $super = true){
    if($super){
        $data = D('Category')->supercate();
    }
    else{
        $data = D('Category')->catetable();
    }
    foreach($data as $v){
        $dataList[$v['cid']] = $v;
        $dataList[$v['cid']]['selected'] = ($cid == $v['cid'])?'selected':'';
    }
    //树型结构处理
    $menu = new \Org\Util\Tree();
    $menu->icon = array('┃','┣','┗');
    $menu->nbsp = "&nbsp;";
    $str = "<option value='\$cid' \$selected>\$spacer\$name</option>";
    $menu->init($dataList);
    return $menu->get_tree(0, $str);
}

/**
 * 通过分类ID返回分类的名称
 * @param  $cid
 * @return string
 */
function get_category($cid){
    $name = D('Category')->get_name($cid);
    if($name)
        return $name;
    return $cid;
}

/**
 * 锁定状态的显示转换
 * @param  $locked
 * @return string
 */
function get_locked($locked){
    $arr = array('未锁定','已锁定',);
    if(isset($arr[$locked]))
        return $arr[$locked];
    return $locked;
}

/**
 * 词条属性的显示转换
 * @param  $type
 * @return string
 */
function get_doc_type($type){
    $arr = array('普通词条','推荐词条', '热门词条','精彩词条');
    if(isset($arr[$type]))
        return $arr[$type];
    return $type;
}

/**
 * 审核状态的显示转换
 * @param $visible
 * @return string
 */
function get_visible($visible){
    $arr = array('未审核','已审核');
    if(isset($arr[$visible]))
        return $arr[$visible];
    return $visible;
}

/**
 * 优秀版本的显示转换
 * @param $excellent
 * @return string
 */
function get_edition_excellent($excellent){
    $arr = array('否','是');
    if(isset($arr[$excellent]))
        return $arr[$excellent];
    return $excellent;
}

/**
 * 版本修改类型的显示转换
 * @param $type
 * @param $reason
 * @return string
 */
function get_edition_reason($type, $reason){
    $arr = array('1'=>'编辑全文', '2'=>'编辑分段', '3'=>'编辑摘要', '4'=>'编辑标签');
    if(isset($arr[$type]))
        return '【'.$arr[$type]."】　".$reason;
    return $reason;
}

/**
 * 正则判断URL格式是否正确
 */
function checkURL($url){
    $reg  = '/^(http|https|ftp)\://([a-zA-Z0-9\.\-]+(\:[a-zA-Z0-9\.&amp;%\$\-]+)*@)?((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])|([a-zA-Z0-9\-]+\.)*[a-zA-Z0-9\-]+\.[a-zA-Z]{2,4})(\:[0-9]+)?(/[^/][a-zA-Z0-9\.\,\?\'\\/\+&amp;%\$#\=~_\-@]*)*$/';
    return preg_match($reg,$url);
}

/**
 * 批量操作JS函数
 */
function bulkActionJS(){
    $module_controller = MODULE_NAME.'/'.CONTROLLER_NAME.'/';
    //获取选中ID字符串
    $getIds = <<<ids
            function getIds(){
                var ids = '',i=0;
                $("input.ids:checked").each(function(){
                    id = $(this).val();
                    ids += i==0?id:(','+id);
                    i++;
                });
                if(ids)
                    return ids;
                return 0;
            }
ids;

    //更改锁定状态
    $url = U($module_controller.'alertlock');
    $alterlock = <<<lock
            function alertlock(locked){
                var ids = getIds();
                if(!ids){ alert('没有选择任何信息'); return false;}
                $.post("$url",{locked:locked,ids:ids}, function(msg){
                    alert(msg);window.location.reload();
                });
            }
lock;

    //删除所有选
    $url = U($module_controller.'delete');
    $delselected = <<<del
            function delselected(){
                var ids = getIds();
                if(!ids){
                    alert('没有选择任何信息'); return false;
                }
                else{
                    if(confirm("删除后无法恢复，确定要删除吗？")){
                        $.post("$url",{ids:ids}, function(msg){
                            alert(msg);window.location.reload();
                        });
                    }
                }
            }
del;

    //审核所选
    $url = U($module_controller.'audit');
    $audit = <<<audit
            function audit(){
                var ids = getIds();
                if(!ids){
                    alert('没有选择任何信息'); return false;
                }
                else{
                    if(confirm("确定要通过所选项的审核吗？")){
                        $.post("$url",{ids:ids}, function(msg){
                            alert(msg);window.location.reload();
                        });
                        }
                }
            }
audit;

    //更改词条类型
    $url = U($module_controller.'doctype');
    $doctype = <<<type
            function doctype(type){
                var ids = getIds();
                if(!ids){ alert('没有选择任何信息'); return false;}
                $.post("$url",{type:type,ids:ids}, function(msg){
                    alert(msg);window.location.reload();
                });
            }
type;

    //优秀版本
    $url = U($module_controller.'alertexcellent');
    $alertexcellent = <<<excellent
            function alertexcellent(excellent){
                var ids = getIds();
                if(!ids){
                    alert('没有选择任何信息'); return false;
                }
                else{
                    $.post("$url",{ids:ids,excellent:excellent}, function(msg){
                        alert(msg);window.location.reload();
                    });
                }
            }
excellent;


    $funcStr = "<script type=\"text/javascript\">";
    $funcStr .= $getIds;
    $funcStr .= $alterlock;
    $funcStr .= $delselected;
    $funcStr .= $audit;
    $funcStr .= $doctype;
    $funcStr .= $alertexcellent;
    $funcStr .= "</script>";
    echo $funcStr;
}

/**
 * 获取用户名
 * @param  $uid     用户ID标识
 * @return string
 */
function get_username($uid){
    $memberInfo = memberInfo($uid);
    return $memberInfo['username'];
}

/**
 * 图片压缩方法
 * @param string  $url      图片路径
 * @param string  $prefix   名称前缀
 * @param int     $width    宽度
 * @param int     $height   高度
 * @param string  $suffix   后缀
 * @return array
 */
function image_compress($url,$prefix='s_',$width=80,$height=60,$suffix=''){
    $result=array ('result'=>false,'tempurl'=>'','msg'=>'提示信息');
    if(!file_exists($url)){
        $result['msg'] =$url. '图片文件不存在';
        return $result;
    }
    $urlinfo=pathinfo($url);
    $ext=strtolower($urlinfo['extension']);
    $tempurl=$urlinfo['dirname'].'/'.$prefix.substr($urlinfo['basename'],0,-1-strlen($ext)).$suffix.'.'.$ext;
    $ext=($ext=='jpg')?'jpeg':$ext;
    $createfunc='imagecreatefrom'.$ext;
    $imagefunc='image'.$ext;
    if(function_exists($createfunc)){
        list($actualWidth,$actualHeight) = getimagesize($url);
        if($actualWidth<$width&&$actualHeight<$height){
            copy($url,$tempurl);
            $result['tempurl']=$tempurl;
            $result['result']=true;
            return $result;
        }
        if ($actualWidth < $actualHeight){
            $width=round(($height / $actualHeight) *$actualWidth);
        } else {
            $height=round(($width/ $actualWidth) *$actualHeight);
        }
        $tempimg=imagecreatetruecolor($width, $height);
        $img = $createfunc($url);
        imagecopyresampled($tempimg, $img, 0, 0, 0, 0, $width, $height, $actualWidth, $actualHeight);
        $result['result']=($ext=='png')?$imagefunc($tempimg, $tempurl):$imagefunc($tempimg, $tempurl, 80);

        imagedestroy($tempimg);
        imagedestroy($img);
        if(file_exists($tempurl)){
            $result['tempurl']=$tempurl;
        }else {
            $result['tempurl']=$url;
        }
    }else{
        copy($url, $tempurl);
        if(file_exists($tempurl)){
            $result['result']= true;
            $result['tempurl']=$tempurl;
        }else {
            $result['tempurl']=$url;
        }
    }
    return $result;
}

/**
 * 获取导航菜单
 */
function getNavigation($cid){
    $model = D('Category');
    $navigation = $model->where(array('navigation'=>1))->field('cid,name,short_name')->limit(11)->select();
    $return = '';
    if($navigation){
        foreach($navigation as $cate){
			$class = "";
			if($cid == $cate['cid']){
				$class = "class='current'";
			}
            $return .= '<a title="'.$cate['cid']."_".$cate['navigation'].'" href="'.U("/baike/{$cate['short_name']}-{$cate['cid']}").'" '.$class.'>'.$cate['name'].'</a>';
        }
    }
    echo $return;
}

/**
 * 反向转义词条的图片信息
 * @param string    $imginfo    图片信息的json字符串
 * @param bool      $first      是否只返回第一张图片的路径
 * @return array/string
 */
function stripslashes_imginfo($imginfo, $first=false){
   $imginfo = stripslashes($imginfo);
   $imgs = json_decode($imginfo, true);
   if($first)
       return $imgs[0]['path'];
    return $imgs;
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
    // Split at all position not after the start: ^
    // and not before the end: $
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
 * 获取数字某位的数值
 * @param $num 要解析的数字
 * @param int $flag=1 ，1 个，2 十，3 百，4 千，5万 ，6 十万， 7 百万........
 * @return int
 */
function get_num($num, $flag=1){
    if(is_int($num) && is_int($flag)){
        $count = strlen($num);
        if($flag > $count)
            return 0;
        if($flag == 1)
            return $num % 10;
        else if ($flag == $count)
            return floor( $num / pow(10,($count-1)));
        else
            return floor($num / pow(10, ($flag-1))) % 10;
    }
    else{
        return 0;
    }
}

/**
 * 去除javascript的特殊字符的处理函数
 * @param $string
 * @return mixed
 */
function stripscript($string){
    $pregfind=array("/<script.*>.*<\/script>/siU",'/on(error|mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i',"/&lt;script.*&gt;.*&lt;\/script.*&gt;/siU");
//    $pregfind=array("/&lt;script.*&gt;.*&lt;\/script.*&gt;/siU",'/on(error|mousewheel|mouseover|click|load|onload|submit|focus|blur)="[^"]*"/i');
    $pregreplace=array('','','',);
    $string=preg_replace($pregfind,$pregreplace,$string);
    return $string;
}

/*
 * 根据用户ID获取用户的昵称
 */
function getMemName($user_id){
    $arr = M()->db(2,'DB_BSM')->table('bsm_member')->where(array('mid'=>$user_id))->find();
    if($arr) {
        return !empty($arr['nickname'])?$arr['nickname']:$arr['username'];
    }
}
