<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取文档模型信息
 * @param  integer $id    模型ID
 * @param  string  $field 模型字段
 * @return array
 */
function get_document_model($id = null, $field = null){
    static $list;

    /* 非法分类ID */
    if(!(is_numeric($id) || is_null($id))){
        return '';
    }

    /* 读取缓存数据 */
    if(empty($list)){
        $list = S('DOCUMENT_MODEL_LIST');
    }

    /* 获取模型名称 */
    if(empty($list)){
        $map   = array('status' => 1, 'extend' => 1);
        $model = M('Model')->where($map)->field(true)->select();
        foreach ($model as $value) {
            $list[$value['id']] = $value;
        }
        S('DOCUMENT_MODEL_LIST', $list); //更新缓存
    }

    /* 根据条件返回数据 */
    if(is_null($id)){
        return $list;
    } elseif(is_null($field)){
        return $list[$id];
    } else {
        return $list[$id][$field];
    }
}

/**
 * 获取栏目标识
 * @param  integer $id   文章id
 * @param  integer $model   模型id
 * @return array
 */
function get_category_name($id, $model){
    $catid = D('Document')->where(array('id'=>$id, 'model'=>$model))->getField('catid');
    return D('Category')->where(array('catid'=>$catid))->getField('name');
}

/**
 * 根据aid获取作家名
 * @param integer $aid 作家aid
 * @return string      作家名
 */
function getName($aid)
{
    return  M('GalleryArtist')->where(array('id'=>$aid))->getField('name');
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
 * 根据region_id获取地区名称
 * @param integer $region_id 地区id
 * @return string      地区名
 */
function getCity($region_id){
	$cityName = M('Region')->field('name')->where(array('id'=>$region_id))->find();
	return $cityName['name'];

}