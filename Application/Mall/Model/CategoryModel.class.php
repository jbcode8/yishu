<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 类别模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.23
// +----------------------------------------------------------------------

namespace Mall\Model;
use Mall\Model\CategoryModel;

class CategoryModel extends MallBaseModel{
    
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
        array('update_time', 'time', 2, 'function'),
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
    public function IndexgetCat(){
        $str = '';
        $Model = new CategoryModel() ;
        $arr = $Model->query("select * from yishu_paimai_category");
        echo $Model->getLastSql();
        echo "<br/>";
        print_r($Model);
        exit;
        foreach($arr as $rs){
            if($rs['parent_id'] == $parentId){
                if($i <= $level){ 
                    $str.= ','.$rs['cate_id']; 
                }else{ 
                    break;
                } 
                $n = $i; 
                $n++;
                $subCategoryId = $this->getSubCategoryId($rs['cate_id'], $level, $arr, $n);
                if(!empty($subCategoryId)){ $str.= ','.$subCategoryId; }
            }
        }
        return trim($str, ',');
    }
    // 创建缓存数据
    function caches($isReturn = false){
        $arr = $this->field(array('cate_id', 'cate_name', 'parent_id', 'listorder', 'attribute','short_name'))->where(array('status' => '1'))->order('listorder ASC')->select();
        S('MallCategory', $arr);
        if($isReturn){ return $arr; }
    }
    
    /**
     * @param $cateId
     * @param array $arr
     * @param int $i
     * @return int
     */
    function getCategoryLevel($cateId, $arr = array(), $i = 1){
        foreach($arr as $rs) {
            if($rs['cate_id'] == $cateId){
                if($rs['parent_id'] == 0){return $i;} $i++;
                return $this->getCategoryLevel($rs['parent_id'], $arr, $i);
            }
        }
    }

    /**
     * 通过子类id返回其父类对应的属性名
     * @param $cateId 子类id
     * @return array 返回数组，元素格式：cate_id => attr
     */
    public function parentAttr($cateId){
        $strAttr = $this->_parentAttr($cateId);
        $strAttr = trim($strAttr, '|');
        $arrAttr = array();
        if(!empty($strAttr)){
            $arr = explode('|', $strAttr);
            foreach($arr as $rs){
                list($cate_id, $attr) = explode('-', $rs);
                $arrAttr[$cate_id] = $attr;
            }
        }
        return $arrAttr;
    }

    // 仅限parentAttr函数使用
    private function _parentAttr($cateId, $i = 1){
        $str = '';
        $arr = $this->getCategoryCache();
        foreach($arr as $rs){
            if($rs['cate_id'] == $cateId){
                if($i > 1){
                    if($rs['attribute']){
                        $str.= '|'.$rs['cate_id'].'-'.$rs['attribute'];
                    }
                }
                $n = $i; $n++;
                $str.= $this->_parentAttr($rs['parent_id'], $n);
            }
        }
        return $str;
    }

    /**
     * 获取子类别的父级类别，可定义父级深度
     * @param $cateId 父类ID
     * @param array $arr 被检索的数组
     * @param int $k 父类深度
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
                $list.= ','.$rs['cate_id'].'-'.$rs['cate_name'].'-'.$rs['short_name'];
                if($i > $k){break;} $n = $i; $n++;
                $parentId = $this->_resetParentCategory($rs['parent_id'], $k, $arr, $n);
                if(!empty($parentId)){ $list.= ','.$parentId; }
            }
        }
        return trim($list, ',');
    }

    /**
     * @param int $parentId 父级类别
     * @param array $arr 被检索的数组
     * @param int $level 子级深度
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
     * @param int $parentId 父级类别
     * @param int $level 子级深度
     * @param array $arr 被检索的数组
     * @param int $i 层级数
     * @return string 返回类别ID的字符串(例如：3,4,5,7,9,11 适合SQL的IN语句)
     */
    public function getSubCategoryId($parentId = 0, $level = 10, $arr = array(), $i = 1){
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
        //$arrCache = S('MallCategory');
        
        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }
}
