<?php
// +----------------------------------------------------------------------
// | 大师频道大事记控制器
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Controller;


class EventsController extends ArtistAdminController{

    /**
     * 大事记管理列表
     */
    public function index(){
        $map = array();
        $list = parent::lists('Events', $map, 'createtime ASC');
        $this->assign('_list', $list);
        $this->display();
    }

}