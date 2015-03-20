<?php

	namespace Jishou\Model;
	use Think\Model;
	class RegionModel extends Model{
		protected $dbName ='yishuv2';

		protected $_validate = array(
			array('address_uid','require','尚未登录'),
			array('address_provinceid','require','省份错误'),
			array('address_provincename','require','省份信息错误'),
			array('address_cityid','require','市区错误'),
			array('address_cityname','require','市区信息错误'),
			array('address_address','require','详细信息必填'),
			array('address_receiver','require','用户名缺少'),
			array('address_mobile','number','联系方式必须是数字'),
		
		);
	}