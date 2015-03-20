<?php

namespace Paimai\Controller;

use Paimai\Controller\PaimaiPublicController;

class FrontLtopicController extends PaimaiPublicController
{

    /**
     * 初始化
     */
    public function _initialize()
    {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
       // $this->assign('title', '中国艺术网-拍卖');
    }

	/**
     * 列表信息
     */
    public function index()
    {
		$this->title=$special['special_name']."拍卖专场_中国艺术网在线竞拍";
		//$this->keywords=$special['special_keywords'];
		//$this->desc=$special['special_intro'];
		$this->keywords="拍卖专场";
		$this->desc="中国艺术网在线竞拍隆重上线拍卖专场活动，玩家可以在拍卖专场上拍卖到最新最有价值的藏品，数量有限，先到先得，中国艺术网在线竞拍安全交易，值得信赖。";
		$special_where=array(
			"special_isshow"=>1,
			"special_isdelete"=>0,
			//"special_endtime"=>array('GT', time())
		);
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
        //每页显示的数量
        $prePage = 60;
        
        $special_obj=M('PaimaiSpecial');
        $special_limit=10;

        //即将开始专场
        $starting_special_where=$special_where;
        $starting_special_where['special_starttime']=array('GT',time());
        $starting_special_arr = $special_obj->where($special_where)->limit($special_limit)->/*page($p . ',' . $prePage)->*/order("special_id desc")->select();
        $this->starting_special_arr=$this->_formatspecial($starting_special_arr);
        //v($starting_special_arr);
        //p($starting_special_arr);

        //即将结束专场
        $started_special_where=$special_where;
        $started_special_where['special_starttime']=array('LT',time());
        $started_special_where['special_endtime']=array('GT',time());
        $started_special_arr = $special_obj->where($started_special_where)->limit($special_limit)->/*page($p . ',' . $prePage)->*/order("special_id asc")->select();
        $this->started_special_arr=$this->_formatspecial($started_special_arr);
        //v($started_special_arr);

       //p($started_special_arr);
       //已经结束专场
        $end_special_where=$special_where;
        //$end_special_where['special_starttime']=array('LT',time());
        $end_special_where['special_endtime']=array('LT',time());
        $end_special_arr = $special_obj->where($end_special_where)->limit($special_limit)->/*page($p . ',' . $prePage)->*/order("special_id desc")->select();
        $this->end_special_arr=$this->_formatspecial($end_special_arr);
        //p($end_special_arr);

		//即将开拍(预拍)
		/*$listp = array();
		//即将结束(正在拍卖)
		$liste = array();
		//循环分配数据
		foreach ($list as $key => $value) {
			# code...
		}
		foreach($list as $v){
			if($v['special_endtime']-time()<48*3600){
				$liste[] = $v;
			} else {
				$listp[] = $v;
			}
		}
		$this->liste = $liste;
		$this->listp = $listp;
        $this->assign("lists", $list);
        $Page = new \Think\Page($page_total_count, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        //p($show);
		$suffix=$_SERVER['QUERY_STRING'];*/
		//echo $suffix."<br/>";
		//去除开头两个字符串,并替换&&为?
		//$suffix=str_replace("&&","?&",substr($suffix,3));
		//echo $suffix."<br/>";
		//去除分页 p=3中的数字
		//$suffix=preg_replace("/&p=(\d+)*/","",$suffix);
		
		//$show=preg_replace("/(.*)Paimai\/FrontLtopic\/index(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);
        //$this->assign('page', $show); // 赋值分页输出
        //分配body css
		$this->display('Front:ltopic4');
		
	}
	/*
	传入专场数组和状态，返回网页中需要的数组
	*/
	public function _formatspecial($list){
		foreach ($list as $k => $v) {
            
        }
		foreach($list as &$val){
			$goods_field=array(
				'goods_id',
				'recordid',
				);
			$goods_where=array(
				'goods_specialid'=>$val['special_id'],
				'goods_isdelete'=>0,
				'goods_isshow'=>1,
				);
			$val['thumb'] = D('Content/Document')->getPic($val['recordid'], 'thumb');
			//本專場下面的商品竞拍的次数
			$val['goods_people_count']=M('PaimaiGoods')->where($goods_where)->sum("goods_bidtimes");
			//专场下面的拍品数
			$val['goods_count']=M('PaimaiGoods')->where($goods_where)->count();
			$val['goods']=M('PaimaiGoods')->field($goods_field)->where($goods_where)->limit(5)->select();
			//这块可能导致网页速度变慢，可以在网页中把这个写成ajax来请求商品图片
			foreach ($val['goods'] as &$v) {
				$v['thumb']=D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
			}	
		}
		return $list;
	}
	public function ajax_loadmore(){
		if(!IS_AJAX)$this->error("此页面不存在");
		$p=I('p',0,'intval');
		$num=I('num',0,'intval');
		$special_field=array(
			"*",
			"special_name",
			"special_starttime",
			"special_endtime",
			"recordid",
			);
		$special_where=array(
			"special_isshow"=>1,
			"special_isdelete"=>0,
			);

		$special_limit=$p.",".$num;

		$list = M('PaimaiSpecial')->where($special_where)->limit($special_limit)->order("special_id desc")->select();
		if(empty($list)){
            $data['status']=0;
            echo json_encode($data);
            exit;
        }
        foreach ($list as $k => &$v) {
        	//专场图片
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            $v['special_name']=substr_CN($v['special_name'],15);
            //剩余时间
            $v['leave_time']=$v['special_endtime']-time();
            //开始时间格式化
            $v['special_starttime']=date("m.d/H:s",$v['special_starttime']);
            //结束时间格式化
            $v['special_endtime']=date("m.d/H:s",$v['special_endtime']);
            //得到商品
            $goods = M('PaimaiGoods')->field('goods_id,goods_name,recordid')->where(array('goods_specialid'=>$v['special_id'],'goods_isdelete'=>0,'goods_isshow'=>1))->order('goods_id desc')->select();
            //得到商品数量
			$v['goods_count'] = getGoodscount($v['special_id']);
			//初始参拍人
			$v['goods_people_count'] = 0;
			$str="";
			foreach($goods as $m=>&$n){
				//商品图片
				$goods_thumb= D('Content/Document')->getPic($n['recordid'], 'thumb',$n['goods_id']);
				//取出前五个组织html
				if($m<5){
					$str.="<li>";
						$str.="<a href='/paimai/goods-".$n['goods_id'].".html'>";
							$str.="<img src='".$goods_thumb."' width='40' height='40'/>";
						$str.="</a>";
					$str.="</li>";
				}
				//本商品的参拍人
				$goods_people_num = M('PaimaiBidrecord')->where('bidrecord_goodsid='.$n['goods_id'])->getField('count(distinct(bidrecord_uid)) as people_num');
				//参拍人累加到专场
				$v['goods_people_count'] += $goods_people_num;
			}
			//商品图片列表 html
			$v['goods_list']=$str;
        }
        //p($list);
		echo json_encode($list);
		exit;
	}
	/*通过商品id返回商品列表*/
	public function getgoodslistbyspecialid(){

	}

}