<?php
    /**
     *抢拍商品相关的页面入口 
     *@time 2015-1-19
     *@author zhihui  
     * */ 
    namespace Seckill\Controller;
    use Seckill\Controller\SeckillController;

    class GoodsShowController extends SeckillController{
        //商品详情的入口
        public function index(){
        // 接受参数
    
        $goods_id = I('get.id', '0', 'intval');
        if ($goods_id == 0) $this->error('此信息不存在或已经删除！');
		
	#根据id查询商品
		$goods_where=array(
			'skgoods_id'=>$goods_id,
			//'skgoods_isshow'=>0,
            'skgoods_isdelete' =>0,
		);
        $GoodsObj = M('SeckillGoods');
        $Goods = $GoodsObj->where($goods_where)->find();

		//p($goods_id);
		//如果商品不存在报错
        //empty($Goods) AND $this->error('此信息不存在或已经删除！');
        //404页面，因为tp路由不到404，只能在这里定义
        if(empty($Goods)){
            header("Location: http://www.yishu.com/search/?m=content&c=index&a=error_404&model=paimai"); 
            exit();
        }
	//去除	$this->xpcount = M('paimai_collect')->count();
	#报名|提醒人数
        /*2015-1-22 去除$remind_where=array(
            'gid'=>$goods_id,
            );
         */
        /*2015-1-22 去除
		$this->remind_count = M('paimai_remind')->where($remind_where)->count();
         */
        //p($this->xpcounts);
    #更改拍品点击量
        //$GoodsObj->where(array('goods_id' => $goods_id))->setInc("goods_hits");	
        //改为专场点击量加1
        M('SeckillSpecial')->where(array('skspecial_id' => $Goods['skgoods_specialid']))->setInc("skspecial_hits");	
		
		//进入页面cookie新增浏览历史
		$cookie_browse = cookie('paimai_browse');
		if(!in_array($goods_id,$cookie_browse))
		{
			if(count($cookie_browse)<10){	
				$cookie_browse[] = $goods_id;
				cookie('paimai_browse',$cookie_browse);
			}else{			
				$temp_cookie = array_splice($cookie_browse,1,9);
				unset($cookie_browse);
				$cookie_browse = $temp_cookie;
				$cookie_browse[] = $goods_id;
				cookie('paimai_browse',$cookie_browse);
			}
		}

        //获得图片
        if($Goods['third_platform']){
			$third = 1;
		}else{
			$third = 0;
		}
        $Goods["pics"] = D('Content/Document')->getPic($Goods['recordid'], 'image',$Goods["skgoods_id"],5,$third);
        
        //p($Goods['pics']);
        $this->assign("goodspics", $Goods['pics']);
	
     #出价记录 如果出价记录不等于0,则查询出价记录
        
        /*无需这个 if ($Goods['goods_bidtimes']) {
            $this->bidrecord = M("PaimaiBidrecord")->where(array('bidrecord_goodsid' => $goods_id))->order("bidrecord_id desc")->select();
        }*/
		

      #商品属性,yishu_paimai_attribute 和yishu_paimai_attribute进行连接
        /*没有  $GoodsAttr = M("PaimaiGoodsattr")->join("yishu_paimai_attribute on yishu_paimai_goodsattr.goodsattr_attrid=yishu_paimai_attribute.attr_id")->field("attr_name,goodsattr_value")->where(array('goodsattr_goodsid' => $goods_id))->select();
		//v($GoodsAttr);
		foreach($GoodsAttr as $k=>$v){
			$GoodsAttr[$k]['goodsattr_value']=M('PaimaiGoodsattr')->where(array("goodsattr_id"=>$v['goodsattr_value']))->getField("goodsattr_value");
		}
		$this->assign("GoodsAttr",$GoodsAttr);
         */

	#面包屑导航
        //获得专场
        $this->Special=M("SeckillSpecial")->field("skspecial_id,skspecial_name,skspecial_hits")->where("skspecial_id=".$Goods['skgoods_specialid'])->find();
        //获得商品分类的名字用于面包屑导航
        //没有去除 $this->GoodsCat = M("PaimaiCategory")->field("cat_name,cat_spell")->where(array('cat_id' => $Goods['goods_catid']))->find();
		//p($Goods);
	#参拍人数
		//ajax已经搞定;

    #围观
        //点击量+竞拍量
        //$this->lookgoods=$Goods['goods_bidtimes']+$Goods['goods_hits'];
        //p($this->lookgoods);
	#评论分页
        //评论及评论分页
	/*评论不要了	$p=I("p",1,"intval");
        //每页显示的数量
        $prePage = 5;
        $field = array(
            "*"
        );
        $list = M("PaimaiComment")->where(array("comment_goodsid" => $goods_id))->field($field)->page($p . ',' . $prePage)->order("comment_id desc")->select();
        $this->assign("comment", $list);
        $count = M("PaimaiComment")->where(array("comment_goodsid" => $goods_id))->field($field)->order("comment_id desc")->select();
        //p($count);
        $this->count = count($count);
        $Page = new \Think\Page($this->count, $prePage);
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出
        //p($show);
        $this->assign('page', $show);
    */

    #同场推荐的产品  本分类下的商品,不包含自己
        $likegoods_field = array(
            'skgoods_id',
            'skgoods_name',
            'skgoods_killprice',
            'skgoods_marketprice',
            "recordid",
            "skgoods_starttime",
            "skgoods_endtime",
        );
		/*$likegoods_where=array(
			'goods_catid'=>$Goods['goods_catid'],
			'goods_id'=>array('not in', "$goods_id"),
		);*/
        $likegoods_where=array(
            'skgoods_specialid'=>$Goods['skgoods_specialid'],
            'skgoods_id'=>array('not in', "$goods_id"),
            'skgoods_isshow'=>0,
        );
        $LikeGoods = M('SeckillGoods')->field($likegoods_field)->where($likegoods_where)->order("skgoods_id desc")->limit(7)->select();
		foreach ($LikeGoods as $k => $v) {
            $LikeGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v["skgoods_id"],100,$third);
        }
		$this->assign("LikeGoods",$LikeGoods);
    #可能感兴趣的
         $interestgoods_field = array(
            'skgoods_id',
            'skgoods_name',
            'skgoods_killprice',
            'skgoods_marketprice',
            "recordid",
            "skgoods_starttime",
            "skgoods_endtime",
        );
        $interestgoods_where=array(
            'skgoods_id'=>array('not in', "$goods_id"),
            'skgoods_isshow'=>1,
        );
        $InterestGoods = M('SeckillGoods')->field($likegoods_field)->where($likegoods_where)->order("skgoods_id desc")->limit(4)->select();
        foreach ($InterestGoods as $k => $v) {
            $InterestGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v["skgoods_id"],100,$third);
        }
        $this->assign("InterestGoods",$InterestGoods);
	#对介绍编辑器内容进行转码
		if(!$Goods["third_platform"]){ 
			$goods['skgoods_intro'] = html_entity_decode($Goods['skgoods_intro'], ENT_QUOTES, 'UTF-8');
			$this->goods_intro=$goods['skgoods_intro'];
		}else{
			if($Goods['third_platform']){
				$third = 1;
			}else{
				$third = 0;
			}
			$pic_arr = D('Content/Document')->getPic($Goods['recordid'], 'image',$Goods["skgoods_id"],100,$third);
			$pic_str = '';
			foreach($pic_arr as $val){
				$pic_str .= '<img alt="" src="'.$val.'" data-bd-imgshare-binded="1">';
			}
			$goods['skgoods_intro'] = strip_tags(html_entity_decode($Goods['goods_intro'], ENT_QUOTES, 'UTF-8')).$pic_str;
			//print_r($goods['goods_intro']);
			$this->goods_intro=$goods['goods_intro'];
		}
        

	#seo信息,如果商品定义的seo相关信息没有，则取商品名字
		//title
        if(!empty($Goods['skgoods_seotitle'])){
            $this->title="【".$Goods['skgoods_seotitle']."】抢拍_抢拍价格_在线抢拍价格_中国艺术网在线抢拍";
        }else{
            $this->title="【".$Goods['skgoods_name']."】抢拍_抢拍价格_在线抢拍价格_中国艺术网在线抢拍";
        }
		
		//keywords
        if(!empty($Goods['skgoods_keywords'])){
            $this->keywords=$Goods['skgoods_keywords'].",".$Goods['skgoods_keywords']."价格,".$Goods['goods_keywords']."抢拍价格,".$Goods['goods_keywords']."在线抢拍价格";
        }else{
            $this->keywords=$Goods['skgoods_name'].",".$Goods['skgoods_name']."价格,".$Goods['goods_name']."抢拍价格,".$Goods['goods_name']."在线抢拍价格";
        }
		
		//描述
        if(!empty($Goods['goods_seodesc'])){
            $this->desc="中国艺术网在线竞拍频道为藏家提供一个".$Goods['skgoods_seodesc']."的在线拍卖平台，藏家可在线出价竞拍".$Goods['skgoods_seodesc']."，安全便利， 在规定时间出价".$Goods['skgoods_seodesc']."价格最高的藏家即可获得".$Goods['skgoods_seodesc']."，欢迎藏家竞拍。";
        }else{
            $this->desc="中国艺术网在线竞拍频道为藏家提供一个".$Goods['skgoods_name']."的在线拍卖平台，藏家可在线出价竞拍".$Goods['skgoods_name']."，安全便利， 在规定时间出价".$Goods['skgoods_name']."价格最高的藏家即可获得".$Goods['skgoods_name']."，欢迎藏家竞拍。";
        }
		
		//p($goods);
		//
		$this->shijian=time();
		if($Goods['skgoods_endtime']<time()){
			//这个是已经结束
			$Goods['tag']=2;
		}elseif($Goods['skgoods_starttime']>time()){
			//这个是还没开始
			$Goods['tag']=0;
		}else{
			//这个是正在进行
			$Goods['tag']=1;
		}
        /*没有去掉
		if($Goods['goods_everypricestyle']==1){
            //
            $Goods['goods_everyprice']=format_money(geteveryprice($Goods['goods_nowprice']));
        }
         */
        //p($Goods);
		$this->assign("goods", $Goods);
        $this->display('FrontPage:goods4');
        }
    
    }

