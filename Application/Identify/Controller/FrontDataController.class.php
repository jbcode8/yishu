<?php

// +----------------------------------------------------------------------
// | 鉴定模块_前端_首页_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
class FrontDataController extends BaseController {
    public function _initialize() {
        parent::_initialize();
        $auth = getLoginStatus();
        $uid = $auth['uid'];
        $this->auth = $auth;
    }
    public function index() {
        
        // 鉴定信息的字段和条件
        $field = 'id, thumb, name';
        $where['isopen'] = 1; // 公开
        //$where['isok'] = 0; // 未鉴定
        $order = 'id DESC';
        
        // 陶瓷分类下的鉴定信息
        $where['category'] = 1;
        $this->arrTC = D('IdentifyData')->field($field)->where($where)->order($order)->limit(8)->select();
        unset($where['category']);
        
        // 玉器分类下的鉴定信息
        $where['category'] = 2;
        $this->arrYQ = D('IdentifyData')->field($field)->where($where)->order($order)->limit(8)->select();
        unset($where['category']);
        
        // 书画分类下的鉴定信息
        $where['category'] = 3;
        $this->arrSH = D('IdentifyData')->field($field)->where($where)->order($order)->limit(8)->select();
        unset($where['category']);
        
        // 铜器分类下的鉴定信息
        $where['category'] = 4;
        $this->arrTQ = D('IdentifyData')->field($field)->where($where)->order($order)->limit(8)->select();
        unset($where['category']);
        
        // 钱币分类下的鉴定信息
        $where['category'] = 5;
        $this->arrQB = D('IdentifyData')->field($field)->where($where)->order($order)->limit(8)->select();
        unset($where['category']);
        
        // 杂项分类下的鉴定信息
        $where['category'] = 6;
        $this->arrZX = D('IdentifyData')->field($field)->where($where)->order($order)->limit(8)->select();
        unset($where, $field);
        
        // 鉴定专家团
        $this->arrExpert = D('IdentifyExpert')->field('id','username, thumb')->order('id DESC')->limit(4)->select();

        // 鉴定推荐
        $this->arrPush = D('IdentifyData')->field('id, name, thumb, size, createtime, answer')->where('`isopen` = 1 AND `isopen` = 1 AND `ispush` = 1')->order('id DESC')->limit(8)->select();

        // 鉴定 和 未鉴定
        $this->arrOK = D('IdentifyData')->field('id, name, thumb, size, createtime')->where('`isok` <> 0 AND `isopen` = 1')->order('id DESC')->limit(5)->select();
        $this->arrNO = D('IdentifyData')->field('id, name, thumb, size, createtime')->where('`isok` = 0 AND `isopen` = 1')->order('id DESC')->limit(5)->select();

        // 焦点图
        $aryIds = M(C('PHPCMS_DB').'.position_data', 'v9_')->field('id, data')->where('posid = 88')->limit(6)->select();
        $strIds = '';
        $aryImg = array();
        foreach($aryIds as $rs){
                $strIds.= ','.$rs['id'];
                $ary = str2array($rs['data']);
                $aryImg[$rs['id']] = $ary['thumb'];
        }
        $strIds = trim($strIds, ',');
        $this->aryFocus = M(C('PHPCMS_DB').'.news', 'v9_')->field('id ,title, url')->where('`id` IN ('.$strIds.')')->select();
        $this->aryImg = $aryImg;
        
        // 鉴定资讯
        $this->arrNews = M(C('PHPCMS_DB').'.news', 'v9_')->field('id ,title, url')->where('catid = 100')->order('id DESC')->limit(8)->select();

        $this->display('Front:index');
    }
}
