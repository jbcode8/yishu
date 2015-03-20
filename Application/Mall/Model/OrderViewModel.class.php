<?php
/**
 * Description of OrderRelationModel 会员订单视图模型类
 * @author Kaiwei Sun date:2014/07/25
 */
namespace Mall\Model;
use Think\Model\ViewModel;
class OrderViewModel extends ViewModel {
    protected $viewFields = array(
        'mall_order' => array(
            'order_id','order_sn','add_time','pay_time','ship_time','finished_time','order_desc,status',
            '_type' => 'LEFT'
        ),
        'mall_order_goods' => array(
            'goods_id','store_id','goods_name','goods_price','goods_count','goods_img', 
            '_on' => 'mall_order.order_id = mall_order_goods.order_id'
        )
    ); 
    public function getOrder($map, $onpage = 0, $onepage = 10){
        return $this->where($map)->order('add_time desc')->page($onpage,$onepage)->select();
    }
    public function counts($map){
        return $this->where($map)->count();
    }
}
