<?php
	namespace Jishou\Model;
	use Think\Model;
	class JishouGoodsTypeModel extends Model{
		protected $_validate=array(
			array('gtype_enabled','0,1','是否启用选项错误',0,'in',3),
			array('gtype_name','require','名称不能为空'),
			array('gtype_name','','名字重复',2,'unique',1),
		);
	
	}
?>