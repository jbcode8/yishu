<?php
/**
 * 前台基础类
 * @author          Ethan Lu <838777565@qq.com>
 * @createdate		2013-6-2
 */
namespace Identify\Controller;
class BaseController extends AppframeController {
    protected $TemplatePath,$Theme;
    public function _initialize(){
        parent::_initialize();
        $this->TemplatePath = __APP__.'/Template/';
        
        $route = C('URL_ROUTE_RULES');
        $t_route = array();
        switch (GROUP_NAME) {
            case 'Identify':
                $t_route = array('//'=>'front_data/index');
                break;
            case 'Auction':
                $t_route = array('//'=>'front_init/index');
                break;
            case 'Gallery':
                $t_route = array('//'=>'front_gallery/index');
                break;
            case 'Artist':
                $t_route = array('//'=>'front_artist/index');
                break;
            default:
                break;
        }
        C('URL_ROUTE_RULES',array_merge($route,$t_route));

        $this->Theme = 'Default';
        //设置前台提示信息模板
        if (file_exists_case($this->TemplatePath . $this->Theme . "/" . "error" . C("TMPL_TEMPLATE_SUFFIX"))) {
            C("TMPL_ACTION_ERROR", $this->TemplatePath . $this->Theme . "/" . "error" . C("TMPL_TEMPLATE_SUFFIX"));
        }
        if (file_exists_case($this->TemplatePath . $this->Theme . "/" . "success" . C("TMPL_TEMPLATE_SUFFIX"))) {
            C("TMPL_ACTION_SUCCESS", $this->TemplatePath . $this->Theme . "/" . "success" . C("TMPL_TEMPLATE_SUFFIX"));
        }
        $this->top_menu();
        $this->footer_menu();
        $this->friend_link();
    }
    
    /**
     * 前台模板显示
     * @param type $templateFile
     * @param type $charset
     * @param type $contentType
     * @param type $content
     * @param type $prefix
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        parent::display(parseTemplateFile($templateFile), $charset, $contentType, $content, $prefix);
    }

    public function top_menu(){
        $model = M("phpcms.category","v9_");
        //输出主菜单为0的数组
        $selfmenu = $model->field('catid')->where(array('ismenu'=>1,'siteid'=>1,'parentid'=>0,'module'=>'content'))->order('listorder ASC')->select();
        $parentid = array();
        foreach($selfmenu as $v){
            $parentid[] = $v['catid'];
        }
        //输出需要显示的菜单
        $map['parentid'] = array('IN',implode(',',$parentid));
        $map['ismenu'] = 1;
        $map['module'] = 'content';
        $this->top_menu = $model->field('catname,url')->where($map)->order('listorder ASC')->select();
    }
	/**
	 * 合作媒体机构
	 */
    public function friend_link(){
    	$model = M('phpcms.link','v9_');
    	$friend = $model->field('linkid, linktype, name, url, logo')->order('linkid DESC')->select();
    	$friend_link = array();
    	foreach($friend as $k => $v)
    	{
    		if($v['linktype'] == 1)
    		{
    			$friend_link['thumb'][] = $v;
    		}else{
    			$friend_link['name'][] = $v;
    		}
    	}
    	$this->friend_link = $friend_link;
    }
    
    public function footer_menu(){
        $model = M("phpcms.category","v9_");
        $this->footer_menu =$model->field('catname,url')->where(array('parentid'=>1,'ismenu'=>1,'siteid'=>1))->order('listorder ASC')->select();
    }
}