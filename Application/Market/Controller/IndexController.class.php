<?php
/**
 * Description of IndexController
 * @date 2014/08/05
 * @author KAIWEI SUN <663642331@qq.com>
 */

namespace Market\Controller;

use Think\Controller;

class IndexController extends Controller {
    public function _initialize() {
        $auth = getLoginStatus();
        $uid = $auth['uid'];
        $this->auth = $auth;
    }
    public function index() {
        //通用导航栏
        $this->category = D('Home/Category')->getAll();
        //品牌
        $this->gallery_list = M('gallery_list')->field('id, name')->limit(10)->select();
        //行情分类
        $this->cate = M('market_cate')->select();
        //艺术家信息及作品
        $user = D('ArtistRelation')->relation(true)->field('id,name,goodat,description,jointime,view,recordid')->where(array('status' => 1))->limit(10)->select();
        $this->user = getData($user);
        $this->title = '书画行情 - 中国艺术网';
        $this->display('./Template/Market/Index/index.html');
    }

    //分页异步加载
    public function data_page() {
        $map = '';
        if (I('get.keyword')) {
            $map['name'] = array('like', '%' . I('get.keyword') . '%');
        }
        if (I('get.index')) {
            $map['index'] = I('get.index');
        }
        if (I('get.cid', '', 'intval')) {
            $map['cid'] = I('get.cid');
        }
        if (I('get.status', '', 'intval')) {
            $map['status'] = I('get.status');
        }
        if (I('get.trend', '', 'intval')) {
            $map['trend'] = I('get.trend');
        }
        if (I('get.price', '', 'intval')) {
            if (I('get.price') == 1) {
                $map['price'] = array('between', '1000,5000');
            } else if (I('get.price') == 2) {
                $map['price'] = array('between', '5000,10000');
            } else if (I('get.price') == 3) {
                $map['price'] = array('between', '10000,30000');
            } else if (I('get.price') == 4) {
                $map['price'] = array('between', '30000,50000');
            } else if (I('get.price') == 5) {
                $map['price'] = array('gt', 50000);
            }
        }
        $pages = 20;
        $count = D('MarketRelation')->relation(true)->where($map)->count();
        $p = I('get.p', 1, 'intval') ? I('get.p', 1, 'intval') : 1;
        if ($p <= 0 || $p > $count) {
            $p = 1;
        }
        $page = new Pages($count, $pages);
        $page->setConfig('prev', "◀ 上一页");
        $page->setConfig('next', '下一页 ▶');
        $page->setConfig('first', '◀ 首页');
        $page->setConfig('last', '尾页 ▶');
        $tolpage = ceil($count / $pages);
        $list = D('MarketRelation')->relation(true)->where($map)->order('id desc')->page($p, $pages)->select();
        $i = 1;
        $select = '';
        while ($i <= $tolpage) {
            if (I('get.p') == $i) {
                $select .= "<option value='" . $i . "' selected>" . $i . "</option>";
            } else {
                $select .= "<option value='" . $i . "'>" . $i . "</option>";
            }
            $i++;
        }
        $this->count = $count;
        $this->page = $page->show();
        $this->pages = $tolpage;
        $this->select = $select;
        $this->list = $list;
        $this->display('./Template/Market/Index/page.html');
    }

}
