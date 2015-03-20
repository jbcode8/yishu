<?php

// +----------------------------------------------------------------------
// | 机器人控制器
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
namespace Rebot\Controller;

class IndexController
{
	var $per = 20; //均分小时数
	var $plus_range1 = 7; //幅度加比例
	var $plus_range2 = 3; //指定加比例
	public function _initialize()
    {

        
    }

	public function index()
    {
		echo date('Y-m-d H:i:s', time())."\n";
	}

	//拍卖机器人 全局扫描进行中的拍品，自动抬价至成本价 预计24小时内抬到成本价 因此把成本均分为20份 上下浮动 以达到预期
	public function paimai()
	{
		//查找所有进行中的拍品
		$cur_time = time();
		$goods = M('PaimaiGoods')->where('goods_starttime<='.$cur_time.' and goods_endtime>'.$cur_time.' and case when goods_cost>0 then goods_nowprice<goods_cost else goods_nowprice<goods_startprice end')->select(); //成本价存在的话当前价小于成本价 否则当前价小于起始价
		//print_r($goods);
		foreach($goods as $val){
			$goods_id = $val['goods_id'];
			$goods_nowprice = $val['goods_nowprice']; 
			$type = $this->rand_type();
			$cost = $val['goods_cost'];
			if($val['goods_everypricestyle']){
				$everyprice = geteveryprice($val['goods_nowprice']);
			}else{
				$everyprice = $val['goods_everyprice']; //加价幅度可能会动态变化 到时候根据当前价格改变
			}
			$rebot_plus = $this->robot_plus($cost,$type,$everyprice);
			M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_nowprice',$rebot_plus); //更新goods表
			M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_bidtimes',1);
			$username_rand = 'YS_'.sprintf("%06d", rand(1,999999));
			$data = array(
				'bidrecord_uid'=>0,
				'bidrecord_price'=>$goods_nowprice+$rebot_plus,
				'bidrecord_goodsid'=>$goods_id,
				'bidrecord_time'=>time(),
				'bidrecord_uname'=>$username_rand 
				);
			$bidrecord_object = M('PaimaiBidrecord');
			$bidrecord_object->add($data); //更新bidrecord表
			
			$per = $this->rand_goods_per();

			//随机到超过一次继续提价
			while($per-1 > 0){
				$goods_goon = M('PaimaiGoods')->where('goods_id = '.$goods_id.' and case when goods_cost>0 then goods_nowprice<goods_cost else goods_nowprice<goods_startprice end')->find();
				if(empty($goods_goon)){ //已提至成本价 结束
					break;
				}else{
					$goods_nowprice = $goods_goon['goods_nowprice']; 
					$type = $this->rand_type();
					if($goods_goon['goods_everypricestyle']){
						$everyprice = geteveryprice($goods_goon['goods_nowprice']);
					}else{
						$everyprice = $goods_goon['goods_everyprice']; //加价幅度可能会动态变化 到时候根据当前价格改变
					}
					$rebot_plus = $this->robot_plus($cost,$type,$everyprice);
					M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_nowprice',$rebot_plus); //更新goods表
					M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_bidtimes',1);
					$username_rand = 'YS_'.sprintf("%06d", rand(1,999999));
					$data_goon = array(
						'bidrecord_uid'=>0,
						'bidrecord_price'=>$goods_nowprice+$rebot_plus,
						'bidrecord_goodsid'=>$goods_id,
						'bidrecord_time'=>time(),
						'bidrecord_uname'=>$username_rand 
						);
					$bidrecord_object = M('PaimaiBidrecord');
					$bidrecord_object->add($data_goon); //更新bidrecord表
					$per -- ;
				}
				
			}
			//echo M('PaimaiBidrecord')->getLastSql();
		}
		echo 'success';
		

	}

	//改良版拍卖 根据时间点跑到固定比例
	public function paimaiv2(){
		$cur_time = time();
		$goods = M('PaimaiGoods')->where('goods_starttime<='.$cur_time.' and goods_endtime>'.$cur_time.' and goods_cost>0 ')->select();
		foreach($goods as $val){
			//先判断条件 时间点是否达到成本比例
			$time_diff_f = ''; //1:1小时内 2:2小时内 3:2小时-最后2小时 4:最后2小时
			$percent = ''; //该跑完的比例
			$cut_per = ''; //机器增长频率 这个值越高 随机轮到的概率越低 值越低 跑得越快
			$time_diff = $cur_time - $val['goods_starttime'];
			if($time_diff < 60*60*1){
				$time_diff_f = 1;
				$percent = 0.1;
				$cut_per = 10;
			}elseif($time_diff < 60*60*2){
				$time_diff_f = 2;
				$percent = 0.2;
				$cut_per = 20;
			}else{
				$time_diff = $val['goods_endtime'] - $cur_time;
				if($time_diff < 60*60*2){
					$time_diff_f = 4;
					$percent = 1;
					$cut_per = 20;
				}else{
					$time_diff_f = 3;
					$percent = 0.5;
					$cut_per = 30;
				}
			}
			if($val['goods_nowprice'] >= $val['goods_cost']*$percent){ //达到目标不跑了
				continue;
			}else{
				$goods_id = $val['goods_id'];
				$goods_nowprice = $val['goods_nowprice']; 
				$cost = $val['goods_cost'];
				if($val['goods_everypricestyle']){
					$everyprice = geteveryprice($val['goods_nowprice']);
				}else{
					$everyprice = $val['goods_everyprice']; 
				}

				$round_natural = ceil($val['goods_cost']*$percent/$everyprice); //自然增长所需次数
				$nrd = '';
				
				if($round_natural > $cut_per){
					$nrd = ceil($round_natural/$cut_per);
				}else{
					$nrd = $round_natural/$cut_per;
					$execute_flag = false;
					if($nrd < 0.3){
						$rand = rand(1,3);
						if($rand == 1){
							$execute_flag = true;
						}
					}elseif($nrd < 0.5 && $nrd >= 0.3){
						$rand = rand(1,2);
						if($rand == 1){
							$execute_flag = true;
						}
					}else{
						$execute_flag = true;
					}
				}


				if($nrd >= 1){
					//重新计算当前价 加价幅度			
					for($i=1;$i<=$nrd;$i++){
						$goods_repeat = M('PaimaiGoods')->where('goods_id = '.$goods_id)->find();
						if($goods_repeat['goods_nowprice'] >= $goods_repeat['goods_cost']*$percent){
							break;
						}
						if($goods_repeat['goods_everypricestyle']){
							$everyprice_repeat = geteveryprice($goods_repeat['goods_nowprice']);
						}else{
							$everyprice_repeat = $goods_repeat['goods_everyprice']; 
						}
						M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_nowprice',$everyprice_repeat); //更新goods表
						M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_bidtimes',1);
						$username_rand = 'YS_'.sprintf("%06d", rand(1,999999));
						$data = array(
							'bidrecord_uid'=>0,
							'bidrecord_price'=>$goods_repeat['goods_nowprice']+$everyprice_repeat,
							'bidrecord_goodsid'=>$goods_id,
							'bidrecord_time'=>time(),
							'bidrecord_uname'=>$username_rand 
							);
						$bidrecord_object = M('PaimaiBidrecord');
						$bidrecord_object->add($data); //更新bidrecord表
					}
				}else{
					if($execute_flag){
						M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_nowprice',$everyprice); //更新goods表
						M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_bidtimes',1);
						$username_rand = 'YS_'.sprintf("%06d", rand(1,999999));
						$data = array(
							'bidrecord_uid'=>0,
							'bidrecord_price'=>$goods_nowprice+$everyprice,
							'bidrecord_goodsid'=>$goods_id,
							'bidrecord_time'=>time(),
							'bidrecord_uname'=>$username_rand 
							);
						$bidrecord_object = M('PaimaiBidrecord');
						$bidrecord_object->add($data); //更新bidrecord表
					}
				}
			}			
		}
		echo 'success';
	}

	//3版 最后1小时之前10% 最后1小时100%
	public function paimaiv3(){
		$cur_time = time();
		$goods = M('PaimaiGoods')->where('goods_starttime<='.$cur_time.' and goods_endtime>'.$cur_time.' and goods_cost>0 ')->select();
		foreach($goods as $val){
			//先判断条件 时间点是否达到成本比例
			$time_diff_f = ''; //1:最后一小时 2:最后一小时前
			$percent = ''; //该跑完的比例
			$cut_per = ''; //机器增长频率

			$time_diff = $val['goods_endtime'] - $cur_time;
			if($time_diff < 60*60*5){
				$time_diff_f = 1; //最后一小时，改成最后五小时
				$percent = 1;
				$cut_per = 5;
			}else{
				$time_diff_f = 2; //最后一小时前
				$percent = 0.1;
				$cut_per = 250;
			}

			if($val['goods_nowprice'] >= $val['goods_cost']*$percent){ //达到目标不跑了
				continue;
			}else{
				$goods_id = $val['goods_id'];
				$goods_nowprice = $val['goods_nowprice']; 
				$cost = $val['goods_cost'];
				if($val['goods_everypricestyle']){
					$everyprice = geteveryprice($val['goods_nowprice']);
				}else{
					$everyprice = $val['goods_everyprice']; 
				}

				$round_natural = ceil($val['goods_cost']*$percent/$everyprice); //自然增长所需次数
				$nrd = '';
				
				if($round_natural > $cut_per){
					$nrd = ceil($round_natural/$cut_per);
				}else{
					$nrd = $round_natural/$cut_per;
					$execute_flag = false;
					if($nrd < 0.3){
						$rand = rand(1,3);
						if($rand == 1){
							$execute_flag = true;
						}
					}elseif($nrd < 0.5 && $nrd >= 0.3){
						$rand = rand(1,2);
						if($rand == 1){
							$execute_flag = true;
						}
					}else{
						$execute_flag = true;
					}
				}


				if($nrd >= 1){
					//重新计算当前价 加价幅度			
					for($i=1;$i<=$nrd;$i++){
						$goods_repeat = M('PaimaiGoods')->where('goods_id = '.$goods_id)->find();
						if($goods_repeat['goods_nowprice'] >= $goods_repeat['goods_cost']*$percent){
							break;
						}
						if($goods_repeat['goods_everypricestyle']){
							$everyprice_repeat = geteveryprice($goods_repeat['goods_nowprice']);
						}else{
							$everyprice_repeat = $goods_repeat['goods_everyprice']; 
						}
						M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_nowprice',$everyprice_repeat); //更新goods表
						M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_bidtimes',1);
						$username_rand = 'YS_'.sprintf("%06d", rand(1,999999));
						$data = array(
							'bidrecord_uid'=>0,
							'bidrecord_price'=>$goods_repeat['goods_nowprice']+$everyprice_repeat,
							'bidrecord_goodsid'=>$goods_id,
							'bidrecord_time'=>time(),
							'bidrecord_uname'=>$username_rand 
							);
						$bidrecord_object = M('PaimaiBidrecord');
						$bidrecord_object->add($data); //更新bidrecord表
					}
				}else{
					if($execute_flag){
						M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_nowprice',$everyprice); //更新goods表
						M('PaimaiGoods')->where(array('goods_id'=>$goods_id))->setInc('goods_bidtimes',1);
						$username_rand = 'YS_'.sprintf("%06d", rand(1,999999));
						$data = array(
							'bidrecord_uid'=>0,
							'bidrecord_price'=>$goods_nowprice+$everyprice,
							'bidrecord_goodsid'=>$goods_id,
							'bidrecord_time'=>time(),
							'bidrecord_uname'=>$username_rand 
							);
						$bidrecord_object = M('PaimaiBidrecord');
						$bidrecord_object->add($data); //更新bidrecord表
					}
				}
			}			
		}
		echo 'success';
	}


	//商品随机1-5次
	private function rand_goods_per(){
		return rand(1,5);
	}

	//选择幅度加还是指定加方式随机  幅度加70% 指定加30%
	private function rand_type()
	{
		$num = rand(1,10);
		if($num <= $this->plus_range1){
			return 1; //幅度加
		}else{
			return 0; //指定加
		}
	}

	//幅度加基数随机 
	private function rand1($param,$base){
		if($param == 1){
			if($base == 1){
				return $base;
			}else{
				$num = rand($base-1,$base+1);
				return $num;
			}
		}
		//其他param计算模式可以根据金额区分偏移量
	}
	
	//指定加 range = 1 2000以下 2 2000-10000 3 10000以上
	private function rand2($param,$basem,$range){
		if($param ==1){
			switch($range){
				case 1:$down = 0.1;$up = 0.1;break;
				case 2:$down = 0.1;$up = 0.15;break;
				case 3:$down = 0.1;$up = 0.2;break;	
				default:break;
			}
			$num = rand(round($basem*(1-$down)),round($basem*(1+$up)));
			return $num;
		}
		//其他param计算模式可以根据金额区分偏移量
	}

	//根据成本和方式计算浮动基数方向和加价金额
	private function robot_plus($cost,$type,$everyprice){
		$per_plus = round($cost/$this->per); //理论上每次加价幅度
		if($type){ //幅度加 均分取整后是1 *1 大于1则上下浮动100%
			//为了保证尽早到达向上取整
			$base = ceil($per_plus/$everyprice);
			$final_plus = ($this->rand1(1,$base))*$everyprice;
			
		}else{ //指定加 2000元以下 上下浮动10% 2000-10000元上浮动15%下浮动10% 10000元以上上浮动20%下浮动10%
			$basem = $per_plus;
			if($cost <= 2000){
				$range = 1;
			}elseif($cost > 2000 && $cost <= 10000){
				$range = 2;
			}elseif($cost > 10000){
				$range = 3;
			}
			$final_plus = $this->rand2(1,$basem,$range);
		}
		return $final_plus;		
	}
}
