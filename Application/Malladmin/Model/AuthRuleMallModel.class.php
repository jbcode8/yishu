<?php
// +----------------------------------------------------------------------
// | AuthRuleModel.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Model;
use Think\Model;

class AuthRuleMallModel extends Model {

    const RULE_URL = 1;
    const RULE_MAIN = 2;

    protected $_validate = array(
        array('pid', 'require', '上级菜单必须选择!', self::MUST_VALIDATE),
        array('module', 'require', 'module不能为空!', self::MUST_VALIDATE),
        array('type', 'require', '类型必须选择!', self::MUST_VALIDATE),
        array('name', 'require', '标识不能为空!', self::MUST_VALIDATE),
        array('title', 'require', '菜单名称不能为空!', self::MUST_VALIDATE),
        array('condition', 'iscondition', '权限条件规则不正确!', self::VALUE_VALIDATE,'callback'),
        array('listorder', 'number', '排序只能为正数!', self::VALUE_VALIDATE),
    );

    /**
     * 验证权限条件是否符合要求
     * @param $data 自动验证传递的值
     * @return bool
     */
    protected function iscondition($data){
        if(strpos($data,'{') === false || strpos($data,'{') != 0){
            return false;
        }
        return true;
    }

    public function lists($filed=true){
        return $this->field($filed)->where('status=1')->order('listorder asc')->select();
    }

    /**
     * 删除后删除关联菜单
     * @param $data
     * @param $options
     */
    public function _after_delete($data,$options){
        $this->where("pid={$data['id']}")->delete();
    }
}