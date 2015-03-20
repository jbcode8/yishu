<?php
// +----------------------------------------------------------------------
// | 问答模块公用文件
// +----------------------------------------------------------------------
// | Author: tangyong <695841701@qq.com>
// +----------------------------------------------------------------------

/**
 * 获取树形目录(多用于添加新的类别或者修改子类别)
 * @param $cate_id 当前类别
 * @param array $data 被检索的数据(默认是总的分类)
 * @param string $model 指明那个模块(默认是总的分类)
 * @return string 返回select控件的option部分，且有选中的状态
 */
function selectCategory($cate_id, $data = array(), $model = ''){

    // 读取分类缓存数据
    (empty($data) && empty($model)) AND $data = D('Category')->getCategoryCache();

    // 数组重构
    foreach($data as $v){
        $dataList[$v['cate_id']] = $v;
        $dataList[$v['cate_id']]['pid'] = $v['parent_id'];
        $dataList[$v['cate_id']]['selected'] = ($cate_id == $v['cate_id']) ? 'selected="selected"' : '';
    }

    // 树型结构处理
    $menu = new \Org\Util\Tree();
    $menu->icon = array('┃','┣','┗');
    $menu->nbsp = "&nbsp;&nbsp;&nbsp;";
    $str = "<option value='\$cate_id' \$selected>\$spacer\$name</option>".PHP_EOL;

    $menu->init($dataList);
    return $menu->get_tree(0, $str);
}

/**
 * 获取类别名(根据类别ID)
 * @param $cate_id 类别ID
 */
function getCateName($cate_id){
    $name = D('Category')->where(array('cate_id'=>$cate_id))->find();
    if($name) {
        return $name['name'];
    }
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
        if($v['parent_id'] == $pid){
            $arr[] = $v['cate_id'];
            $arr = array_merge($arr,category_childids($cate, $v['cate_id']));
        }
    }
    return $arr;
}

function category_tree($cate, $name = 'child', $pid = 0){
    $arr = array();
    foreach($cate as $v){
        if($v['parent_id'] == $pid){
            $v[$name] = category_tree($cate, $name = 'child', $v['cate_id']);
            $arr[] = $v;
        }
    }
    return $arr;
}

//通过cate_id返回当前ID下的所有小分类
function getChildsId ($cate, $cate_id) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $cate_id) {
            $arr[] = $v;
            $arr = array_merge($arr, getChildsId($cate, $v['cate_id']));
        }
    }
    return $arr;
}
//一个子分类ID返回所有父级分类
function getParents ($cate, $id) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['cate_id'] == $id) {
            $arr[] = $v;
            $arr = array_merge(getParents($cate, $v['parent_id']), $arr);
        }
    }
    return $arr;
}
//返回所有问题分类
function cates ($cate, $pid = 0) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $pid) {
            $arr[] = $v;
            $arr = array_merge($arr, cates($cate, $v['cate_id']));
        }
    }
    return $arr;
}
/*
 * 根据问题ID获取回答的个数
 */
function reply_num($qid){
    $reply_num = D('Reply')->where(array('question_id' => $qid))->count();
    if($reply_num > 0) {
        return $reply_num;
    } else {
        return 0;
    }
}

/*
 * 根据TAG值判断是热门回答还是精彩回答
 */
function check_tag($tag){
    $arr = array('','热门回答','精彩回答');
    if(isset($arr[$tag]))
        return $arr[$tag];
    return $tag;

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

/*
 * 根据问题类别ID获取该类别的问题的个数
 */
function cate_num($cate_id){
	
	if($cate = M('category')->field('cate_id')->where("parent_id=$cate_id")->select()){
		foreach($cate as $v){
			$cids .= $v['cate_id'].','; 
		}
		$map['cate_id']  = array('in',substr($cids,0,-1));
		$cate_num = D('Question')->where($map)->count();
	}else{
		$cate_num = D('Question')->where(array('cate_id' => $cate_id))->count();
	}
    if($cate_num > 0) {
        return $cate_num;
    } else {
        return 0;
    }
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

/*
 * 根据用户ID获取用户提问数
 */
function getMemAsk($user_id){
    $arr = D('Question')->where(array('user_id'=>$user_id))->count();
    if($arr) {
        return $arr;
    } else {
        return 0;
    }
}

/*
 * 根据用户ID获取用户回答数
 */
function getMemAnswers($user_id){
    $arr = D('Reply')->where(array('user_id'=>$user_id))->count();
    if($arr) {
        return $arr;
    } else {
        return 0;
    }
}
//截取字符
function subtext($text, $length) {
    if(mb_strlen($text, 'utf8') > $length) 
    return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}
//时间差
function difftime($time){
    $d = (time() - $time) / 60 / 60 /24;
    $h = (time() - $time) / 60 / 60;
    $i = (time() - $time) / 60;
    $s = time() - $time;
    if($d > 20){
        return '20天以上';
    }else if($d > 1){
        return intval($d).'天前';
    }else if($h > 1){
        return intval($h).'小时前';
    }else if($i > 1){
        return intval($i).'分钟前';
    }else if($s > 1){
        return intval($s).'秒钟前';
    }
}
//采纳比
function caina($user_id, $a){
    $count = M('reply')->where(array('user_id'=>$user_id,'bast'=>1))->count();
    return ceil($count / $a) * 100;
}
//财富值
function cf($user_id){
    $cf = M('member_info')->field('prestige')->where(array('user_id'=>$user_id))->find();
    return $cf['prestige'] ? $cf['prestige'] : 0;
}
