<?php

// +----------------------------------------------------------------------
// | 计划任务 导入第三方（自有平台）商品数据到艺术拍卖品
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Task\Controller;

class ThirdController
{
	public function _initialize()
    {

        
    }

	public function index()
    {			
		$yf_platform = 1;
		$yl_platform = 2;
		$yff_platform = 3;
		$lp_platform = 4;

		//$this->batch($yf_platform);
		//$this->batch($yl_platform);
		//$this->batch($yff_platform);
		$this->batch($lp_platform);
	}

	//批量入库 plat_id平台id limit一次批量几条
	private function batch($plat_id,$limit=500){
		$table_con = '';
		$table_pre = '';
		$sn_pre = '';
		switch($plat_id){
			case 1:$table_con = 'DB_YUFU';$table_pre = 'jade_';$sn_pre = 'YF_';$img_prefix = '/Public/img/third/yf/';$index = 1;break;
			case 2:$table_con = 'DB_YULIN';$table_pre = 'zb_';$sn_pre = 'YL_';$img_prefix = '/Public/img/third/yl/';$index = 2;break;
			case 3:$table_con = 'DB_YUFEIFAN';$table_pre = 'zg_';$sn_pre = 'YFF_';$img_prefix = '/Public/img/third/yff/';$index = 3;break;
			case 4:$table_con = 'DB_LANPO';$table_pre = 'hp_';$sn_pre = 'LP_';$img_prefix = '/Public/img/third/lp/';$index = 4;break;
			default:break;

		}
		//$yu_arr = M()->db(1,$table_con)->table($table_pre.'goods')->where("goods_number>0 and goods_thumb not like '%simg/%'")->limit($limit)->select();
		$yu_arr = M()->db($index,$table_con)->table($table_pre.'goods')->where("goods_number>0 and goods_thumb not like '%simg/%'")->select();
		foreach($yu_arr as $val){
			$paimai_obj = M('paimai_goods');
			$find = M('paimai_goods')->where(array('third_id'=>$val['goods_id'],'third_platform'=>$plat_id))->find();
			if(empty($find)){
				$data['goods_name'] = $val['goods_name'];
				$data['goods_sn'] = $sn_pre.$val['goods_sn'];
				$data['goods_brief'] = strip_tags($val['goods_desc']);
				$data['goods_intro'] = $val['goods_desc'];
				$data['goods_isshow'] = 0;
				$data['goods_keywords'] = $val['keywords'];
				$data['goods_createtime'] = time();
				$data['goods_sellername'] = $val['provider_name'];
				$data['goods_weight'] = $val['goods_weight'];
				$data['third_id'] = $val['goods_id'];
				$data['third_platform'] = $plat_id;
				$data['third_index_img'] = $img_prefix.$val['goods_thumb'];
				$data['goods_adminuid'] = 1;
				$insert_id = $paimai_obj->add($data);
				$yu_gallery = M()->db(1,$table_con)->table($table_pre.'goods_gallery')->where("goods_id=".$val['goods_id'])->select();
				foreach($yu_gallery as $v){
					$paimai_gallery_obj = M('paimai_gallery');
					$data1['goods_id'] = $insert_id;
					$data1['img_url'] = $img_prefix.$v['img_url'];
					$paimai_gallery_obj->add($data1);
				}
				//print_r($yu_gallery);
				//echo $paimai_obj->getLastSql();
			}

		}
		
	}
}
