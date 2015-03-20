<?php
	namespace Jianding\Model;
	use Think\Model;

	class JiandingAttributeModel extends Model{
		protected $_validate = array(
				array('cat_id','require','没有分类属性'),
				array('cat_id','number','分类属性值不对'),
				array('attr_name','require','属性值名必须'),
				array('attr_input_type','1,2','输入类型值错误',0,'in',3),
				array('sort_order','number','排序必须是数字'),
				array('attr_ext','0,6','单位值太长',0,'length',3),
			);

		protected $_auto=array(
				//array('attr_value','md5',3,'function'),	
			);
		
		/*
		* 获取gtype_id 的下的所有的属性值
		* @param $gtype_id 商品属性分类的ID
		* @return array()  属性分类下的所有的关联值
		*/
		public function singleAttribute($cat_id){
			
			
			
			$attrs = $this->field('*')
					->where(array('cat_id'=>$cat_id,'is_delete'=>0))->select();
			return $attrs;
		}
	
	}
	

?>