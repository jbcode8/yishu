<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 产品 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.11.02
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class AdminGoodsController extends AdminController {

    public function _initialize(){

        parent::_initialize();

        $this->modelName = 'Goods';
    }

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

        $list = $this->lists($this->modelName, $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    public function detail(){

        isset($_GET['goods_id']) AND $gid = intval($_GET['goods_id']);

        $data = D($this->modelName)->find($gid);
        if($data){

            // 赋值
            $this->vo = $data;

            // 获取组图
            $aryPics = D('GoodsImage')->field('img_url')->where(array('goods_id'=>$data['goods_id']))->order('listorder ASC')->select();
            empty($aryPics) OR $this->aryPics = $aryPics;

            // 售后服务 & 鉴定证书 & 认证标识
            $this->aryGoodsService = D($this->modelName)->getCheckboxName('goods_service', $data['goods_service']);
            $this->aryGoodsCertificate = D($this->modelName)->getCheckboxName('goods_certificate', $data['goods_certificate']);
            $this->aryGoodsAttest = D($this->modelName)->getCheckboxName('goods_attest', $data['goods_attest']);

            $this->display();
        }

    }

    // 产品状态(0.下架；1.待审；2.正常)
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

        if(D($this->modelName)->where($where)->setField($field) !== false){
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

        if(D($this->modelName)->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }
}