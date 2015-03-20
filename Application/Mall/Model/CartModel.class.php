<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 购物车模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2013.10.30
// +----------------------------------------------------------------------

namespace Mall\Model;

class CartModel extends MallBaseModel{

    /**
     * 加入购物车
     * @param $sid
     * @param $id
     * @param int $n
     * @return int
     */
    public function add2cart($sid, $id, $n = 1){

        $sessCart = session('sessCart');

        if(!empty($sessCart) && isset($sessCart[$sid])){
            if(!array_key_exists($id, $sessCart[$sid]['g'])){
                $sessCart[$sid]['g'][$id] = $n;
            }else{
                return false;
            }
        }else{
            $sessCart[$sid]['g'][$id] = $n;
            $sessCart[$sid]['sn'] = orderSN();
            $sessCart[$sid]['tm'] = time();
        }

        session('sessCart', $sessCart);
        return true;
    }
}
