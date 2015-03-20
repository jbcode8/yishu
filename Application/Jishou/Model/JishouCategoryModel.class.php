<?php
	namespace Jishou\Model;
	use Think\Model;

	class JishouCategoryModel extends Model{
		protected $_validate = array(
			array('parent_id','number','父目录必须是一个数字'),
			array('cat_name','require','必须填写分类名称'),
			array('cat_name','','目录名重复',0,'unique',2),
			array('is_enabled','0,1','0或1',0,'in',3),
			array('is_filter',array(0,1),'0或1',0,'in',3),
			array('keywords','require','请填写分类页关键字'),
			array('cat_desc','require','请填写分类页描述'),
			array('cat_spell','require','必须填写分类名称拼音'),
		);

		protected $_auto = array(
			array('keywords','htmlspecialchars',3,'function'),	
			array('cat_desc','htmlspecialchars',3,'function'),
			array('cat_spell','htmlspecialchars',3,'function'),
		);

		/*
		*  @method 返回目录编辑需要的数据
		*  @param  $cat_id  目录ID
		*  @return  array  目录的数据完好的
		*/
		public function categoryData($cat_id){
			$field = 'cat_id,cat_name,parent_id,
			cat_name,keywords,cat_desc,
			attr_id,is_filter,
			cat_spell,is_enabled,sort_order';
			//$cat = M('JishouCategory');
			$catData = $this->field($field)
					->where(array('cat_id'=>$cat_id,'is_delete'=>0))
					->limit(1)->select();
			$catData = $catData[0];
			$attrs_id = $catData['attr_id'];
			unset($catData['attr_id']);
			$attrData = M('JishouAttribute')
				->field('gtype_id,attr_name,attr_id')
				->where(array('attr_id'=>array('in',$attrs_id)))
				->select();

			$catData['attr_data'] =$attrData;
			if(empty($catData)){
				return false;
			}else{
				return $catData;
			}

			
		}

	}

?>