<?php
// +----------------------------------------------------------------------
// | ModelModel.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

class ModelModel extends Model {

    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_BOTH, 'function'),
        array('status', 1, self::MODEL_INSERT),
    );

    /**
     * 获取继承列表
     */
    public function getExtendList(){
        return $this->field('id,title')->where(array('extend'=>0))->select();
    }

    /**
     * 获取模型内容
     * @return mixed
     */
    public function getList(){
        $model = $this->getField('id,name,title,extend,template_list,template_add,template_edit,status,position_field',true);
        return $model;
    }

    protected function _before_write(&$data){
        S('models',null);
    }

} 