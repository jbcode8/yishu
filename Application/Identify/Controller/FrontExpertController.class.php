<?php

// +----------------------------------------------------------------------
// | 鉴定模块_前端_专家名录_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
class FrontExpertController extends BaseController {
    public function _initialize() {
        parent::_initialize();
        $auth = getLoginStatus();
        $uid = $auth['uid'];
        $this->auth = $auth;
    }
    public function index() {
        
        // 类别数组
        if(!S('IdentifyCategory')){
            D('IdentifyCategory')->createIdentifyCategoryCache();
        }
        $this->arrCategory = S('IdentifyCategory');
       
        // 获取类别ID
        $cid = I('get.cid', '0', 'int');
		
        empty($cid) OR $where['category'] = $cid;
        empty($cid) OR $this->cid = $cid;
        
        // 分页需要定义为$this->Model
        $this->Model = D('IdentifyExpert');
        
        // 分页信息
        C('PAGE_NUMS', 15); // 重置分页条数
        $this->page = $this->pages($where);
       
        // 分页列表
        $field = array('id','username', 'thumb', 'brief');
        $this->list = $this->Model->field($field)->where($where)->limit($this->p->firstRow.', '.$this->p->listRows)->order('`id` DESC')->select();
        
        // 鉴定专家团
        $this->arrExpert = D('IdentifyExpert')->field('username, thumb, brief')->order('id DESC')->limit(3)->select();
        
        // 最新上传藏品
        $this->arrHot = D('IdentifyData')->field('id, name, thumb, size, createtime')->where('`isopen` = 1')->order('createtime DESC')->limit(5)->select();
        
        // 推荐藏品
        $this->arrPush = D('IdentifyData')->field('id, name, thumb, size, createtime')->where('`isopen` = 1 AND `isok` = 1 AND `ispush` = 1')->order('id DESC')->limit(5)->select();

        //合作机构
        $res= M(C('PHPCMS_DB').'.link','v9_')->field(array('url','name','logo'))->where("`passed` = 1 ")->limit(20)->select();
        $link01 = array();
        $link02 = array();
        foreach($res as $link){
            if($link['logo'] == ''){
                $link01[]=$link;
            }
            else{
                $link02[]=$link;
            }
        }
        $this->link01 = $link01;
        $this->link02 = $link02;

        $this->display('Front:expert');
    }
    
}
