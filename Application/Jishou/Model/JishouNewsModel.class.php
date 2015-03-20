<?php
	namespace Jishou\Model;
	use Think\Model;
	class JishouNewsModel extends Model{
		protected $_validate = array(
			array('link_name','require','链接名必填'),
			array('link_url','require','链接地址必填'),
			);

		protected $_auto = array(
			array('add_time','time',1,'function'),
			);


	}

?>