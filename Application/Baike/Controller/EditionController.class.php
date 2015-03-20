<?php
// +----------------------------------------------------------------------
// | 艺术百科词条版本库控制器文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Controller;

class EditionController extends  BaikeAdminController{

    /**
     * 版本库列表
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
        if($st && $et && $st != $et){ $map['time'] = array('between', array($st,$et));} else if($st){ $map['time'] = array('egt', $st); } else if ($et){ $map['time'] = array('elt', $et); }else{}
        //创建者判断
        if($auhtor = I('get.author')){
            $member = memberInfo($auhtor,true);
            $auhtorid = $member['id'] ? $member['id'] : 0;
            $map['authorid'] = $auhtorid;
        }

        $list = parent::lists('Edition', $map, 'time DESC');
        $this->assign('_list',$list);
        $this->display();
    }


    /**
     * 重写审核方法，加入版本替换
     * 替换规则：版本审核通过，则当前正在使用的版本加入版本库，审核通过的版本替换正在使用的版本，删除审核通过的版本数据
     */
    public function audit(){
        $model = D('Edition');
        $pk = $model->getPk();
        $ids = I('post.ids');
        if (!$ids) {
            $this->error('没有选择任何信息');
        }else{
            $map[$pk] = array('IN', $ids);
            $data['visible'] = 1;
            $res = $model->where($map)->save($data);
            if ($res) {
                $e_ids = explode(',', $ids);
                foreach($e_ids as $id){
                    $this->editionReplace($id);
                }
                $this->success('操作成功');
            }
            else {
                $this->error('没有任何修改');
            }
        }
    }

    /**
     * 版本的审核替换
     * @param $eid
     */
    private  function editionReplace($eid){
        //方案一：词条首次审核同过即刻加入版本库，设置审核状态为通过，后续版本审核依次替换即可
        //  缺点：冗余了doc表的所有数据
        //  优点：及时性较好，编码比较简单易理解，doc表不用ip字段
        //方案二：词条首次审通过不加入版本库；当有用户编辑词条即版本产生后审核版本将正在使用的版本加入版本库，审核通过的版本替换正在使用的版本，删除审核通过的版本数据
        //  缺点：编码较为繁琐，会丢失编辑时的用户IP信息，编辑类型，编辑原因
        //  优点：没有数据冗余，版本库数据较单一  创建临时表储存会丢失的数据，便于变笨替换的时候回去
        $em = D('Edition');
        $dm = D('Doc');
        $tm = D('DocTemp');
        $edition = $em->field('did,authorid,time,ip,tag,content,imginfo,summary,type,reason')->where(array('eid'=>$eid))->find();
        $doc = $dm->field('did,cid,tag,title,summary,content,lasteditorid,lastedit,imginfo')->where(array('did'=>$edition['did']))->find();

        //获取临时数据
        $temp = $tm->where(array('did'=> $edition['did']))->find();

        //词条加入版本库
        $words = floor(2/3*strlen(htmlspecialchars_decode($doc['content'])));
        $data = array(
            'did'=>$doc['did'],
            'cid'=>$doc['cid'],
            'title'=>$doc['title'],
            'tag'=>$doc['tag'],
            'summary'=>$doc['summary'],
            'authorid'=>$doc['lasteditorid'],
            'time'=>$doc['lastedit'],
            'content'=>$doc['content'],
            'imginfo'=>$doc['imginfo'],
            'words' => $words,
            'visible'=> 1
        );
        if($temp){
            $data['ip'] = $temp['ip'];
            $data['type'] = $temp['type'];
            $data['reason'] = $temp['reason'];
        }
        $em->add($data);

        //修改词条
        $data =array(
            'did' => $edition['did'],
            'lasteditorid' => $edition['authorid'],
            'lastedit' => $edition['time'],
            'imginfo' => $edition['imginfo'],
            'summary' => $edition['summary'],
            'content' => $edition['content'],
            'tag' => $edition['tag'],
        );
        $dm->save($data);

        //修改临时数据
        $data = array('did'=>$edition['did'], 'ip'=>$edition['ip'], 'type'=>$edition['type'], 'reason'=>$edition['reason']);
        if($temp){  $tm->save($data);  } else{ $tm->add($data);  }

        //删除当前版本
        $em->where(array('eid'=>$eid))->delete();
    }

    /**
     * 版本对比
     */
    public function contrast(){
        $eid = I('get.eid', '', 'intval');
        $did = I('get.did', '', 'intval');
        if($eid && $did){
            //对比版本
            $edition = D('Edition')->field('authorid,content,imginfo,time')->where(array('eid'=>$eid))->find();
            $content = htmlspecialchars_decode($edition['content']);
            $edition['sectionlist'] = D('Doc')->splithtml($content);
            unset($edition['content']);
            //线上词条
            $doc = D('Doc')->field('lasteditorid,content,imginfo,lastedit')->where(array('did'=>$did))->find();
            $content = htmlspecialchars_decode($doc['content']);
            $doc['sectionlist'] = D('Doc')->splithtml($content);
            unset($doc['content']);

            $this->assign('old', $doc);
            $this->assign('new', $edition);
            $this->display();
        }
        else{
            $this->error('参数有误！');
        }
    }
}