<?php

// +----------------------------------------------------------------------
// | 鉴定模块_前端_鉴定资讯_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;

class FrontNewsController extends BaseController {
    public function _initialize() {
        parent::_initialize();
        $auth = getLoginStatus();
        $uid = $auth['uid'];
        $this->auth = $auth;
    }
    public function index() {
        
        // 分页需要定义为$this->Model
        $this->Model = M('phpcms.news','v9_');
        
        $where = '`catid` = 100 AND `status` = 99';
        
        // 分页信息
        C('PAGE_NUMS', 10); // 重置分页条数
        $this->page = $this->pages($where);
        
        // 分页列表
        $field = array('username', 'thumb', 'brief');
        $this->list = $this->Model->field('*')->where($where)->limit($this->p->firstRow.', '.$this->p->listRows)->order('`id` DESC')->select();
        
        // 最新藏品
        $this->arrHot = D('IdentifyData')->field('id, name, thumb, size, createtime')->where('`isopen` = 1')->order('createtime DESC')->limit(6)->select();
        
        // 推荐藏品
        $this->arrPush = D('IdentifyData')->field('id, name, thumb')->where('`isopen` = 1 AND `isok` = 1 AND `ispush` = 1')->order('id DESC')->limit(4)->select();


        //拍卖作品展示
        //$Model = new Model();
        // = $Model->query("SELECT g.`name`,d.`id`,d.`endtime`,g.`thumb`,d.`startprice`,g.`size` FROM bsm_auction_data AS d LEFT JOIN bsm_auction_goods AS g ON d.gid=g.id ORDER BY d.`starttime` DESC LIMIT 4");
        $res= M(C('PHPCMS_DB').'.news','v9_')->field(array('url','title','thumb'))->where("thumb!='' AND catid=38")->order('inputtime DESC')->limit(4)->select();

        $this->goods01=$res[0];
        $this->goods02=$res[1];
        $this->goods03=$res[2];
        $this->goods04=$res[3];

        //热门展览
        $idstr = M(C('PHPCMS_DB').'.category','v9_')->where(array("catid"=>20))->getField('arrchildid');
        $this->hotshow = M(C('PHPCMS_DB').'.news','v9_')->field(array('url','title'))->where("`catid` IN (".$idstr.")")->limit(6)->select();

        //沙龙活动
        $this->activity = M(C('PHPCMS_DB').'.exhibition','v9_')->field(array('url','title','thumb'))->where("`catid` = 59 AND `thumb` != ''")->order("inputtime DESC")->limit(4)->select();

        $this->display('Front:news');
    }
    
}
