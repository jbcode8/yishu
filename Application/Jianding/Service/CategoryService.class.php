<?php
	/**
	*	zhihui
	*   鉴定项目目录分类 数据提取层
	*  	CategoryService.class.php
	*	date  2015-2-9
	*/
	namespace Jianding\Service;
	class CategoryService {
			//获取所有的鉴定项目的目录
			public function getAllCategory(){
				$field = 'cat_id,cat_name,keywords,cat_desc,is_filter,add_time,cat_spell';
				$category = M('JiandingCategory')
					->field($field)
					->where(array('is_enabled'=>1,'is_delete'=>0))
					//->order('sort_order desc,cat_id')
					->select();
				return $category;
			}

			/**
			*  根据catspell 获取category  的ID
			*  @param   $catspell    目录的cat_spell
			*  @return $cat_id  目录的ID
			*/
			public function getCatId($catspell){
				$cat_id = M('JiandingCategory')->where(array('cat_spell'=>$catspell))
							->getField('cat_id');
				return $cat_id;
			}


			/**
			* 获取目录下的所有属性
			* @param   $cat_id  目录的ID
			* @return  array   格式  array('属性名'=>array('属性值','属性值',...),'属性名'=>array('属性值','属性值',.....)) 
			*/
			public function getAttrs($cat_id){
				$attrs = M('JiandingAttribute')
							->where(array('cat_id'=>$cat_id,'is_delete'=>0,'attr_input_type'=>2))
							->select();
				$attrs_info = array();
				foreach($attrs as $attr){
					$attrs_info[$attr['attr_name']] = array('attr_value'=>explode('|',$attr['attr_value']),'attr_id'=>$attr['attr_id']);
				}

				return $attrs_info;

			}
	}