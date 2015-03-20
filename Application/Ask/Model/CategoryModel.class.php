<?php 
//+------------------------------------------------------------
//|	问答模块_分类表_模型(CURD)
//+------------------------------------------------------------
//|	Author:	songfeilong <414545427@qq.com>
//+------------------------------------------------------------

namespace Ask\Model;
/**
 *	分类模型
 *	@author tangyong <695841701@qq.com>
 */
class CategoryModel extends AskModel{

    /**
	 *	自动验证
	 */

	protected $_validate = array(
			//array('title','require','问题名称不能为空!',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
			array('name','require','分类名称不能为空!',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),

		);	
	/**
	 *	自动完成
	 */
	protected $_auto = array(	
			array('create_time', NOW_TIME, self::MODEL_INSERT),
			array('update_time', NOW_TIME, self::MODEL_UPDATE ),
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
        $arr = $this->field(array('cate_id', 'name', 'parent_id', 'listorder', 'if_show', 'create_time', 'update_time'))->where(array('if_show' => '1'))->order('listorder DESC')->select();
        S('AskCategory', $arr);
        if($isReturn){ return $arr; }
    }

    // 获取缓存数据且返回
    function getCategoryCache(){

        // 获取缓存数据
        $arrCache = S('AskCategory');

        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }

    /**
     * @param int $parentId 父级类别
     * @param int $level 子级深度
     * @param array $arr 被检索的数组
     * @param int $i 层级数
     * @return string 返回类别ID的字符串(例如：3,4,5,7,9,11 适合SQL的IN语句)
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
}	
?>