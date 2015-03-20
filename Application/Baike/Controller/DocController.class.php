<?php
// +----------------------------------------------------------------------
// | 艺术百科词条控制器文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Controller;

class DocController extends  BaikeAdminController{

    /**
     * 词条列表
     */
    public function index(){
        $map = array();
        //类别搜索
        if($cid = I('get.cid')){
            $childs = category_childids(D('Category')->category(),$cid);
            if($childs){
                $map['cid'] = array('IN',$childs);
            }
        }
        if($title = I('title')){ $map['title'] = array('LIKE', '%'.$title.'%');  }
        //时间判断
        $st = I('start_time',0 ,strtotime);
        $et = I('end_time',0 ,strtotime);
        if($st && $et && $st != $et){ $map['lastedit'] = array('between', array($st,$et));} else if($st){ $map['lastedit'] = array('egt', $st); } else if ($et){ $map['lastedit'] = array('elt', $et); }else{}
        //创建者判断
        if($auhtor = I('get.author')){
            $member = memberInfo($auhtor,true);
            $auhtorid = $member['id'] ? $member['id'] : 0;
            $map['authorid'] = $auhtorid;
        }
        $list = parent::lists('Doc', $map ,'lastedit DESC');
        $this->assign('_list', $list);
        $this->display();
    }

    /**
     * 词条移动方法
     */
    public function move(){
        //TODO 待前端页面数据调用完成在做
    }

    /**
     * 删除词条后刷新分类的词条数量
     */
    public function _before_delete(){
        if(IS_POST){
            D('Category')->refreshAllDocs();
        }
        else{
            D('Category')->refreshDocs(0);
        }
    }

    /**
     * 修改词条的类型  0 普通， 1 推荐， 2 热门，3 精彩
     */
    public function doctype(){
        $model = D('doc');
        $ids = I('post.ids');
        if (!$ids) {
            $this->error('没有选择任何信息');
        }
        else{
            $map['did'] = array('IN', $ids);
            $type = I('post.type', '', 'intval');
            if ($type === '') {
                $this->error('参数有误');
            } else {
                $data['type'] = $type;
                $res = $model->where($map)->save($data);
               if($res)
                   $this->success('操作成功');
                else
                   $this->error('没有任何修改');
            }
        }
    }


}