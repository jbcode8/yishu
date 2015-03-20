<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 产品 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.11.02
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class GoodsController extends AdminController {

    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');
        $startTime = I('start_time', '', 'trim');
        $endTime = I('end_time', '', 'trim');
        $type = I('stype', '', 'trim');

        // 组装搜索条件
        if(!empty($kw) || !empty($startTime) || !empty($endTime) || !empty($type)){

            // 开始时间
            empty($startTime) OR $intStart = time2int($startTime, ' 00:00:00');
            $this->assign('start_time', $startTime);

            // 结束时间
            empty($endTime) OR $intEnd = time2int($endTime, ' 23:59:59');
            $this->assign('end_time', $endTime);

            // 关键字
            empty($type) AND $type = 'order_sn';
            empty($kw) OR $condition[$type] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
            $this->assign('type', $type);

            // 时间同时具有
            if($startTime != '' && $endTime != ''){
                $condition['add_time'] = array(between, array($intStart, $intEnd));
            }else{
                // 开始时间和结束时间单个
                empty($startTime) OR $condition['create_time'] = array('gt', $intStart);
                empty($endTime) OR $condition['create_time'] = array('lt', $intEnd);
            }
        }

        $list = $this->lists('Goods', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    public function detail(){

        $goods_id = I('get.goods_id','','intval');

        $this->vo = D('Goods')->find($goods_id);
        $this->display();
    }

    // 产品状态(0：未审核；1：审核；2：锁定)
    public function editstatus(){

        // 获取信息ID
        isset($_GET['goods_id']) AND $goods_id = intval($_GET['goods_id']);
        isset($goods_id) OR $this->error('信息不存在或者已删除！');

        // 获取状态值
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) OR $this->error('参数有误！');

        $where['goods_id'] = $goods_id;
        $field['status'] = $status;
        $field['update_time'] = time();

        if(D('Goods')->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }

    // 是否推荐(1：推荐；0：正常)
    public function editrecom(){

        // 获取信息ID
        isset($_GET['goods_id']) AND $goods_id = intval($_GET['goods_id']);
        isset($goods_id) OR $this->error('信息不存在或者已删除！');

        // 获取状态值
        isset($_GET['recom']) AND $recom = intval($_GET['recom']);
        isset($recom) OR $this->error('参数有误！');

        $where['goods_id'] = $goods_id;
        $field['recommend'] = $recom;
        $field['update_time'] = time();

        if(D('Goods')->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }

} 