<?php
// +----------------------------------------------------------------------
// | BaseLogic.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Logic;
use Think\Model;

abstract class BaseLogic extends Model{

    /**
     * 构造函数，用于这是Logic层的表前缀
     * @param string $name 模型名称
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     */
    public function __construct($name = '', $tablePrefix = '', $connection = '') {
        /* 设置默认的表前缀 */
        $this->tablePrefix = C('DB_PREFIX') . 'document_';
        /* 执行构造方法 */
        parent::__construct($name, $tablePrefix, $connection);
    }

    /**
     * 详细
     * @param $id
     * @return bool|mixed
     */
    public function detail($id){
        $data = $this->field(true)->find($id);
        if(!$data){
            $this->error = '获取详细信息出错！';
            return false;
        }
        return $data;
    }

    /**
     * 删除记录
     * @param $id
     * @return bool
     */
    public function delData($id){
        if(!$this->delete($id)){
            $this->error = '删除子记录错误!';
            return false;
        }
        return true;
    }

    /**
     * 新增或者更新数据
     */
    abstract public function update($id = 0);
} 