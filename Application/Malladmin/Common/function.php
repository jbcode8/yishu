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
    $data = D('AuthRuleMall')->lists('id,pid,name,title');
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
    $data = D('MemberMenuMall')->lists('id,pid,name');
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
        $data = D('ModelMall')->getList();
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
        $model = D('ModelMall')->getList();
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
    if(!$list = S('category')){
        $list = D('CategoryMall')->getList();
        S('category',$list);
    }
    return $list[$catId][$field];
}

/**
 * 检查是否还有下级栏目
 * @param int $catid
 * @return bool
 */
function is_child($catid=0){
    if(!$list = S('category')){
        $list = D('CategoryMall')->getList();
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
    $position = M('PositionMall')->field('posid,name')->where($map)->order('listorder asc,posid asc')->select();

    /* 获取内容选中的栏目 */
    $data = M('PositionDataMall')->where(array('id'=>$id,'model_id'=>$modelid))->getField('posid',true);
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