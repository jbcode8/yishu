<?php

// +----------------------------------------------------------------------
// | 拍卖模块_自动出价_[会员中心]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;

use Member\Controller\MemberController;

class CenterAutoController extends MemberController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionAutoPrice a');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
        C('PAGE_NUMS', 1); // 重置分页条数
        $this->page = $this->pages();
        
        // 用户mid
        $mid = session('mid');
        
        // 分页列表
        $this->list = $this->Model->join('bsm_auction_data D ON a.aid = D.id')->field('a.*,D.title,D.currentprice')->limit($this->p->firstRow.', '.$this->p->listRows)->order('a.price DESC')->select();
        
        $this->display('Center:auto_index');
    }
    
    /**
     * 更新是否公开的状态
     */
     public function update() {
         
        // 条件：关联此用户对应的信息
        $where['mid'] =  session('mid');
        $data = $this->Model->where($where)->find(I('get.id', 0, 'int'));
        
        empty($data) AND $this->error('此信息不存在或者已被删除！');
        
        //更新操作
        $boolean = $this->Model->where(array('id' => $data['id']))->filter('strip_tags')->save(array('isopen' => I('get.isopen', 0, 'int')));
        if ($boolean !== false) {
            $this->success('状态更新成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
        } else {
            $this->error('状态更新失败！');
        }
     }

    /**
     * 删除信息
     */
    public function delete() {
        
        $this->error('信息删除失败！');
        
        // 读取信息并检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        if ($data) {
            $boolean = $this->Model->delete($data['id']);
            if ($boolean !== false) {
                $this->success('信息删除成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
            } else {
                $this->error('信息删除失败！');
            }
        } else {
            $this->error('此信息不存在或已经删除！');
        }
    }

}
