<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 公用方法
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.23
// +----------------------------------------------------------------------

//通过PID返回把有父级下的所有数据
function getChildsId ($cate, $parent_id) {
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $parent_id) {
            $arr[] = $v['cate_id'];
            $arr = array_merge($arr, getChildsId($cate, $v['cate_id']));
        }
    }
    return $arr;
}

//记录分词数据
function get_tags_arr($word){
    import('Org.Net.Pscws');
    $pscws = new \Pscws();
    $pscws->set_dict(LIB_PATH.'Org/Net/scws/dict.utf8.xdb');
    $pscws->set_rule(LIB_PATH.'Org/Net/scws/rules.utf8.ini');
    $pscws->set_ignore(true);
    $pscws->send_text($word);
    $words = $pscws->get_tops(5);
    $tags = array();
    foreach ($words as $val) {
        $tags[] = $val['word'];
    }
    $pscws->close();
    return $tags;
}

/**
 * 根据用户ID返回用户信息，此函数基于总的function.php下的memberInfo()函数
 * @param $uid 用户ID
 * @param string $tag 需要信息的键
 * @return mixed 返回值键对应的值
 */
function getUserInfo($uid, $tag = 'username'){
    $arr = memberInfo($uid);
    return $arr[$tag];
}

/**
 * 返回Checkbox控件组
 * @param $cate_id
 * @param $attribute
 * @return string
 */
function attrCheckBox($cate_id, $attribute = ''){

    $checkBox = '';

    // 获取此类别的父级类别对应的属性
    $arrParentAttr = D('Category')->parentAttr($cate_id);
    empty($arrParentAttr) OR $parentAttr = implode(',', $arrParentAttr);
    empty($parentAttr) OR $arrParentAttr = explode(',', $parentAttr);
    empty($parentAttr) OR $attribute .= ','.$parentAttr;

    // 列出总的属性类型
    $arrAttr = D('Attribute')->getAttributeCache();

    // 当前选中的
    $arrOnAttr = empty($attribute) ? array() : explode(',', $attribute);

    $i = 1;
    foreach($arrAttr as $v){

        // 设置当前已经选中属性的选中状态
        $checked = (in_array($v['attr_id'], $arrOnAttr)) ? ' checked="checked"' : '';

        // 设置父级的属性不可编辑和变色
        $disabled = $red = '';
        if(in_array($v['attr_id'], $arrParentAttr)){
            $disabled = ' disabled="disabled"';
            $red = ' style="color:#f00"';
        }

        // 组合多选的checkboxHTML
        $checkBox.= '<input type="checkbox" name="attr" value="'.$v['attr_id'].'"'.$checked.$disabled.' /> <span'.$red.'>'.$v['attr_name'].'</span>&nbsp;&nbsp;'.PHP_EOL;

        // 设置每五条换行
        ($i % 5 == 0) AND $checkBox.= '<br />';
        $i++;
    }

    return $checkBox;
}

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
    $str = "<option value='\$cate_id' \$selected>\$spacer\$cate_name</option>".PHP_EOL;

    $menu->init($dataList);
    return $menu->get_tree(0, $str);
}

/**
 * 把类别转化为JSON格式用于JS循环
 * @param string $model 被检索的数据模型(默认是总的分类模型)
 * @return string 返回JSON数据
 */
function jsonCategory($model = 'Category'){
    $arrCate = D($model)->getCategoryCache();
    return json_encode($arrCate);
}

/**
 * 用于后台的品牌名对应的类别输出(多层次/多深度)
 * @param $cate_id
 * @return string 返回例如 A品牌 > A1品牌 > A1-1品牌
 */
function brandCateCrumb($cate_id){
    $arr = D('Category')->getParentCategory($cate_id);
    if($arr){
        $list = array();
        foreach($arr as $rs){
            $list[] = $rs[1];
        }
        krsort($list);
        return implode(' > ', $list);
    }
}

/**
 * 返回品牌是否被推荐
 * @param $recommend
 * @return string
 */
function brandIsRecommend($recommend){
    return ($recommend) ? '推荐' : '正常';
}

/**
 * 判断链接类型
 * @param $type
 * @return string
 */
function getFriendType($type){
    return ($type == '0') ? '文字链接' : '图片链接';
}

/**
 * 变量的应用 实现 树状结构
 * @param $ary 无结构的一维数组
 * @param string $id 元素ID
 * @param string $pid 元素的父级ID
 * @param string $sub 子级的标示
 * @return mixed 返回树状结构
 */
function aryTree($ary, $id = 'id', $pid = 'pid', $sub = '_sub'){

    if(is_array($ary)){

        $refer = array();
        /** 创建新的数组，实现 [新数组的键] 和 [元数组的键] 指向同一个内容 */
        foreach($ary as $k => $v){
            $refer[$v[$id]] =& $ary[$k];
        }

        foreach($ary as $k => $v){ /** 此处的$ary自循环开始就已经分配内容，即不变 */

            $isPid = $v[$pid]; /** 获取当前循环体的父级ID */
            if(isset($refer[$isPid])){ /** 如果当前循环体存在父级元素 */
                $parent =& $refer[$isPid]; /** 则将对应的父级元素 和 变量[$parent] 指向同一个内容 */
                $parent[$sub][$v[$id]] =& $ary[$k]; /** 再将变量[$parent]增加子元素[当前循环体] 且将元数组指向与其同样的内容，即实现更新元数组 */
                unset($ary[$k]); /** 最后解除元数组的映射关系，且注销删除本身 */
            }
        }

        unset($refer);
    }

    return $ary;
}

/**
 * 返回状态
 * @param $type
 * @return string
 */
function getStatus($type){
    return ($type == '0') ? '禁用' : '启用';
}

/**
 * 获取类别名(根据类别ID)
 * @param $cid 类别ID
 * @param $model 模型名：用于获取对应的缓存
 * @return string 返回id对应的类别名
 */
function getCateName($cid, $model){
    $arr = D($model)->getCategoryCache();
    foreach($arr as $rs){
        if($rs['cate_id'] == $cid){
            return $rs['cate_name'];
        }
    }
    return '';
}

/**
 * 判断文章来源
 * @param $store_id
 * @return string
 */
function getStoreName($store_id){
    if($store_id == 0){
        return '系统管理员';
    }else{
        return mallStoreInfo($store_id, 'store_name');
    }
}

/**
 * 返回属性名
 * @param $attr_id
 * @return mixed
 */
function getAttrName($attr_id){
    $arr = D('Attribute')->getAttributeCache();
    return $arr[$attr_id]['attr_name'];
}

/**
 * 属性名的Select控件
 * @param int $attr_id
 * @return string
 */
function selectAttribute($attr_id = 0){
    $arr = D('Attribute')->getAttributeCache();
    $str = '<select name="attr_id">';
    foreach($arr as $rs){
        $select = $rs['attr_id'] == $attr_id ? ' selected="selected"' : '';
        $str.= '<option value="'.$rs['attr_id'].'"'.$select.'>'.$rs['attr_name'].'</option>';
    }
    $str.= '</select>';
    return $str;
}

/**
 * 类别的一级分类的Select控件
 * @param $cid
 * @return string
 */
function selectCate($cid){
    $arr = D('Category')->getCategoryCache();
    $option = '<select name="cate_id"><option value=""> -- 请选择</option>';
    foreach($arr as $rs){
        if($rs['parent_id'] == 0){
            $select = $rs['cate_id'] == $cid ? ' selected="selected"' : '';
            $option.= '<option value="'.$rs['cate_id'].'"'.$select.'>'.$rs['cate_name'].'</option>';
        }
    }
    $option.= '</select>';
    return $option;
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
 * 后台的搜索选项下拉控件(Select)
 * @param array $arrOption 数组格式：'address' => '详细地址'
 * @param string $option 当前选项
 * @return string 返回下拉控件(Select)
 */
function searchSelect($arrOption = array('address' => '详细地址'), $option = ''){
    $str = '<select name="stype">'.PHP_EOL;
    foreach($arrOption as $k => $v){
        $selected = $k == $option ? ' selected="selected"' : '';
        $str.= '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'.PHP_EOL;
    }
    $str.= '</select>'.PHP_EOL;
    return $str;
}

/**
 * 根据咨询ID获取咨询信息，可定义字段
 * @param $id 咨询ID
 * @param string $fields 可选类型(1.单一字符串；2.空数组；3.多元素的数组)
 * @return mixed 返回信息 由参数决定（1.单一字符串；2.全部字段的数组；3.多元素的数组；4.信息不存在返回0）
 */
function getQuestionInfo($id, $fields = 'content'){
    $field = (is_array($fields) && empty($fields)) ? '*' : $fields;
    $data = M('MallQuestion')->field($field)->where(array('question_id' => $id))->find();
    return empty($data) ? 0 : (is_array($fields) ? $data : $data[$fields]);
}

/**
 * 根据评论ID获取评论信息，可定义字段
 * @param $id 评论ID
 * @param string $fields 可选类型(1.单一字符串；2.空数组；3.多元素的数组)
 * @return mixed 返回信息 由参数决定（1.单一字符串；2.全部字段的数组；3.多元素的数组；4.信息不存在返回0）
 */
function getCommentInfo($id, $fields = 'content'){
    $field = (is_array($fields) && empty($fields)) ? '*' : $fields;
    $data = M('MallComment')->field($field)->where(array('comment_id' => $id))->find();
    return empty($data) ? 0 : (is_array($fields) ? $data : $data[$fields]);
}

/**
 * 根据产品ID获取产品信息，可定义字段
 * @param $id 产品ID
 * @param string $fields 可选类型(1.单一字符串；2.空数组；3.多元素的数组)
 * @return mixed 返回信息 由参数决定（1.单一字符串；2.全部字段的数组；3.多元素的数组；4.信息不存在返回0）
 */
function getGoodsInfo($id, $fields = 'goods_name'){
    $field = (is_array($fields) && empty($fields)) ? '*' : $fields;
    $data = M('MallGoods')->field($field)->where(array('goods_id' => $id))->find();
    return empty($data) ? 0 : (is_array($fields) ? $data : $data[$fields]);
}

/**
 * 根据产品ID返回对应的属性值ID和属性名ID
 * @param $goods_id 产品ID
 * @return mixed 返回数组
 */
function aryGoodsAttr($goods_id){
    return D('GoodsAttr')->field('attr_id, val_id')->where(array('goods_id' => $goods_id))->select();
}

/**
 * 获取产品图片
 * @param $goods_id
 * @return array
 */
function aryGoodsImage($goods_id){
    $ary = array();
    $arr = D('GoodsImage')->field('img_id, img_name, img_url')->where(array('goods_id' => $goods_id))->select();
    if(is_array($arr) && !empty($arr)){
        foreach($arr as $rs){
            $ary[$rs['img_id']] = $rs;
        }
    }
    return $ary;
}

/**
 * 获取产品的静态值(点击次数，收藏数等)
 * @param $goods_id
 * @return array
 */
function aryGoodsCount($goods_id){
    return D('GoodsCount')->where(array('goods_id' => $goods_id))->find();
}

/**
 * 根据产品的属性值ID返回属性值
 * @param $goodsAttr 属性数组(值ID和名ID)
 * @return array 返回 值ID => 值 的数组
 */
function aryAttrVal($goodsAttr){
    $ary = array();
    $val = '';
    foreach($goodsAttr as $rs){
        $val.= ','.$rs['val_id'];
    }
    empty($val) OR $val = trim($val, ',');
    $arr = D('AttrValue')->where('attr_val_id IN ('.$val.')')->select();

    foreach($arr as $row){
        $ary[$row['attr_val_id']] = $row['val_name'];
    }
    return $ary;
}

/**
 * 根据产品的属性值ID返回属性名
 * @param $goodsAttr 属性数组(值ID和名ID)
 * @return array 返回 名ID => 名 的数组
 */
function aryAttrName($goodsAttr){
    $ary = array();
    $val = '';
    foreach($goodsAttr as $rs){
        $val.= ','.$rs['attr_id'];
    }
    empty($val) OR $val = trim($val, ',');
    $arr = D('Attribute')->where('attr_id IN ('.$val.')')->select();

    foreach($arr as $row){
        $ary[$row['attr_id']] = $row['attr_name'];
    }
    return $ary;
}

/**
 * 根据品牌Id返回品牌信息
 * @param $brand_id 品牌Id
 * @param $field 查询值(单个字段 | 数组格式的多个字段)
 * @return mixed 返回值取决于第二个参数
 */
function getBrandInfo($brand_id, $field){
    $ary = D('Brand')->field($field)->where(array('brand_id' => $brand_id))->find();
    if(is_array($field)){
        return $ary;
    }else{
        return $ary[$field];
    }
}

/**
 * @param $cateId
 * @return string
 */
function getArticleCateName($cateId){
    $aryCate = S('ArticleCategory');
    if(is_array($aryCate) && !empty($aryCate)){
        foreach($aryCate as $rs){
            if($rs['cate_id'] == $cateId){
                return $rs['cate_name'];
            }
        }
    }else{
        return '';
    }
}

/**
 * Unix时间戳转为字符串时间格式
 * @param int $int
 * @param int $type 可定义时间格式
 * @return bool|int|string
 */
function int2time($int = 0, $type = 0){
    $arrType = array('Y-m-d H:i:s', 'Y-m-d', 'Y-m-d H:i');
    return empty($int) ? time() : date($arrType[$type], $int);
}

/**
 * 根据父项ID获取下一级的元素数组
 * @param int $pid
 */
function regionOne($pid = 2){
    return M('Region')->field(array('name','pid','id'))->where(array('pid' => $pid))->order('listorder ASC, id ASC')->select();
}

/**
 * 组装省份的select控件
 * @param int $rid
 * @return string
 */
function selectRegion($rid = 0){
    $arr = regionOne(2); // 中国下的一级行政区(省)
    $option = '<select name="region_id"><option value=""> -- 请选择</option>';
    foreach($arr as $rs){
        $select = $rs['id'] == $rid ? ' selected="selected"' : '';
        $option.= '<option value="'.$rs['id'].'"'.$select.'>'.$rs['name'].'</option>';
    }
    $option.= '</select>';
    return $option;
}

/**
 * 根据地区ID返回地区名
 * @param $rid
 * @return mixed
 */
function getRegionName($rid){
    $arr = regionOne(2); // 中国下的一级行政区(省)
    foreach($arr as $rs){
        if($rs['id'] == $rid) return $rs['name'];
    }
}

/**
 * 组装支付方式的select控件
 * @param int $pid
 * @param int $sid
 * @return string
 */
function selectPayment($pid = 0, $sid = 0){
    $where['status'] = 1;
    empty($sid) OR $where['store_id'] = $sid;
    $arr = M('MallPayment')->field(array('pay_name','pay_id'))->where($where)->order('listorder ASC, pay_id ASC')->select();
    $option = '<select name="pay_id" id="pay_id"><option value=""> -- 请选择</option>';
    foreach($arr as $rs){
        $select = $rs['pay_id'] == $pid ? ' selected="selected"' : '';
        $option.= '<option value="'.$rs['pay_id'].'"'.$select.'>'.$rs['pay_name'].'</option>';
    }
    $option.= '</select>';
    return $option;
}

/**
 * 根据ID获取支付方式名称
 * @param $pid
 * @param int $sid
 * @return mixed
 */
function getPayName($pid, $sid = 0){
    $where['status'] = 1;
    empty($sid) OR $where['store_id'] = $sid;
    $arr = M('MallPayment')->field(array('pay_name','pay_id'))->where($where)->order('listorder ASC, pay_id ASC')->select();
    foreach($arr as $rs){
        if($rs['pay_id'] == $pid) return $rs['pay_name'];
    }
}

/**
 * 根据属性名返回属性值
 * @param $attr_id
 * @return array
 */
function getAttrVal($attr_id){

    $attrVal = D('AttrValue')->getAttrValueCache();
    $arrAttrId = explode(',', $attr_id);
    $arrAttrVal = array();

    foreach($arrAttrId as $rs){
        foreach($attrVal as $k => $row){
            if($rs == $row['attr_id']){
                $arrAttrVal[$rs][] = $row;
                unset($attrVal[$k]); // 用完即销毁，减少数组长度，
            }
        }
    }
    return $arrAttrVal;
}

/**
 * 根据属性值列表和属性名列表输出HTML代码
 * @param $arrAttrVal
 * @param $arrAttrName
 * @param array $aryThenAttr
 * @return string
 */
function selectAttr($arrAttrVal, $arrAttrName, $aryThenAttr = array()){

    $select = '';
    foreach($arrAttrVal as $k => $rs){
        $select.= '<div class="left"><p class="name"><b class="red">*</b>'.$arrAttrName[$k].'<input type="hidden" name="attr[]" value="'.$k.'" /></p>';
        $select.= '<div class="cn"><select name="val[]">';
        foreach($rs as $vo){
            $sle = $aryThenAttr[$k] == $vo['attr_val_id'] ? ' selected="selected"' : '';
            $select.= '<option value="'.$vo['attr_val_id'].'"'.$sle.'>'.$vo['val_name'].'</option>';
        }
        $select.= '</select></div></div>';
    }

    return $select;
}

/**
 * 根据类别返回对应的品牌
 * @param $arr
 * @param $id
 * @return string
 */
function selectBrand($arr, $id){
    $str = '<select id="brand_id" name="brand_id">';
    foreach($arr as $rs){
        $select = $rs['brand_id'] == $id ? ' selected="selected"' : '';
        $str.= '<option value="'.$rs['brand_id'].'"'.$select.'>'.$rs['brand_name'].'</option>';
    }
    $str.= '</select>';
    return $str;
}

/**
 * 根据订单ID返回关联的产品信息
 * @param $oid
 * @return array
 */
function getOrderGoods($oid){
    $arr = array();
    empty($oid) OR $arr = M('MallOrderGoods')->where(array('order_id'=>$oid))->find();
    return $arr;
}

/**
 * 根据订单ID返回关联的其他信息
 * @param $oid
 * @return array
 */
function getOrderOther($oid){
    $arr = array();
    empty($oid) OR $arr = M('MallOrderOther')->where(array('order_id'=>$oid))->find();
    return $arr;
}

/**
 * 根据状态标识返回状态名
 * @param $status
 * @return mixed
 */
function orderStatus($status){
    $arr = array(
        '-1' => '取消订单',
        '0' => '待付款',
        '1' => '已付款',
        '2' => '已发货',
        '3' => '已收货',
    );
    return $arr[$status];
}

/**
 * 通过文章类别ID返回对应的类别名
 * @param $id
 * @param string $field
 * @return mixed
 */
function getArticleCate($id, $field = 'cate_name'){
    $arrCate = S('ArticleCategory');
    foreach($arrCate as $cate){
        if($cate['cate_id'] == $id) return $cate[$field];
    }
}

/**
 * 增加历史记录
 * @param $gid
 * @param $count
 */
function addHistory($gid, $count = 10){
    $history = session('history');
    if(empty($history)){
        $history = array($gid);
        session('history', $history);
    }else{
        if(!in_array($gid, $history)){
            array_unshift($history, $gid);
            count($history) > $count AND $history = array_slice($history, 0, $count);
            session('history', $history);
        }
    }
}

/**
 * 重置历史记录(清空)
 */
function resetHistory(){
    session('history', null);
}

/**
 * 返回历史记录
 * @return array
 */
function aryHistory(){
    $ary = session('history');
    $aryGoods = array();
    if(!empty($ary)){
        $gids = implode(',', $ary);
        $aryGoods = D('Goods')->field('goods_id,goods_name,goods_price,default_img')->where(array('goods_id' => array('in', $gids)))->select();
    }
    return $aryGoods;
}

/**
 * 获取购物保障的缓存
 * @return mixed
 */
function aryEnsure(){
    return D('Ensure')->getEntrueCache();
}

/**
 * 过滤字符串，防止攻击
 * @param $str
 * @return string
 */
function filterStr($str){

    // 后续可以壮大其过滤权限
    return trim($str);
}

/**
 * 根据店铺ID返回其自定义的类别
 * @param $id
 * @return array
 */
function getAryStoreCate($id){
    $ary = array();
    $aryCache = D('StoreCategory')->where(array('status'=>1,'store_id'=>$id))->order('listorder')->select();
    foreach($aryCache as $rs){
        $rs['store_id'] == $id AND $ary[$rs['id']] = $rs;
    }
    return $ary;
}

/**
 * 根据类别ID返回类别信息(名称|其他信息)
 * @param $id
 * @param string $field
 * @return mixed
 */
function getStoreCateInfo($id, $field = 'cate_name'){
    $ary = D('StoreCategory')->getCache();
    if(is_array($ary)){
        foreach($ary as $rs){
            if($id == $rs['id']){
                return $rs[$field];
            }
        }
    }
}

/**
 * 根据类别ID返回父级类别信息(名称|其他信息)
 * @param $store_id
 * @return array
 */
function getParentStoreCate($store_id){
    $ary = D('StoreCategory')->getCache();
    $storeCate = array();
    if(is_array($ary)){
        foreach($ary as $rs){
            if($store_id == $rs['store_id'] && $rs['pid'] == 0){
                $storeCate[$rs['id']] = $rs;
            }
        }
    }
    return $storeCate;
}

/**
 * 店铺类别的下拉控件
 * @param $store_id
 * @param int $id
 * @return string
 */
function selectStoreCate($store_id, $id = 0){
    $ary = D('StoreCategory')->getCache();
    $html = '<select name="pid" id="pid">'.PHP_EOL.'<option value="0">顶级类别</option>'.PHP_EOL;
    if(is_array($ary)){
        foreach($ary as $rs){
            if($rs['store_id'] == $store_id && $rs['pid'] == 0){
                $selected = $rs['id'] == $id ? ' selected="selected"' : '';
                $html.= '<option value="'.$rs['id'].'"'.$selected.'>'.$rs['cate_name'].'</option>'.PHP_EOL;
            }
        }
    }
    $html.= '</select>';
    return $html;
}

/**
 * 根据子类的ID返回其父类的ID
 * @param $subCateId
 * @return mixed
 */
function getParentStoreCateId($subCateId){
    $ary = D('StoreCategory')->getCache();
    foreach($ary as $rs){
        if($rs['id'] == $subCateId){
            return $rs['pid'];
        }
    }
}

/**
 * 更新统计文件的静态值，例如：产品或店铺的访问量，收藏量，注意使用的是M方法，因为没有对应的模型
 * @param $table
 * @param $field
 * @param $where
 */
function updateModelCount($table, $field, $where){
    M($table)->where($where)->setInc($field);
}

/**
 * 调试输出
 * @param $v
 * @param bool $exit
 */
function prt($v, $exit = true){
    echo '<pre>'.PHP_EOL;
    print_r($v);
    echo '</pre>';
    $exit AND die();
}

/**
 * @param $val
 * @param string $back
 */
function ajaxMsg($val, $back = 'backfunc'){
    die(trim($_GET[$back]).'('. json_encode($val) .')');
}

/**
 * 获取有效店铺的总数
 */
function intStoreCount(){
    return D('Store')->where(array('status'=>1))->count();
}

/**
 * 生成订单号
 */
function orderSN(){
    return date('YmdHis',time()).rand(100000,999999);
}

/**
 * 获取购物车的产品数量
 * @return int|number
 */
function cartGoodsCount(){
    $sessCart = session('sessCart');
    $int = 0;
    if(is_array($sessCart) && !empty($sessCart)){
        $int = array_sum(array_map(function($val){return count($val['g']);}, $sessCart));
    }
    return $int;
}

/**
 * 获取当前的URL
 * @return string
 */
function currentUri(){
    return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
}

/**
 * 根据店铺的ID返回店铺信息
 * @param $sid 店铺ID
 * @param string $field 预返回的字段
 * @return mixed
 */
function getStoreInfo($sid, $field = '*'){
    $field = trim($field);
    $ary = D('Store')->field($field)->where(array('store_id'=>$sid))->find();
    if($field === '*' || strpos(',', $field) !== false){
        return $ary;
    }else{
        return $ary[$field];
    }
}