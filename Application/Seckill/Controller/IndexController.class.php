<?php
//专场列表
namespace  Seckill\Controller;
    use Seckill\Controller\SeckillController;

 class IndexController extends SeckillController
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

		$this->title="抢拍专场_中国艺术网在线竞拍";
		$this->keywords="抢拍专场";
		$this->desc="中国艺术网在线抢拍隆重上线抢拍专场活动。";

		//已经开始 -----------------------
		$special_where=array(
			"skspecial_isshow"=>0,
			"skspecial_isdelete"=>0,
			"skspecial_endtime"=>array('GT', time()),
            "skspecial_starttime"=>array('LT', time()),
		);
        $list = M('SeckillSpecial')->where($special_where)->order("skspecial_id asc")->select();
        foreach ($list as $k => $v) {
            $list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }

       
        
	  foreach($list as &$val){
			$goods_field=array(
				'skgoods_id',
				'recordid',
				);
			$goods_where=array(
				'skgoods_specialid'=>$val['skspecial_id'],
				'skgoods_isdelete'=>0,
				'skgoods_isshow'=>0,
				);
			//本專場下面的商品竞拍的次数
			//$val['goods_people_count']=M('PaimaiGoods')->where($goods_where)->sum("goods_bidtimes");
			//专场下面的拍品数
			$val['goods_count']=M('SeckillGoods')->where($goods_where)->count();
			$val['goods']=M('SeckillGoods')->field($goods_field)->where($goods_where)->limit(5)->select();
			//这块导致网页速度变慢，可以在网页中把这个写成ajax来请求商品图片
			foreach ($val['goods'] as &$v) {
				$v['thumb']=D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
			}	
		}
           $this->listp = $list;
 

       //即将开始  ------------------------   当前时间   <   开始时间
       $special_wheres=array(
			"skspecial_isshow"=>0,
			"skspecial_isdelete"=>0,
            "skspecial_starttime"=>array('GT', time()),
		);
        $liste = M('SeckillSpecial')->where($special_wheres)->order("skspecial_id asc")->select();
		
        foreach ($liste as $k => &$v) {
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
		
	  foreach($liste as &$val){
			$goods_field=array(
				'skgoods_id',
				'recordid',
				);
			$goods_where=array(
				'skgoods_specialid'=>$val['skspecial_id'],
				'skgoods_isdelete'=>0,
				'skgoods_isshow'=>0,
				);
			//本專場下面的商品竞拍的次数
			//$val['goods_people_count']=M('PaimaiGoods')->where($goods_where)->sum("goods_bidtimes");
			//专场下面的拍品数
			$val['goods_count']=M('SeckillGoods')->where($goods_where)->count();
			$val['goods']=M('SeckillGoods')->field($goods_field)->where($goods_where)->limit(5)->select();
			//这块导致网页速度变慢，可以在网页中把这个写成ajax来请求商品图片
			foreach ($val['goods'] as &$v) {
				$v['thumb']=D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
			}	
		}
        $this->liste = $liste;


		//p($list);
		
		$this->display('FrontPage:index');
		
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