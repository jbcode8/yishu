<?php

// +----------------------------------------------------------------------
// | 鉴定模块_前端_列表页_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
use Think\Model;
class FrontSearchController extends BaseController {
    public function _initialize() {
        parent::_initialize();
        $auth = getLoginStatus();
        $uid = $auth['uid'];
        $this->auth = $auth;
    }
    public function index() {
        
        // 获取搜索关键字
        $wd = I('get.wd', '', 'trim');
        empty($wd) AND die('请填写有效的搜索关键字');
        $where['name']= array('LIKE', '%'.$wd.'%');
        $where['keywords']= array('LIKE', '%'.$wd.'%');
        $where['_logic'] = 'OR';
        $newwhere['_complex'] = $where;
        
        $this->wd = $wd;
        
        // 类别数组
        if(!S('IdentifyCategory')){
            D('IdentifyCategory')->createIdentifyCategoryCache();
        }
        $this->arrCategory = S('IdentifyCategory');
        
        // 获取类别ID
        $cid = I('get.category', '', 'int');
        $cid !== '' AND $newwhere['category'] = $cid;
        $cid !== '' AND $this->cid = $cid;
        
        // 获取是否鉴定
        $isok = I('get.isok', '', 'int');
        $isok !== '' AND $newwhere['isok'] = $isok;
        $isok !== '' AND $this->isok = $isok;        
        
        // 分页需要定义为$this->Model
        $this->Model = D('IdentifyData');
        
        // 分页信息
        $this->page = $this->pages($newwhere);
        
        // 分页列表
        $field = array('id', 'name', 'category', 'isok', 'isopen', 'mid', 'createtime');
        $this->list = $this->Model->field('*')->where($newwhere)->limit($this->p->firstRow.', '.$this->p->listRows)->order('`id` DESC')->select();
        
        // 鉴定专家团
        $this->arrExpert = D('IdentifyExpert')->field('username, thumb')->order('id DESC')->limit(6)->select();
        
        // 推荐藏品(推荐且不包含当前ID)
        $where = '`isopen` = 1 AND `isopen` = 1 AND `ispush` = 1';
        $this->arrPush = D('IdentifyData')->field('id, name, thumb, size, createtime')->where($where)->order('id DESC')->limit(5)->select();

        //最新拍卖
        $hotSql = 'SELECT D.currentprice,D.endtime,D.id,D.gid,D.hits,G.name,G.thumb FROM bsm_auction_data D,bsm_auction_goods G WHERE D.gid = G.id ORDER BY D.id DESC LIMIT 2';
        $Model = new Model();
        $this->hotAuction = $Model->query($hotSql);

        $this->display('Front:search');
    }
}
