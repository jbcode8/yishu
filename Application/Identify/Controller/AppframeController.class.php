<?php
/**
 * 框架基础类
 * @author          Ethan Lu <838777565@qq.com>
 * @createdate		2013-6-2
 */
namespace Identify\Controller;
use Think\Page;
use Think\Controller;
class AppframeController extends Controller {
    protected $p,$Model;
    
    public function _initialize(){}
    
    public function index(){
        $this->display();
    }
    
    /**
     * 分页调用
     * @return \Page
     */
    public function pages($where=array()){
        $count = $this->Model->where($where)->count(); 
        $this->p = new Page($count,C('PAGE_NUMS')); 
        $this->p->setConfig('header', '条数据');
        $this->p->setConfig('first', '首页');
        $this->p->setConfig('last', '尾页'); 
        return $this->p->show();
    }
}