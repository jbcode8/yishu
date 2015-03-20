<?php 
namespace Jianding\Model;
use Think\Model;

class JiandingExpertModel extends Model
{
    public $tableName = 'jianding_experts';
	protected $_validate = array(
		array('cat_id', 'number', '分类id必须是一个数字'),
	    array('expert_name', 'require', '必须填写专家名'),
	    array('eval_fee', 'require', '必须填写专家鉴定费用'),
	    array('is_use', '0,1', '0或1', 0, 'in', 3),
	    array('portrait_img_name', 'require', '必须上传专家头像'),
	    array('portrait_img_path', 'require', '必须上传专家头像'),
	);
	
	protected $_auto = array(
		array('expert_desc', 'htmlspecialchars', 3, 'function'), 
	);
	
	/**
	 * 获取所有专家信息
	 */
	public function expertsData()
	{
		$field = 'expert_id,cat_id,portrait_img_name,
		    portrait_img_path,expert_name,expert_desc,
		    eval_fee,eval_num,favorable,devalue,is_use';
		$data = $this->field($field)
		     ->where(array('is_delete'=>0))
		     ->select();
		if (empty($data))
		{
			return false;
		}
		return $data;
	}
}