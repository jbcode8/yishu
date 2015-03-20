<?php
namespace Home\Model;
use Think\Model;
class TestModel extends Model{
	protected $tablePrefix = 'top_';
	public function __construct(){
		echo "test";
	}
}

?>