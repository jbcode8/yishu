<?php
// +----------------------------------------------------------------------
// | 艺术百科参考资料控制器文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Controller;

class MaterialController extends BaikeAdminController {

    /**
     * 参考资料列表方法
     */
    public function index(){
        if($cid = I('get.cid')){
            $childs = category_childids(D('Category')->category(),$cid);
            if($childs){
                $map['cid'] = array('IN',$childs);
            }
        }
        if($name = I('name')){ $map['name'] = array('LIKE', '%'.$name.'%'); }
        //时间判断
        $st = I('start_time',0 ,strtotime);
        $et = I('end_time',0 ,strtotime);
        if($st && $et && $st != $et){ $map['lasttime'] = array('between', array($st,$et));} else if($st){ $map['lasttime'] = array('egt', $st); } else if ($et){ $map['lasttime'] = array('elt', $et); }else{}
        //创建者判断
        if($auhtor = I('get.author')){
            $member = memberInfo($auhtor,true);
            $auhtorid = $member['id'] ? $member['id'] : 0;
            $map['authorid'] = $auhtorid;
        }
        //词条判断
        if($doc = I('get.doc')){
            $did = D('Doc')->where(array('title'=>$doc))->getField('did');
            if($did){
                $map['did'] = $did;
            }
        }

        $list = parent::lists('Material',$map);
        $this->assign('_list',$list);
        $this->display();
    }

}