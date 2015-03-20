<?php
	namespace Jishou\Service;

	
	class CategoryService{
		protected $cat_field = array(
		'cat_id','cat_name','keywords','cat_desc','cat_spell'
	);

		
		public function getAllCategory(){
			$cat = M('JishouCategory')->field($this->cat_field)->select();
			return $cat;
		}
	}