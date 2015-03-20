<?php
namespace Home\Controller;
/**
 * 文章tag控制器
 */
class TagController extends HomeController
{
    /*TAG*/
    public function index()
    {
        $Document = D('Document');
        $p = I('get.p', '', 'intval');
        $p = empty($p) ? 1 : $p;
        $tag = I('get.tag', '', 'strip_tags');
        $where['title']  = array('LIKE', '%'.$tag.'%');
        $where['description']  = array('LIKE', '%'.$tag.'%');
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        $map['status']  = 1;
        $result = $Document->search($map, $p, 10);
        //获取文章缩略图
        foreach($result['list'] as $k=>$v) {
            $result['list'][$k]['url'] = $Document->getpic($result['list'][$k]['recordid'],'thumb');
        }
        $this->assign('lists', $result['list']);
        $this->assign('show', $result['show']);
        $this->display();
    }
}
?>