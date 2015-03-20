<?php
namespace Home\Controller;
/**
 * 搜索模型控制器
 */
class SearchController extends HomeController
{
    /*搜索*/
    public function index()
    {
        G('begin');
        /*
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
        */
        $Document = D('Document');
        $p = I('get.p', '', 'intval');
        $p = empty($p) ? 1 : $p;
        $q = I('get.q', '', 'strip_tags');
        $cid = I('get.cid',17);
        if(isset($_GET['search_type'])){
            switch($_GET['search_type']){
                case '资讯':
                    $cid = 17;
                    break;
                case '展讯':
                    $cid = 20;
                    break;
                default:
                    break;
            }
        }
        if(isset($_GET['search_content'])){
            $q = strip_tags($_GET['search_content']);
        }
        $where = '';
        $where .=  "(title like '%".$q."%' or ";
        $where .=  "description like '%".$q."%') ";
        $time = I('get.time', '', 'strip_tags');
        switch($time){
            case 'day':
                $where .= 'and updatetime >' .strtotime("-1 day");
                break;
            case 'week':
                $where .= 'and updatetime >' . strtotime("-7 day");
                break;
            case 'month':
                $where .= 'and updatetime >' . strtotime("-30 day");
                break;
            case 'year':
                $where .= 'and updatetime >' . strtotime("-365 day");
                break;
            default:
                break;
        }
        $table = '';
        if($cid == 17){
            $table = 'news';
        }elseif($cid == 20){
            $table = 'exhibition';
        }
        $this->cid = $cid;
        $this->time = $time;
        $this->q = $q;
        $page_num = 10;
        $result = $Document->searchV9($table,$where, $p, $page_num);
        $pages = ceil($result['count']/$page_num);
        $i = 1;
        $select = '';
        while ($i <= $pages) {
            if (I('get.p') == $i) {
                $select .= "<option value='" . $i . "' selected>" . $i . "</option>";
            } else {
                $select .= "<option value='" . $i . "'>" . $i . "</option>";
            }
            $i++;
        }
        G('end');
        $info = array('count'=>$result['count'],'time'=>G('begin', 'end', 3), 'q'=>$q);
        $this->assign('lists', $result['list']);
        $this->assign('page', $result['show']);
        $this->assign('pages', $pages);
        $this->assign('select',$select);
        $this->assign('info', $info);
        $this->display();
    }
}
?>