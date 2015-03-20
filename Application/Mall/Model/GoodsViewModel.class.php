<?php
// +--------------------------------------
// | 前端筛选条件视图模型类
// +--------------------------------------
// | Author: Kaiwei Sun 663642331@qq.com date:2014/07/24
// +--------------------------------------
namespace Mall\Model;
use Think\Model\ViewModel;
class GoodsViewModel extends ViewModel{
    protected $viewFields = array(
        'mall_goods' => array(
            'goods_name','market_price','goods_price', 'default_img','goods_id','store_id','cate_id','brand_id',
            '_type' => 'LEFT'
        ),
        'mall_goods_count' => array(
            'views','comment', '_on' => 'mall_goods.goods_id = mall_goods_count.goods_id'
        )
    );
    public function getAll($where,$order,$limit){
        return $this->where($where)->order($order)->limit($limit)->select();
    }
}

