<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 总后台 品牌 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.25
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class BrandController extends AdminController {

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');
        $startTime = I('start_time', '', 'trim');
        $endTime = I('end_time', '', 'trim');

        // 搜索条件初始化
        $condition = array();

        // 组装搜索条件
        if(!empty($kw) || !empty($startTime) || !empty($endTime)){

            // 开始时间
            empty($startTime) OR $intStart = time2int($startTime, ' 00:00:00');
            $this->assign('start_time', $startTime);

            // 结束时间
            empty($endTime) OR $intEnd = time2int($endTime, ' 23:59:59');
            $this->assign('end_time', $endTime);

            // 关键字
            empty($kw) OR $condition['brand_name'] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);

            // 时间同时具有
            if($startTime != '' && $endTime != ''){
                $condition['create_time'] = array(between, array($intStart, $intEnd));
            }else{
                // 开始时间和结束时间单个
                empty($startTime) OR $condition['create_time'] = array('gt', $intStart);
                empty($endTime) OR $condition['create_time'] = array('lt', $intEnd);
            }
        }

        $list = $this->lists('Brand', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 是否推荐(1：推荐；0：正常)
    public function editrecom(){

        // 获取信息ID
        isset($_GET['brand_id']) AND $brand_id = intval($_GET['brand_id']);
        isset($brand_id) OR $this->error('信息不存在或者已删除！');

        // 获取状态值
        isset($_GET['recom']) AND $recom = intval($_GET['recom']);
        isset($recom) OR $this->error('参数有误！');

        $where['brand_id'] = $brand_id;
        $field['recommend'] = $recom;
        $field['update_time'] = time();

        if(D('Brand')->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }
    
    // 删除之前的判断
    public function _before_delete(){

        // 获取信息ID
        isset($_GET['brand_id']) AND $brand_id = intval($_GET['brand_id']);
        isset($brand_id) OR $this->error('信息不存在或者已删除！');

        $where['brand_id'] = $brand_id;
        
        // 检测类别是否关联产品
        $goodsCount = D('Goods')->where($where)->count();
        $goodsCount > 0 AND $this->error('此品牌下关联有产品，请先确定此品牌下没有产品后再删除！');
    }
    
}