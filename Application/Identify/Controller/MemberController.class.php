<?php

// +----------------------------------------------------------------------
// | 用户基础类
// +----------------------------------------------------------------------
// | Author: Ethan <838777565@qq.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
class MemberController extends BaseController{
    public $uc,$mid;
    public function _initialize() {
        parent::_initialize();
        C('APP_SUB_DOMAIN_DEPLOY',false);
        $this->check_member();
        $this->uc = service('Passport');
        /*左侧菜单*/
        S('MemberMenu')?S('MemberMenu'):D('MemberMenu')->caches();
        $memberMenu = S('MemberMenu');
        $this->leftMenu = $this->getLeftMenu(0,$memberMenu);
        $this->mid = session('mid');
        $this->pm_count = uc_pm_list($this->mid);
    }
    /**
     * 检测用户是否已经登陆
     */
    final public function check_member() {
        if(!session('mid')){
            $this->error('请先登录!','http://i.yishu.com'.U('Member/Passport/login','',true,false,false));
        }
    }
    /**
     * 获取左侧菜单
     * @param type $pid
     * @param type $array
     * @return type
     */
    public function getLeftMenu($pid =0,$array){
        foreach ($array as $k => $v) {
            if($v['parentid'] == $pid && $v['display']==1){
                $menu[$k] = $v;
                $arrMenu = getChild($v['id'], $array);
                foreach ($arrMenu as $value) {
                    if($value['display']==1){
                        $menu[$k]['child'][] = $value;
                    }
                }
            }
        }
        return $menu;
    }
}