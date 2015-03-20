<?php
namespace Special\Controller;
use Home\Controller\HomeController;
/**
 * 搜索模型控制器
 */
class SpecialController extends HomeController
{
    public function _initialize() {


    }
    /*指向专题*/
    public function index()
    {
        $temp = new \Special\Model\TempModel();
        $name = I('get.special','','trim');
        //缓存处理
        if(!S('Special_'.$name."")) {
            $template = $temp->field('template')->where(array('name'=>$name, 'status'=>1))->find();
            if($template) {
                S('Special_'.$name."",$template['template']);
            } else {
                $this->error('专题不存在或者未开启');
            }
            $content = htmlspecialchars_decode(S('Special_'.$name.""));
            $url = "Application\\Special\\View\\Special\\$name.html";
            $fp=fopen($url,"w+");
            if(is_writable($url)) {
                fputs($fp,$content);
                fclose($fp);
            } else {
                $this->error('文件不可写，请检查');
            }
        }
        $this->display($name);
    }
}
?>