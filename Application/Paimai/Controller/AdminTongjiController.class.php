<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-7
 * Time: 上午11:33
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminTongjiController extends AdminController{
    public function _initialize() {
        parent::_initialize();

    }
    public function goods(){

        $goods_obj=M("PaimaiGoods");
    #前台未开拍商品
        $frontstartbefore_where=array(
            'goods_starttime'=>array('GT',Time()),
            'goods_isshow'=>1,
            );
        //拍品总数
        $this->frontstartbefore_count =$goods_obj->where($frontstartbefore_where)->count();
        //拍品总价
        $this->frontstartbefore_nowpricesum =$goods_obj->where($frontstartbefore_where)->sum("goods_nowprice");

    #前台正地进行商品
        $frontstarting_where=array(
            'goods_starttime'=>array('LT',Time()),
            'goods_endtime'=>array('GT',Time()),
            'goods_isshow'=>1,
            );
        //拍品总数
        $this->frontstarting_count =$goods_obj->where($frontstarting_where)->count();
        //拍品总价
        $this->frontstarting_nowpricesum =$goods_obj->where($frontstarting_where)->sum("goods_nowprice");

    #前台已经结束商品
        $frontstartafter_where=array(
            'goods_endtime'=>array('LT',Time()),
            'goods_isshow'=>1,
            );
        //拍品总数
        $this->frontstartafter_count =$goods_obj->where($frontstartafter_where)->count();
        //拍品总价
        $this->frontstartafter_nowpricesum =$goods_obj->where($frontstartafter_where)->sum("goods_nowprice");

    #前台商品总数
        $frontgoods_where=array(
            'goods_isshow'=>1,
            );
        //拍品总数
        $this->frontgoods_count =$goods_obj->where($frontgoods_where)->count();
        //拍品总价
        $this->frontgoods_nowpricesum =$goods_obj->where($frontgoods_where)->sum("goods_nowprice");

    #出价总数
        $bidcount_where=array(
            'bidrecord_uid'=>array("NEQ",0),    
        );
        $this->BidCount=M("PaimaiBidrecord")->where($bidcount_where)->count();
        //假数据
        $this->_BidCount=M("PaimaiBidrecord")->count();
    #参拍总人数
        $joinercount_where=array(
            'bidrecord_uid'=>array("NEQ",0),
        );
        $this->JoinerCount=count(M("PaimaiBidrecord")->where($joinercount_where)->group("bidrecord_uid")->select());
        //假数据
        $this->_JoinerCount=count(M("PaimaiBidrecord")->group("bidrecord_uid")->select());

    #成交数
        $finishcount_where=array(
            'goods_isshow'=>1,
            'goods_isdelete'=>0,
            //'goods_successid'=>array('NEQ',0),
            'goods_status'=>3,//2为已经被拍下还未付款的商品,状态3为已经付款的商品
        );
        $this->FinishCount=M("PaimaiGoods")->where($finishcount_where)->count();
        
    #成交额(条件可以和上面进行合并)
        $finishmoney_where=array(
            'goods_isshow'=>1,
            'goods_isdelete'=>0,
            //'goods_successid'=>array('NEQ',0),
            'goods_status'=>3,//2为已经被拍下还未付款的商品,状态3为已经付款的商品
        );
        $FinishMoney=M("PaimaiGoods")->where($finishmoney_where)->sum("goods_nowprice");
        //调用资金格式化函数,将要显示的资金格式化
        $this->FinishMoney=format_money(empty($FinishMoney)?0:$FinishMoney);
        
        $this->display();
    }

} 