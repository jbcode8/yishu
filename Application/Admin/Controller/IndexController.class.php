<?php
// +----------------------------------------------------------------------
// | IndexController.class.php.
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Admin\Model\AuthRuleModel;
/**
 * 后台首页控制器
 * @package Admin\Controller
 */
class IndexController extends AdminController {

    public function index(){
	   $this->adminuid=$_SESSION['admin_auth']['uid'];
	   $admin_where=array(
            'uid'=>$this->adminuid,
            );
        $admin = M('Admin')->field("admintype")->where($admin_where)->select();
        $this->admin = $admin[0]['admintype'];

        //print($this->admin);
        $this->display();
    }

    /**
     * 获取菜单节点
     * @return mixed
     */
    public function getMenus(){
        $menus	=	session('ADMIN_MENU_LIST');
        if(!$menus){
            $AuthRule = D('AuthRule');
            $menus = $AuthRule->lists();
            foreach ($menus as $k=>$v) {
                $menus[$k]['url'] = U($v['name']);
                if($v['type'] == 2)unset($menus[$k]['url']);
            }
            //处理控制器中的节点
            foreach ($menus as $key=>$item){
                //判断节点权限
                if ( !$this->checkRule($item['name'],$item['type'],null) ) {  //检测节点权限
                    unset($menus[$key]);
                    continue;
                }
                unset($menus[$key]['name'],$menus[$key]['type'],$menus[$key]['module'],$menus[$key]['status'],$menus[$key]['condition'],$menus[$key]['listorder']);
            }
            session('ADMIN_MENU_LIST',$menus);
        }
        return $menus;
    }

	 /**
     * 获取加盟页面信息
     * @return mixed
     */
	public function get_jiameng_contact() {
		$this->list = M('jiameng_contact')->order('subtime desc')->select();
		$this->display();
	}
} 