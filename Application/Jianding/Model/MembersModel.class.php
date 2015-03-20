<?php

	/**
	*  yishu 用户成员表
	*  MembersModel.class.php
	*/
namespace Jianding\Model;
use Think\Model;

	class MembersModel extends Model{
		protected $trueTableName='sso_members';
		protected $dbName = 'sso';


		/**
		*  通过mid获取用户的注册信息
		*  @param  $mid  注册用户的ID
		*  @return 返回用户的信息的信息 array
		*/
		public function getMembers($mid){
			return $this->where(array('uid'=>$mid))->find();
		}
	}