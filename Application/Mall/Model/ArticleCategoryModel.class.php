<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 文章类别模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.28
// +----------------------------------------------------------------------

namespace Mall\Model;

class ArticleCategoryModel extends MallBaseModel{
    
    // 自动验证
    protected $_validate = array(
        array('parent_id', 'require', '请选择上级类别！'),
        array('cate_name', 'require', '请填写类别名称！'),
        array('cate_name', '', '此类别名称已存在！', 0, 'unique'),
        array('listorder', 'number', '排列顺序必须大于零(0)！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
    );
    
    // 添加之后更新缓存
    public function _after_insert($data, $options) {
        $this->caches();
    }
    
    // 修改之后更新缓存
    public function _after_update($data, $options) {
        $this->caches();
    }
    
    // 删除之后更新缓存
    public function _after_delete($data, $options){
        $this->caches();
    }

    // 创建缓存数据
    function caches($isReturn = false){
        $arr = $this->field(array('cate_id', 'cate_name', 'parent_id', 'listorder'))->order('listorder ASC')->select();
        S('ArticleCategory', $arr);
        if($isReturn){ return $arr; }
    }

    /**
     * @param $cateId 子类ID
     * @param array $arr 被检索的数组
     * @param int $k 预检索的父类深度
     * @return array|string 从深到浅[子级到父级]的返回关联类别数组
     */
    function getParentCategory($cateId, $arr = array(), $k = 10){
        $str = $this->_resetParentCategory($cateId, $k, $arr);
        if(empty($str)){return '';}
        $arr = explode(',', $str);
        foreach($arr as $rs){
            $array[] = explode('-', $rs);
        }
        unset($arr);
        return $array;
    }
    
    // 递归的返回子类别的父级类别，只服务于getParentCategory函数，不可直接使用
    private function _resetParentCategory($cateId, $k, $arr, $i = 1){
        $list = '';
        empty($arr) AND $arr = $this->getCategoryCache();
        if(!is_array($arr)){ return ''; }
        foreach($arr as $rs){
            if($rs['cate_id'] == $cateId){
                $list.= ','.$rs['cate_id'].'-'.$rs['cate_name'];
                if($i > $k){break;} $n = $i; $n++;
                $parentId = $this->_resetParentCategory($rs['parent_id'], $k, $arr, $n);
                if(!empty($parentId)){ $list.= ','.$parentId; }
            }
        }
        return trim($list, ',');
    }

    /**
     * 重置数组为多维数组，可定义深度
     * @param int $parentId 父级类别
     * @param array $arr 子级深度
     * @param int $level 被检索的数组
     * @param int $i 层级数
     * @return array 返回多维数组
     */
    function resetCategore($parentId = 0, $arr = array(), $level = 10, $i = 1){
        $list = array();
        empty($arr) AND $arr = $this->getCategoryCache();
        if(!is_array($arr)){ return $list; }
        foreach($arr as $rs){
            if($rs['parent_id'] == $parentId){
                if($i <= $level){ $list[$rs['cate_id']] = $rs; }else{ break; } $n = $i; $n++;
                $subCategory = $this->resetCategore($rs['cate_id'], $arr, $level, $n);
                if(!empty($subCategory)){ $list[$rs['cate_id']]['subCategory'] = $subCategory; }
            }
        }
        return $list;
    }

    /**
     * 获取某类别ID下的子类别ID
     * @param int $parentId
     * @param int $level
     * @param array $arr
     * @param int $i
     * @return string
     */
    function getSubCategoryId($parentId = 0, $level = 10, $arr = array(), $i = 1){

        $str = '';
        empty($arr) AND $arr = $this->getCategoryCache();
        if(!is_array($arr)){return $str;}
        foreach($arr as $rs){
            if($rs['parent_id'] == $parentId){
                if($i <= $level){ $str.= ','.$rs['cate_id']; }else{ break; } $n = $i; $n++;
                $subCategoryId = $this->getSubCategoryId($rs['cate_id'], $level, $arr, $n);
                if(!empty($subCategoryId)){ $str.= ','.$subCategoryId; }
            }
        }
        return trim($str, ',');
    }

    // 获取缓存数据且返回
    function getCategoryCache(){
        
        // 获取缓存数据
        $arrCache = S('ArticleCategory');
        
        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }
}
