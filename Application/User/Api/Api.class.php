<?php
// +----------------------------------------------------------------------
// | Api.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace User\Api;

//载入函数库文件
require_cache(dirname(dirname(__FILE__)) . '/Common/common.php');
/**
 * API调用控制器层
 * 调用方法 A('Uc/User', 'Api')->login($username, $password, $type);
 */
abstract class Api {

    /**
     * API调用模型实例
     * @access  protected
     * @var object
     */
    protected $model;

    /**
     * 构造方法，检测相关配置
     */
    public function __construct(){
        $this->_init();
    }

    /**
     * 抽象方法，用于设置模型实例
     */
    abstract protected function _init();
} 