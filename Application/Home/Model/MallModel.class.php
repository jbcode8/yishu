<?php
namespace Home\Model;
use Think\Model;
/**
 * 古玩城基础模型
 */
class MallModel extends Model
{
    private $mall_prefix = 'yishu_';
    /**
     * 获取已审核店铺
     * @param  string $order 排序
     * @param  integer $sort 输出条数
     * @param  integer $grade 高级店铺
     * @return array
     */
    public function getStoresInfo($grade,$limit,$order = 'a.create_time desc'){
        $where_grade = '';
        if($grade){
            $where_grade = ' and a.store_grade = '.$grade;
        }
        $stores = $this->db()->query("SELECT a.store_id,a.store_name,a.store_owner_name,a.store_banner,b.name,a.create_time,a.store_desc from ".$this->mall_prefix."mall_store a join ".$this->mall_prefix."region b on a.region_id = b.id where a.status =1 ".$where_grade.' order by '.$order.' limit '.$limit);
        if($grade){ //高级店铺查询商品数
            foreach($stores as &$val){
                $goods_count = $this->db()->query("select count(1) as goods_count from ".$this->mall_prefix."mall_goods where store_id=".$val['store_id']);
                $val['goods_count'] = $goods_count[0]['goods_count'];
            }
        }else{ //非高级店铺查询第一家店铺的主营商品
            foreach($stores as $key=>&$val){
                if($key == 0){
                    $store_category = $this->db()->query("select cate_name from ".$this->mall_prefix."mall_store_category where pid = 0 and store_id=".$val['store_id']." order by listorder asc limit ".$limit);
                    $store_category_str = '';
                    if(!empty($store_category)){
                        foreach($store_category as $v){
                            $store_category_str .= $v['cate_name'].',';
                        }
                        $store_category_str = substr($store_category_str,0,-1);
                    }
                    $val['store_category'] = $store_category_str;
                }
            }
        }
        return $stores;
    }

    /**
     * 获取店铺分类
     * @return array
     */
    public function getStoresCategory(){
        $storesCategory = $this->db()->query("SELECT cate_id,cate_name,short_name FROM `".$this->mall_prefix."mall_category` WHERE parent_id = 0 and status = 1 order by listorder asc");
        return $storesCategory;
    }

    /**
     * 获取商品
     * @return array
     */
    public function getStoresGoods($limit){
        $storeGoods = $this->db()->query("SELECT goods_id,goods_name,default_img FROM `".$this->mall_prefix."mall_goods` WHERE status=2 and is_delete=1 order by create_time desc limit ".$limit);
        return $storeGoods;
    }
}