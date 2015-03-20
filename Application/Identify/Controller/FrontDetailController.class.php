<?php

// +----------------------------------------------------------------------
// | 鉴定模块_前端_详细页_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
class FrontDetailController extends BaseController {
    public function _initialize() {
        parent::_initialize();
        $auth = getLoginStatus();
        $uid = $auth['uid'];
        $this->auth = $auth;
    }
    public function index() {
        
        // 获取ID
        $id = I('get.id', '0', 'int');

        $this->goodsid=$id;
        
        // 详细信息
        $this->info = D('IdentifyData')->where(array('id' => $id, 'isopen' => '1'))->find();
        empty($this->info) AND die('信息不存在或者已被删除');
        
        // 类别ID
        $this->cid = $this->info['category'];
        
        // 更新浏览次数
        $data['hits'] = array('exp', 'hits + 1');
        D('IdentifyData')->where(array('id' => $this->info['id']))->save($data);
        
        // 获取大师的名字和头像
        if($this->info['expertid']){
            $this->Expert = D('IdentifyExpert')->field('username, thumb, brief')->find($this->info['expertid']);
        }
        
        // 获取持宝者昵称
        $this->Member = M('Member')->field('nickname')->find($this->info['mid']);
                
        // 获取评论
        $sql = "SELECT M.username , C.* FROM bsm_identify_comment C LEFT JOIN bsm_member M ON M.mid = C.mid WHERE C.isopen=1 AND C.identifyid=".$this->info['id']." ORDER BY C.createtime DESC LIMIT 10";
//        $this->Comment = D('IdentifyComment')->where(array('identifyid' => $this->info['id'], 'isopen' => '1'))->order('createtime DESC')->limit(10)->select();
        $this->Comment = D('IdentifyComment')->query($sql);
        $this->CommentCount = D('IdentifyComment')->where(array('identifyid' => $this->info['id'], 'isopen' => '1'))->count();

        // 相关藏品(其他类别)
        $where = '`isopen` = 1 AND `isopen` = 1 AND `category` <> '.$this->info['category'];
        $this->arrOther = D('IdentifyData')->field('id, name, thumb, size, createtime')->where($where)->order('id DESC')->limit(5)->select();
        
        // 推荐藏品(推荐且不包含当前ID)
        $where = '`isopen` = 1 AND `isopen` = 1 AND `ispush` = 1 AND `id` <> '.$id;
        $this->arrPush = D('IdentifyData')->field('id, name, thumb, size, createtime')->where($where)->order('id DESC')->limit(5)->select();
        
        //print_r($this->Comment);exit;

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


                
        $this->display('Front:detail');
    }


}
