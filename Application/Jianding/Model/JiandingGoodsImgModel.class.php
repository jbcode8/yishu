<?php 
namespace Jianding\Model;
use Think\Model;
class JiandingGoodsImgModel extends Model
{
    protected $tableName = 'jianding_img';
    
    /**
     * 获取所有商品图片
     */
	public function getAllImgByGoodsId($goods_id)
	{
		return $this->where(array('goods_id' => $goods_id))
		          ->limit(4)->select();
	}
}