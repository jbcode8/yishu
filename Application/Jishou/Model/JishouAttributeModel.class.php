<?php
	namespace Jishou\Model;
	use Think\Model;

	class JishouAttributeModel extends Model{
		protected $_validate = array(
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
		public function singleAttribute($gtype_id){
			
			
			
			$attrs = M('JishouAttribute')->field('*')
					->where(array('gtype_id'=>$gtype_id))->select();
			return $attrs;
		}
	
	}
	

?>