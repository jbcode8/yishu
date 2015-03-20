<?php
namespace Content\Controller;
use Home\Controller\HomeController;
/**
 * 搜索模型控制器
 */
class SearchController extends HomeController
{
    public function _initialize() {


    }
    /*搜索*/
    public function index()
    {
        G('begin');
        $Document = D('Document');
        $p = I('get.p', '', 'intval');
        $p = empty($p) ? 1 : $p;
        $q = I('get.q', '', 'strip_tags');
        $where['title']  = array('LIKE', '%'.$q.'%');
        $where['description']  = array('LIKE', '%'.$q.'%');
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        $map['status']  = 1;
        if($model = I('get.model', '', 'intval')) {
            $map['model'] = $model;
        }
        switch(I('get.time', '', 'strip_tags')){
            case 'day':
                $map['update_time'] = array('gt', strtotime("-1 day"));
                break;
            case 'week':
                $map['update_time'] = array('gt', strtotime("-7 day"));
                break;
            case 'month':
                $map['update_time'] = array('gt', strtotime("-30 day"));
                break;
            case 'year':
                $map['update_time'] = array('gt', strtotime("-365 day"));
                break;
            default:
                break;
        }
        $result = $Document->search($map, $p, 10);
        G('end');
        $info = array('count'=>$result['count'],'time'=>G('begin', 'end', 3), 'q'=>$q);
        $this->assign('lists', $result['list']);
        $this->assign('show', $result['show']);
        $this->assign('info', $info);
        $this->display();
    }
}
?>