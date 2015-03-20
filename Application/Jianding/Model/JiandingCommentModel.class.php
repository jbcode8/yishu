<?php 
namespace Jianding\Model;
use Think\Model;

class JiandingCommentModel extends Model
{
	protected $tableName = 'jianding_comment';
	
	protected $_validate = array(
		array('goods_id', 'require', '商品id必填'), 
	    array('content', 'require', '评论内容必填'),
	);
	
	protected $_auto = array(
		array('atime', 'time', 1, 'function'), 
	);
}