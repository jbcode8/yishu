<?php
// +----------------------------------------------------------------------
// | 大师频道奖项控制器
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Controller;


class AwardController extends ArtistAdminController{

    /**
     * 奖项管理列表
     */
    public function index(){
        $map = array();
        $list = parent::lists('Award', $map);
        $this->assign('_list', $list);
        $this->display();
    }

    /**
     * 艺术家名字选择面板渲染方法，用于form表单的中选择艺术家
     * @param string $domId 表单的input标签的ID属性值
     * @param string $award   默认选中的奖项ID标识，用于编辑表单，添加表单可不填
     */
    public function awards($domId="_award_name", $award=''){
        $this->assign('awardDomId', $domId);
        $this->assign('award', $award);
        $res = D('Award')->field('id,name,letter')->select();
        $az=array('A','B','C','D','E','F','G','H','J','K','L','M','N','O','P','Q','R','S','T','W','X','Y','Z','qt');
        $this->assign('az', $az);
        $name = array();
        foreach($res as $ward){
            $letter = $ward['letter'];
            if(in_array($letter, $az)){
                $key = isset($name[$letter]) && is_array($name[$letter]) ? count($name[$letter]) : 0;
                $name[$letter][$key]['award'] = (int)$ward['id'];
                $name[$letter][$key]['name'] = $ward['name'];
            }
            else{
                $key = isset($name['qt']) && is_array($name['qt']) ? count($name['qt']) : 0;
                $name['qt'][$key]['award'] = (int)$ward['id'];
                $name['qt'][$key]['name'] = $ward['name'];
            }
        }
        ksort($name);
        $this->assign('nameAward', $name);
        $this->display("Award:awards");
    }

} 