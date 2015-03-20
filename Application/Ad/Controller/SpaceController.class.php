<?php
// +----------------------------------------------------------------------
// | AdController.class.php
// +----------------------------------------------------------------------
// | Author: zhangzitao <1286701466@qq.com>
// +----------------------------------------------------------------------

namespace Ad\Controller;
use Admin\Controller\AdminController;

class SpaceController extends AdminController
{
    /**
     * 广告位列表
     */
    public function index()
    {
        $list = D('Space')->getSpaceCache();
        $this->assign('data',$list);
        $this->display();
    }

    /**
     * 删除广告位
     */
    public function delete()
    {
        // 删除广告位同时删除该广告位下的广告
        D('Advertise')->where(array('sid'=>I('get.id','','intval')))->delete();
        parent::delete();
    }

    /**
     * 预览广告位
     */
    public function showAd(){
        $sid = I('get.id', '', 'intval');
        $adshow = D('Space')->where(array('id'=>$sid))->getField('adshow');
        $ads = D('Advertise')->getAdv($sid, $adshow);
        $this->assign('ads',$ads);
        $this->display();
    }
}
?>