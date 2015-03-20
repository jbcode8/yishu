<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-9
 * Time: 下午1:23
 */

namespace Paimai\Model;


use Think\Model;

class PaimaiSpecialModel extends Model{
    //自动验证
    protected $_validate = array(
        array("special_name","require","请添写专场名称"),
        array("special_name","0,30","专场不能大于30个字符","3","length"),
        array('recordid','','商品图片已经有人上传请重新上传上品图片！',0,'unique',1), // 在新增的时候验证图片recordid字段是否唯一
    );
    //自动完成
    protected $_auto = array (
        array("special_starttime","strtotime","3","function"),
        array("special_endtime","strtotime","3","function"),
        array("special_createtime","time","1","function"),
        array("special_updatetime","time","2","function"),
    );
    
	/**
	 * 获取推荐专场 by Usagi 2015-1-4
	 * $limit  获取条数
	 * @return 
	 */
	public function getRecommendSpecial($limit){
	    $special_field = 'special_id, special_name, special_starttime, special_endtime, recordid';
		
		if(date('H') > 22 || date('H')==22){//如果当前时间大于晚十点
			$special_where = array(
			'special_isshow' => 1,
			'special_isdelete' => 0,
			'special_starttime' => array('between',array(strtotime(date("Y-m-d", time()))-2*3600-1, strtotime(date("Y-m-d", time()))+24*3600))
			);
		} else {
			$special_where = array(
			'special_isshow' => 1,
			'special_isdelete' => 0,
			'special_starttime' => array('between',array(strtotime(date("Y-m-d", time()))-26*3600-1, strtotime(date("Y-m-d", time()))+22*3600-1))
			);
		}
		$Special=$this->field($special_field)->where($special_where)->order("special_id asc")->limit($limit)->select();

		//如果为空，调用最后N个专场
		if(empty($Special)){
			$Special=M("PaimaiSpecial")->where("special_isshow=1 and special_isdelete=0")->order("special_id desc")->limit($limit)->select();
		}
		foreach ($Special as $k => $v) {
			$Special[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
			//点击量
			$Special[$k]['hits'] = M('PaimaiGoods')->where(array('goods_specialid'=>$v['special_id']))->sum('goods_hits');
			//出价次数
			$Special[$k]['bidtimes'] = M('PaimaiGoods')->where(array('goods_specialid'=>$v['special_id']))->sum('goods_bidtimes');
			//拍品数
			//$Special[$k]['goods_count'] = M('PaimaiGoods')->where(array('goods_specialid' => $v['special_id'], 'goods_isshow' => 1, 'goods_isdelete' => 0))->count();
		 }
		 //专场名字修改
		 foreach ($Special as $k => $v) {
		 	$aa = explode('——',$v['special_name']);
		 	if(!$aa[1]){
		 		$aa = explode('一一',$v['special_name']);
		 	}
			if($aa[1]){
				$Special[$k]['special_name'] = $aa[1];
			}
		 }
		 $newSpecial = array();
         foreach($Special as $k => $v){
		     $newSpecial[$k/2][$k%2] = $v;
		 }
		 return $newSpecial;
	}

	/**
	 * 获取今日专场 by Usagi 2015-1-4
	 * $limit  获取条数
	 * @return 
	 */
	public function getTodaySpecial($limit){
	    $today_start_time = strtotime(date("Ymd", time()));
		$today_end_time = strtotime(date("Ymd", time()+86400));
        $today_special_field = 'special_id, special_name, special_starttime, special_endtime, recordid';
		$today_special_where = array(
			'special_isshow' => 1,
			'special_isdelete' => 0,
			'special_starttime' => array('between',array($today_start_time, $today_end_time))
		);
        $today_Special = M("PaimaiSpecial")->field($today_special_field)->where($today_special_where)->order("special_id asc")->limit($limit)->select();

		//如果为空，调用最后N个专场
		if(empty($today_Special)){
			$today_Special=M("PaimaiSpecial")->where("special_isshow=1 and special_isdelete=0")->order("special_id desc")->limit($limit)->select();
		}
		foreach ($today_Special as $k => $v) {
			$today_Special[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
			//点击量
			$today_Special[$k]['hits'] = M('PaimaiGoods')->where(array('goods_specialid'=>$v['special_id']))->sum('goods_hits');
			//出价次数
			$today_Special[$k]['bidtimes'] = M('PaimaiGoods')->where(array('goods_specialid'=>$v['special_id']))->sum('goods_bidtimes');
			//拍品数
			$today_Special[$k]['goods_count'] = M('PaimaiGoods')->where(array('goods_specialid' => $v['special_id'], 'goods_isshow' => 1, 'goods_isdelete' => 0))->count();
		 }

		 //专场名字修改
		 foreach ($today_Special as $k => $v) {
		 	$aa = explode('——',$v['special_name']);
		 	if(!$aa[1]){
		 		$aa = explode('一一',$v['special_name']);
		 	}
			$today_Special[$k]['special_name'] = $aa[1];
		 }
		 $today_newSpecial = array();
         foreach($today_Special as $k => $v){
		     $today_newSpecial[$k/2][$k%2] = $v;
		 }
		 return $today_newSpecial;
	}
} 