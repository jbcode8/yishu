<?php
/**
 * Widget.class.php
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 yishu. All rights reserved.
 */

namespace Common\Controller;
use Think\Think;

abstract class Widget{

    /**
     * 视图实例对象
     * @var view
     * @access protected
     */
    protected $view     =  null;

    /**
     * 架构函数 取得模板对象实例
     * @access public
     */
    final public function __construct() {
        //实例化视图类
        $this->view     = Think::instance('Think\View');
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

    /**
     * 获取模板路径
     * @param $templateFile  路径名称  模块/控制器/方法
     * @return string
     */
    final private function get_path($templateFile){
        $array     = explode('/',$templateFile);
        $method    = array_pop($array);
        $classname = array_pop($array);
        $module    = $array? array_pop($array) : 'Common';
        $templateFile  = $module.'/Widget/'.$classname.'/'.$method;
        return APP_PATH.$templateFile.C('TMPL_TEMPLATE_SUFFIX');
    }

    /**
     * @param string $templateFile
     * @return mixed
     * @throws \Exception
     */
    final protected function fetch($templateFile = CONTROLLER_NAME){
        if(!is_file($templateFile)){
            $templateFile = $this->get_path($templateFile);
            if(!is_file($templateFile)){
                throw new \Exception("模板不存在:$templateFile");
            }
        }
        return $this->view->fetch($templateFile);
    }

    /**
     * 模板显示 调用内置的模板引擎显示方法，
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类型
     * @param string $content 输出内容
     * @param string $prefix 模板缓存前缀
     * @throws \Exception
     * @return void
     */
    protected function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        if(!is_file($templateFile)){
            $templateFile = $this->get_path($templateFile);
            if(!is_file($templateFile)){
                throw new \Exception("模板不存在:$templateFile");
            }
        }
        $this->view->display($templateFile,$charset,$contentType,$content,$prefix);
    }
    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Action
     */
    final protected function assign($name,$value='') {
        $this->view->assign($name,$value);
        return $this;
    }
} 