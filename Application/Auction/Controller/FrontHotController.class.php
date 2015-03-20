<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Home\Controller\HomeController;
class FrontHotController extends HomeController {
    
    public function _initialize() {
        
        parent::_initialize();
        
        // 联表查询
        $this->table = 'yishu_auction_data D, yishu_auction_goods G';
        $this->field = 'D.startprice, D.currentprice, D.endtime, D.starttime, D.id, D.gid, G.name, G.thumb';
        $this->where = 'D.gid = G.id';
        $this->order = 'D.id DESC';
        $this->size = 12;
       // $this->Model = new Model();
	$this->assign('title', '中国艺术网-拍卖');

    }

    /**
     * 列表信息
     */
    public function index() {
        
        // 今日热拍总数
        //$arrCount = $this->Model->query('SELECT COUNT(1) FROM '.$this->table.' WHERE '.$this->where);
        $arrCount = M()->query('SELECT COUNT(1) FROM '.$this->table.' WHERE '.$this->where);

        if(empty($arrCount)){
            $count = 0;
        }else{
            $count = intval($arrCount[0]['COUNT(1)']);
            //$this->lists = $this->Model->query('SELECT '.$this->field.' FROM '.$this->table.' WHERE '.$this->where.' ORDER BY '.$this->order.' LIMIT '.$this->size);
            $this->lists = M()->query('SELECT '.$this->field.' FROM '.$this->table.' WHERE '.$this->where.' ORDER BY '.$this->order.' LIMIT '.$this->size);
        }
        //p($this->lists);
        //p($this->lists);
        // 返回总条数和每页数目
        $this->pageInfo = $count.'|'.$this->size;
       // p($this->pageInfo);
        // 今日时间
        $this->time = time();
        
        $this->display('Front:hot');
    }
    
    /**
     * ajax返回列表数据
     */
    public function lists(){
        
        $currentpage = intval($_GET['page']);
        if($currentpage <= 0){
            return false;
        }
        
        // 当前页
        //$currentpage = $page + 1;
        
        // 计算起始值和显示页数
        $limit = $currentpage == 1 ? $this->size : (($currentpage - 1) * $this->size).','.$this->size;
                
        // 联表查询
        $lists = $this->Model->query('SELECT '.$this->field.' FROM '.$this->table.' WHERE '.$this->where.' ORDER BY '.$this->order.' LIMIT '.$limit);
        
        // 获取每个拍卖信息的拍卖次数
        foreach($lists as $rs){
            $arrCount[$rs['id']] = getPriceCount($rs['id']);
        }
        
        //$this->display('Front:hot');
        
        $this->ajaxReturn(array('status' => 3, 'lists' => $lists, 'counts' => $arrCount));
    }
    
}
