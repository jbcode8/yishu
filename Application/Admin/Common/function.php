<?php
// +----------------------------------------------------------------------
// | function.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------


/**
 * 后台菜单
 * @param $id
 * @return string
 */
function getMenu($id){
    $data = D('AuthRule')->lists('id,pid,name,title');
    foreach($data as $k=>$v){
        $dataList[$k] = $v;
        $dataList[$k]['selected'] = ($id == $v['id'])?'selected':'';
    }
    //树型结构处理
    $menu = new \Org\Util\Tree();
    $menu->icon = array('┃','┣','┗');
    $menu->nbsp = "&nbsp;";
    $str = "<option value='\$id' \$selected>\$spacer\$title</option>";
    $menu->init($dataList);
    return $menu->get_tree(0, $str);
}

/**
 * 获取会员菜单select
 * @param $id
 * @return string
 */
function getMemberMenu($id){
    $data = D('MemberMenu')->lists('id,pid,name');
    foreach($data as $k=>$v){
        $dataList[$k] = $v;
        $dataList[$k]['selected'] = ($id == $v['id'])?'selected':'';
    }
    //树型结构处理
    $menu = new \Org\Util\Tree();
    $menu->icon = array('┃','┣','┗');
    $menu->nbsp = "&nbsp;";
    $str = "<option value='\$id' \$selected>\$spacer\$name</option>";
    $menu->init($dataList);
    return $menu->get_tree(0, $str);
}

function int_to_string(&$data,$map=array('status'=>array(1=>'正常',-1=>'删除',0=>'禁用',2=>'未审核',3=>'草稿'))) {
    $data = (array)$data;
    foreach ($data as $key => $row){
        foreach ($map as $col=>$pair){
            if(isset($row[$col]) && isset($pair[$row[$col]])){
                $data[$key][$col.'_text'] = $pair[$row[$col]];
            }
        }
    }
    return $data;
}

/**
 * 获取栏目类型
 */
function getCateType($type){
    switch($type){
        case 0:
            return '单页面';
            break;
        case 1:
            return '内部栏目';
            break;
        case 2:
            return '外部链接';
            break;
        default:
            return;
    }
}

/**
 * 获取模型名称
 * @param $model_id
 * @param string $field
 * @return mixed
 */
function getModelName($model_id=null,$field='title'){
    if($model_id == null) return;
    if(!$data = S('model')){
        $data = D('Model')->getList();
        S('model',$data);
    }
    if(strpos($model_id,',') === false){
        return $data[$model_id][$field];
    }else{
        $arr = explode(',',$model_id);
        $mo = array();
        foreach($arr as $v){
            $mo[]= $data[$v][$field];
        }
        return implode(',',$mo);
    }
}

/**
 * 获取模型信息
 * @param $modelId
 * @param string $field
 * @return mixed
 */
function get_document_model($modelId,$field = 'name'){
    if(!$model = S('models')){
        $model = D('Model')->getList();
        S('models',$model);
    }
    return $model[$modelId][$field];
}

/**
 * 获取分类字段
 * @param $catId
 * @param string $field
 */
function get_category($catId,$field = 'model'){
    $list = D('Category')->field($field)->find($catId);
    return $list[$field];
}

/**
 * 检查是否还有下级栏目
 * @param int $catid
 * @return bool
 */
function is_child($catid=0){
    if(!$list = S('category')){
        $list = D('Category')->getList();
        S('category',$list);
    }
    foreach($list as $v){
        if($v['pid'] == $catid){
            return true;
            break;
        }
    }
    return false;
}

/**
 * 获取推荐位列表
 * @param int $modelid
 * @param int $catid
 * @param int $id
 * @internal param $posid
 */
function getPosition($modelid = 0,$catid = 0,$id = 0){
    /* 获取推荐位栏目 */
    $map['modelid'] = $modelid;
    $map['catid'] = array('IN',$catid.',0');
    $position = M('Position')->field('posid,name')->where($map)->order('listorder asc,posid asc')->select();

    /* 获取内容选中的栏目 */
    $data = M('PositionData')->where(array('id'=>$id,'model_id'=>$modelid))->getField('posid',true);
    $positionArr = array();
    foreach($position as $k=>$v){
        $positionArr[$k] = $v;
        foreach($data as $value){
            if($value == $v['posid']){
                $positionArr[$k]['selected'] = true;
            }
        }
    }

    foreach($positionArr as $v){
        if(isset($v['selected'])){
            $checked = 'checked';
        }else{
            $checked = '';
        }
        echo '<label><input type="checkbox" name="posid[]" value="'.$v['posid'].'"'.$checked.'> '.$v['name'].'</label>';
    }
}

//优惠码生成函数
/**
* @param int $codes_num//定义一个int类型的参数 用来确定生成多少个优惠码
* @param array $exclude_codes_array//定义一个exclude_codes_array类型的数组
* @param int $code_length //定义一个code_length的参数来确定优惠码的长度
* @param string type //定义一个优惠码类型比如A类码
* @return array//返回数组
*/
function generate_promotion_code($codes_num,$exclude_codes_array='',$code_length = 4,$type=""){
    $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $promotion_codes = array();//这个数组用来接收生成的优惠码
    for($j = 0 ; $j < $codes_num; $j++){
        $code = "";
        for ($i = 0; $i < $code_length; $i++){
            $code .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        //如果生成的4位随机数不再我们定义的$promotion_codes函数里面
        if(!in_array($code,$promotion_codes)){
            if(is_array($exclude_codes_array)){
                if(!in_array($code,$exclude_codes_array)){//排除已经使用的优惠码
                    $promotion_codes[$j] = $code;//将生成的新优惠码赋值给promotion_codes数组
                }else{
                    $j--;
                }
            }else{
                $promotion_codes[$j] = $type.$code;//将优惠码赋值给数组
            }
        }else{
            $j--;
        }
    }
    return $promotion_codes;
}