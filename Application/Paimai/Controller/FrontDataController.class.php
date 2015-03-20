<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Paimai\Controller;
use Paimai\Controller\PaimaiPublicController;

class FrontDataController extends PaimaiPublicController
{


    /**
     * 初始化
     */
    public function _initialize()
    {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
        //$this->assign('title', '中国艺术网-拍卖');
		$this->uid=$_SESSION['mid'];

    }

    /**
     * 列表信息
     */
    public function index()
    {

        //p("222");
        // 接受参数
        $goods_id = I('get.id', '0', 'intval');
        if ($goods_id == 0) $this->error('此信息不存在或已经删除！');
		
	#根据id查询商品
		$goods_where=array(
			'goods_id'=>$goods_id,
			'goods_isshow'=>1,
		);
        $GoodsObj = M('PaimaiGoods');
        $Goods = $GoodsObj->where($goods_where)->find();

		//p($goods_id);
		//如果商品不存在报错
        //empty($Goods) AND $this->error('此信息不存在或已经删除！');
        //404页面，因为tp路由不到404，只能在这里定义
        if(empty($Goods)){
            header("Location: http://www.yishu.com/search/?m=content&c=index&a=error_404&model=paimai"); 
            exit();
        }

		$this->xpcount = M('paimai_collect')->count();
	#报名|提醒人数
        $remind_where=array(
            'gid'=>$goods_id,
            );
		$this->remind_count = M('paimai_remind')->where($remind_where)->count();
        //p($this->xpcounts);
    #更改拍品点击量
        $GoodsObj->where(array('goods_id' => $goods_id))->setInc("goods_hits");	
		
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
        $Goods["pics"] = D('Content/Document')->getPic($Goods['recordid'], 'image',$Goods["goods_id"],5,$third);
        
        //p($Goods['pics']);
        $this->assign("goodspics", $Goods['pics']);
	
     #出价记录 如果出价记录不等于0,则查询出价记录
        
        if ($Goods['goods_bidtimes']) {
            $this->bidrecord = M("PaimaiBidrecord")->where(array('bidrecord_goodsid' => $goods_id))->order("bidrecord_id desc")->select();
        }
		

      #商品属性,yishu_paimai_attribute 和yishu_paimai_attribute进行连接
        $GoodsAttr = M("PaimaiGoodsattr")->join("yishu_paimai_attribute on yishu_paimai_goodsattr.goodsattr_attrid=yishu_paimai_attribute.attr_id")->field("attr_name,goodsattr_value")->where(array('goodsattr_goodsid' => $goods_id))->select();
		//v($GoodsAttr);
		foreach($GoodsAttr as $k=>$v){
			$GoodsAttr[$k]['goodsattr_value']=M('PaimaiGoodsattr')->where(array("goodsattr_id"=>$v['goodsattr_value']))->getField("goodsattr_value");
		}
		$this->assign("GoodsAttr",$GoodsAttr);

	#面包屑导航
        //获得专场
        $this->Special=M("PaimaiSpecial")->field("special_id,special_name")->where("special_id=".$Goods['goods_specialid'])->find();
        //获得商品分类的名字用于面包屑导航
        $this->GoodsCat = M("PaimaiCategory")->field("cat_name,cat_spell")->where(array('cat_id' => $Goods['goods_catid']))->find();
		//p($Goods);
	#参拍人数
		//ajax已经搞定;

    #围观
        //点击量+竞拍量
        $this->lookgoods=$Goods['goods_bidtimes']+$Goods['goods_hits'];
        //p($this->lookgoods);
	#评论分页
        //评论及评论分页
		$p=I("p",1,"intval");
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

    #同场推荐的产品  本分类下的商品,不包含自己
        $likegoods_field = array(
            'goods_id',
            'goods_name',
            'goods_nowprice',
            "recordid",
            "goods_bidtimes",
        );
		/*$likegoods_where=array(
			'goods_catid'=>$Goods['goods_catid'],
			'goods_id'=>array('not in', "$goods_id"),
		);*/
        $likegoods_where=array(
            'goods_specialid'=>$Goods['goods_specialid'],
            'goods_id'=>array('not in', "$goods_id"),
            'goods_isshow'=>1,
        );
        $LikeGoods = M('PaimaiGoods')->field($likegoods_field)->where($likegoods_where)->order("goods_id desc")->limit(7)->select();
		foreach ($LikeGoods as $k => $v) {
            $LikeGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v["goods_id"],100,$third);
        }
		$this->assign("LikeGoods",$LikeGoods);
    #可能感兴趣的
         $interestgoods_field = array(
            'goods_id',
            'goods_name',
            'goods_nowprice',
            "recordid",
            "goods_bidtimes",
        );
        $interestgoods_where=array(
            'goods_id'=>array('not in', "$goods_id"),
            'goods_isshow'=>1,
        );
        $InterestGoods = M('PaimaiGoods')->field($likegoods_field)->where($likegoods_where)->order("goods_id desc")->limit(4)->select();
        foreach ($InterestGoods as $k => $v) {
            $InterestGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v["goods_id"],100,$third);
        }
        $this->assign("InterestGoods",$InterestGoods);
	#对介绍编辑器内容进行转码
		if(!$Goods["third_platform"]){ 
			$goods['goods_intro'] = html_entity_decode($Goods['goods_intro'], ENT_QUOTES, 'UTF-8');
			$this->goods_intro=$goods['goods_intro'];
		}else{
			if($Goods['third_platform']){
				$third = 1;
			}else{
				$third = 0;
			}
			$pic_arr = D('Content/Document')->getPic($Goods['recordid'], 'image',$Goods["goods_id"],100,$third);
			$pic_str = '';
			foreach($pic_arr as $val){
				$pic_str .= '<img alt="" src="'.$val.'" data-bd-imgshare-binded="1">';
			}
			$goods['goods_intro'] = strip_tags(html_entity_decode($Goods['goods_intro'], ENT_QUOTES, 'UTF-8')).$pic_str;
			//print_r($goods['goods_intro']);
			$this->goods_intro=$goods['goods_intro'];
		}
		
		//添加品牌说明
		if($Goods['goods_brandid']){
			$b_where = array('brand_id' => $Goods['goods_brandid']);
		    $brand_desc = M('PaimaiBrand')->where($b_where)->getField('brand_desc');
			$this->goods_intro .= $brand_desc;
		}
        

	#seo信息,如果商品定义的seo相关信息没有，则取商品名字
		//title
        if(!empty($Goods['goods_seotitle'])){
            $this->title="【".$Goods['goods_seotitle']."】拍卖_拍卖价格_在线拍卖价格_中国艺术网在线竞拍";
        }else{
            $this->title="【".$Goods['goods_name']."】拍卖_拍卖价格_在线拍卖价格_中国艺术网在线竞拍";
        }
		
		//keywords
        if(!empty($Goods['goods_keywords'])){
            $this->keywords=$Goods['goods_keywords'].",".$Goods['goods_keywords']."价格,".$Goods['goods_keywords']."拍卖价格,".$Goods['goods_keywords']."在线拍卖价格";
        }else{
            $this->keywords=$Goods['goods_name'].",".$Goods['goods_name']."价格,".$Goods['goods_name']."拍卖价格,".$Goods['goods_name']."在线拍卖价格";
        }
		
		//描述
        if(!empty($Goods['goods_seodesc'])){
            $this->desc="中国艺术网在线竞拍频道为藏家提供一个".$Goods['goods_seodesc']."的在线拍卖平台，藏家可在线出价竞拍".$Goods['goods_seodesc']."，安全便利， 在规定时间出价".$Goods['goods_seodesc']."价格最高的藏家即可获得".$Goods['goods_seodesc']."，欢迎藏家竞拍。";
        }else{
            $this->desc="中国艺术网在线竞拍频道为藏家提供一个".$Goods['goods_name']."的在线拍卖平台，藏家可在线出价竞拍".$Goods['goods_name']."，安全便利， 在规定时间出价".$Goods['goods_name']."价格最高的藏家即可获得".$Goods['goods_name']."，欢迎藏家竞拍。";
        }
		
		//p($goods);
		//
		$this->shijian=time();
		if($Goods['goods_endtime']<time()){
			//这个是已经结束
			$Goods['tag']=2;
			$Goods['back_status'] = 0;
		}elseif($Goods['goods_starttime']>time()){
			//这个是还没开始
			$Goods['tag']=0;
			$Goods['back_status'] = 1;
		}else{
			//这个是正在进行
			$Goods['tag']=1;
			$Goods['back_status'] = 1;
		}
		if($Goods['goods_everypricestyle']==1){
            //
            $Goods['goods_everyprice']=format_money(geteveryprice($Goods['goods_nowprice']));
        }
		$this->assign("goods", $Goods);
        //p($Goods);
        $this->display('Front:goods4');
    }

    /**
     * 拍卖的评论
     */
    public function comment()
    {
        $content = I('get.content', '', 'strip_tags');
        $goods_id = I('get.gid', 0, 'intval');
        
        $uid = $this->uid;
		
        if (!$uid) {
            $data['status']=0;
            $data['info']="请先登录,再进行评论";
            echo json_encode($data);
            exit;
        }

        if (strlen($content) >= 6) {
            $data['comment_uid'] = $uid;
            $data['comment_createtime'] = time();
            $data['comment_ip'] = get_client_ip();
            $data['comment_goodsid'] = $goods_id;
            $data['comment_content'] = $content;

            if (M("PaimaiComment")->data($data)->add()) {
                //评论成功
                $data['status']=1;
                $data['info']="评论成功";
                $data['time']=date("Y-m-d H:i:s",$data['comment_createtime']);
                $data['username']=getUsername($uid);//调用
                $data['comment']=$data['comment_content'];
                echo json_encode($data);
                exit;
            } else {
                $data['status']=2;
                $data['info']="评论失败,请重新尝试";
                echo json_encode($data);
                exit;
            }
        } else {
            $data['status']=3;
            $data['info']="评论字符不能少于六个";
            echo json_encode($data);
            exit;
        }
    }
    /*
    ajax对用户进行提醒
    */
    public function ajax_remind(){

    #没有登录
        if(empty($this->uid)){
            $data['status']=0;
            $data['info']="请先登录再操作";
            exit(json_encode($data));
        } else {
            $this->ajaxReturn(array('status' => 1), 'JSON');
        }

    // #接受数据
    //     //商品开始时间
    //     $starttime=I('starttime',0,'intval');
    //     //接受style,如果style=1,则为商品,如果为2,则为专场
    //     $style=I('style',0,'intval');
    //     //接受ID
    //     $id=I('id',0,'intval');
    //     //判断参数是否正确
    //     if(empty($style)||empty($starttime)||empty($id)){
    //         $data['status']=2;
    //         $data['info']="请正确传入参数";
    //         exit(json_encode($data));
    //     }
    //     //$this->uid=1674;

    // #where
    //     $remind_where=array(
    //         'uid'=>$this->uid,
    //         'gid'=>$id,
    //         );
    //     $remind_count=M('paimai_remind')->where($remind_where)->count();

    // #如果已经提醒过
    //     if(!empty($remind_count)){
    //         $data['status']=3;
    //         $data['info']="您已经设置过提醒";
    //         exit(json_encode($data));
    //     }

    // #组织数据
    //     $remind_data=array(
    //         'uid'=>$this->uid,//用户ID
    //         'starttime'=>$starttime,//开拍时间
    //         'time'=>time(),
    //         );

    // #查找用户的手机号
    //     $remind_data_mobile=M('member','bsm_','DB_BSM')->where(array('mid'=>$this->uid))->getField("mobile");

    // #如果手机为空,提示绑定
    //     if(empty($remind_data_mobile)){
    //         $data['status']=4;
    //         $data['info']="请绑定您的手机";
    //         exit(json_encode($data));
    //     }else{
    //         $remind_data['phone']=$remind_data_mobile;
    //     }
    //     //商品或专场ID
    //     if($style==1){
    //         //商品ID
    //         $remind_data['gid']=$id;
    //     }elseif($style==2){
    //         //专场ID
    //         $remind_data['sid']=$id;
    //     }
    // #进行入库
    //     if(!M('PaimaiRemind')->data($remind_data)->add()){
    //         $data['status']=5;
    //         $data['info']="提醒失败,请刷新页面重新尝试";
    //         exit(json_encode($data));
    //     }
    //     //OK
    //     $data['status']=1;
    //     $data['info']="提醒成功";
    //     exit(json_encode($data));
    }
    /*
     对登录用户设置提醒
    */
    public function unlogin_remind() {
    #接受数据
        //设置提醒手机号码
        $phone = I('post.mobile');
        if(empty($phone)) {
            $this->ajaxReturn(array('status' => 0), 'JSON');
        }
        $id=I('id',0,'intval');
    #判断是否设置过提醒
        #where 
        $remind_where = array(
            'phone' => $phone,
            'gid' => $id
            );
        $remind_record = M('paimai_remind')->where($remind_where)->select();
        if(!empty($remind_record)){
            $this->ajaxReturn(array('status'=>2, 'info'=>'您已经设置过提醒！'), 'JSON');
        }

        //商品开始时间
        $startime = I('starttime') - 1800;//提前半小时提醒
        //待定
        $style = I('style');
        //组织数据
        $data = array(
            'phone' => $phone,
            'gid' => $id,
            'uid' => $this->uid,
            'time' => time(),
            'starttime' => $startime
            );
        //入库
        if(M('paimai_remind')->add($data)){
            $this->ajaxReturn(array('status'=>1), 'JSON');
        } else {
            $this->ajaxReturn(array('status'=>0), 'JSON');
        }

     }
    /*
    后台管理员预览功能
    根据管理员id判断,展示页面还是用原来的商品详情页面
    */
    public function goodspreview(){

        //管理员uid
        if(empty($_SESSION['admin_auth']['uid']))$this->error("你请求的页面不存在");
        
        // 接受参数
        $goods_id = I('get.id', '0', 'intval');
        if ($goods_id == 0) $this->error('此信息不存在或已经删除！');
        
    #根据id查询商品
        $goods_where=array(
            'goods_id'=>$goods_id,
            /*'goods_isshow'=>1,*/
        );
        $GoodsObj = M('PaimaiGoods');
        $Goods = $GoodsObj->where($goods_where)->find();

        
        //如果商品不存在报错
        empty($Goods) AND $this->error('此信息不存在或已经删除！');
        //收藏数
        $this->xpcount = M('paimai_collect')->count();
        //报名人数
        $this->xpcounts = M('paimai_remind')->count();
        //更改拍品点击量
        $GoodsObj->where(array('goods_id' => $goods_id))->setInc("goods_hits"); 
        //获得图片,判断是不是第三方平台导过来的数据
        if($Goods['third_platform']){
            $third = 1;
        }else{
            $third = 0;
        }
        //商品首页展示图
        $index_img=str_replace("..",'',img($Goods['index_img']));
        //商品缩略图
        $Goods["thumb"] = D('Content/Document')->getPic($Goods['recordid'], 'thumb',$Goods["goods_id"]);
        $this->index_img=empty($index_img)?$Goods['thumb']:$index_img;
        $this->goods_thumb=$Goods['thumb'];

        //商品细节图
        $Goods["pics"] = D('Content/Document')->getPic($Goods['recordid'], 'image',$Goods["goods_id"],5,$third);
        $this->assign("goodspics", $Goods['pics']);
    
     #出价记录 如果出价记录不等于0,则查询出价记录
        
        if ($Goods['goods_bidtimes']) {
            $this->bidrecord = M("PaimaiBidrecord")->where(array('bidrecord_goodsid' => $goods_id))->order("bidrecord_id desc")->select();
        }
        

      #商品属性,yishu_paimai_attribute 和yishu_paimai_attribute进行连接
        $GoodsAttr = M("PaimaiGoodsattr")->join("yishu_paimai_attribute on yishu_paimai_goodsattr.goodsattr_attrid=yishu_paimai_attribute.attr_id")->field("attr_name,goodsattr_value")->where(array('goodsattr_goodsid' => $goods_id))->select();
        //v($GoodsAttr);
        foreach($GoodsAttr as $k=>$v){
            $GoodsAttr[$k]['goodsattr_value']=M('PaimaiGoodsattr')->where(array("goodsattr_id"=>$v['goodsattr_value']))->getField("goodsattr_value");
        }
        $this->assign("GoodsAttr",$GoodsAttr);

    #面包屑导航
        //获得专场
        $this->Special=M("PaimaiSpecial")->field("special_id,special_name")->where("special_id=".$Goods['goods_specialid'])->find();
        //获得商品分类的名字用于面包屑导航
        $this->GoodsCat = M("PaimaiCategory")->field("cat_name,cat_spell")->where(array('cat_id' => $Goods['goods_catid']))->find();
        //p($Goods);
    #参拍人数
        //ajax已经搞定;

    #评论分页
        //评论及评论分页
        $p=I("p",1,"intval");
        //每页显示的数量
        $prePage = 5;
        $field = array(
            "*"
        );
        $list = M("PaimaiComment")->where(array("comment_goodsid" => $goods_id))->field($field)->page($p . ',' . $prePage)->order("comment_id desc")->select();
        $this->assign("comment", $list);
        $count = M("PaimaiComment")->where(array("comment_goodsid" => $goods_id))->field($field)->order("comment_id desc")->select();
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

    #感兴趣的产品  本分类下的商品,不包含自己
        $likegoods_field = array(
            'goods_id',
            'goods_name',
            'goods_nowprice',
            "recordid",
        );
        /*$likegoods_where=array(
            'goods_catid'=>$Goods['goods_catid'],
            'goods_id'=>array('not in', "$goods_id"),
        );*/
        $likegoods_where=array(
            'goods_specialid'=>$Goods['goods_specialid'],
            'goods_id'=>array('not in', "$goods_id"),
            'goods_isshow'=>1,
        );
        $LikeGoods = M('PaimaiGoods')->field($likegoods_field)->where($likegoods_where)->order("goods_id desc")->limit(5)->select();
        foreach ($LikeGoods as $k => $v) {
            if($v['third_platform']){
                $third = 1;
            }else{
                $third = 0;
            }
            $LikeGoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v["goods_id"],100,$third);
        }
        $this->assign("LikeGoods",$LikeGoods);

    #对介绍编辑器内容进行转码
        if(!$Goods["third_platform"]){ 
            $goods['goods_intro'] = html_entity_decode($Goods['goods_intro'], ENT_QUOTES, 'UTF-8');
            $this->goods_intro=$goods['goods_intro'];
        }else{
            if($Goods['third_platform']){
                $third = 1;
            }else{
                $third = 0;
            }
            $pic_arr = D('Content/Document')->getPic($Goods['recordid'], 'image',$Goods["goods_id"],100,$third);
            $pic_str = '';
            foreach($pic_arr as $val){
                $pic_str .= '<img alt="" src="'.$val.'" data-bd-imgshare-binded="1">';
            }
            $goods['goods_intro'] = strip_tags(html_entity_decode($Goods['goods_intro'], ENT_QUOTES, 'UTF-8')).$pic_str;
            //print_r($goods['goods_intro']);
            $this->goods_intro=$goods['goods_intro'];
        }
        

        $this->shijian=time();
        if($Goods['goods_endtime']<time()){
            //这个是已经结束
            $Goods['tag']=2;
        }elseif($Goods['goods_starttime']>time()){
            //这个是还没开始
            $Goods['tag']=0;
        }else{
            //这个是正在进行
            $Goods['tag']=1;
        }
        if($Goods['goods_everypricestyle']==1){
            $Goods['goods_everyprice']=format_money(geteveryprice($Goods['goods_nowprice']));
        }
        $this->assign("goods", $Goods);
        $this->display('Front:goodspreview');
    }

	//代理出价设置
	public function set_agent_biding(){
	    if(!IS_AJAX){
		    $this->error('此页面不存在');
		}
		//获取商品id和代理出价
		$goods_id = I('goods_id', 0, 'intval');
		$agent_price = I('agent_price', 0, 'floatval');
		if(!$goods_id || !$agent_price){
			$data = array('error' => 1, 'info' => '设置失败，请重新尝试');
            $this->ajaxReturn($data);
		}

		//登录判断
		$uid = $this->uid;
		if(!$uid){
           $data = array('error' => 2, 'info' => '您还没有登录');
		   $this->ajaxReturn($data);
        }

		//商品信息
		$goods_where = array(
			'goods_id' => $goods_id,
		    'goods_isshow' => 1
		);
		$goods_field = array(
		    'goods_id',
			'goods_nowprice',
			'goods_starttime',
			'goods_endtime',
			'goods_bidtimes'
		);
		$goods_info = M('PaimaiGoods')->field($goods_field)->where($goods_where)->find();
		if(!$goods_info){
		    $data = array('error' => 3, 'info' => '商品不存在');
			$this->ajaxReturn($data);
		}
		if($goods_info['goods_starttime'] > time()){
		    $data = array('error' => 4, 'info' => '商品未开拍');
			$this->ajaxReturn($data);
		}
		if($goods_info['goods_endtime'] < time()){
		    $data = array('error' => 5, 'info' => '拍卖已结束');
			$this->ajaxReturn($data);
		}
		if($goods_info['goods_bidtimes'] != 0 && $goods_info['goods_nowprice'] >= $agent_price){
            $data = array('error' => 6, 'info' => '商品已有最新出价，请重新设置');
			$this->ajaxReturn($data);
        }

		//设置代理价入库
		$agent_data = array(
		    'autoagent_price' => $agent_price,
			'autoagent_goodsid' => $goods_id,
			'autoagent_uid' => $uid,
			'autoagent_createtime' => time()
		);

		$m_agent = M('PaimaiAutoagent');

		//是否已设置过代理价
		$agent_id = $m_agent->where(array('autoagent_uid' => $uid, 'autoagent_goodsid' => $goods_id))->getField('autoagent_id');
		if($agent_id){
		    $agent_data['autoagent_id'] = $agent_id;
			if($m_agent->save($agent_data)){
				$data = array('error' => 0, 'info' => '代理价更新成功');
				$this->ajaxReturn($data);
			}else{
			    $data = array('error' => 1, 'info' => '代理价更新失败');
				$this->ajaxReturn($data);
			}
		}else{
		    if($m_agent->create($agent_data)){
				if($m_agent->add()){
					$data = array('error' => 0, 'info' => '代理价设置成功');
					$this->ajaxReturn($data);
				}else{
					$data = array('error' => 1, 'info' => '设置失败');
					$this->ajaxReturn($data);
				}
			}else{
				$data = array('error' => 1, 'info' => '设置失败');
				$this->ajaxReturn($data);
			}
		}
	}

    /*ajax传递过来三个参数:
    1.商品id  id 
    2.用户最新的出价价位 newbidprice,
    3用户本次的加价幅度 useraddprice
     * 用户点击出价按键
     * 业务逻辑:
     * 从前台传进来1.商品id,2,商品价格,3口号,
     * 1先验证用户是否登录
     * 2,是否有保证金与当前商品的保证金比较
     * 3从数据库中得到本商品的最新记录,的uid与当前用户的session中的uid进行对比,
     *
     */
    protected $errno=0;
    protected $error=array(
            1=>"立即登录，抢拍宝贝！",
            2=>"此商品不存在",
            3=>"此商品已经被拍走",
            4=>"你的账户余额低于本商品所要求的最低余额,账户余额不足,是否现在去充值?",
            5=>"你已经是出价最高的用户,请稍后再进行尝试",
            6=>"由于现价已经为...保证金已经为...你还需交纳...保证金",
            7=>"出现异常,请刷新页面重新尝试,提示代码:7",//第一次拍扣除表bsm_member保证金失败
            8=>"出现异常,请刷新页面重新尝试,提示代码:8",//第一次拍向表recharge中生成订单失败
            9=>"出价成功",
            10=>"拍品现在的价格已经大于你的出价,请重新出价",
            11=>"本拍品还没开拍,请稍后尝试",
            120=>"网页正在开发修复中请稍后尝试",
            110=>"出价失败,请重新尝试",
        );
    public function bid()
    {
    
        if (!IS_AJAX) $this->error("此页面不存在");

        //商品id
        $goods_id = I("get.id", 0, "intval");
        //p($goods_id);
        //此用户的出价
        $newbidprice = I("get.newbidprice", 0, "floatval");
        //获得用户的加价幅度
        //$useraddprice = I("get.useraddprice", 0, "floatval");
       
    #调试模式:如果在调试则把$tiaoshi改为1
        $tiaoshi=0;
        if($tiaoshi==1){
            $this->errno=120;
            $data['errno'] =$this->errno;
            $data['errorinfo']=$this->error[$this->errno];
            echo json_encode($data);
            exit;
        }


    #判断传递过来的参数  
        //如果下列情况有一个为0,则返回状态0
        
        if ($goods_id == 0 || $newbidprice == 0) {
            $this->errno=110;
            $data['errno'] =$this->errno;
            $data['errorinfo']=$this->error[$this->errno];
            echo json_encode($data);
            exit;
        }
        $uid = $this->uid;
        $where["bidrecord_goodsid"] = $goods_id;

    #判断是否登录如果没有登录返回
        if (!$uid) {
            $this->errno=1;
            $data['errno'] = $this->errno;
            $data['errorinfo']=$this->error[$this->errno];
            echo json_encode($data); //"你还没有登录";
            exit;
        }

    #判断本商品是否存在
        //得到本商品的信息
        $goods_check_where['goods_isshow'] = 1;
        $goods_check_where['goods_id'] = $goods_id;
        $goods_check = M('PaimaiGoods')->field('goods_id,goods_nowprice,goods_needmoney,goods_endtime,goods_bidtimes,goods_everypricestyle,goods_everyprice,goods_starttime, goods_cost')->where($goods_check_where)->find();
        //判断本商品是否开拍
        if($goods_check['goods_starttime']>time()){
            $this->errno=11;
            $data['errno']=$this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"商品已经有最新出价";
            exit;
        }
        //对重新出价修改取出的保证金
        $goods_check['goods_needmoney']=getneedmoney($newbidprice);
    #拍卖次数不为0且不同用户可能停留在同一页面时候进行竞拍
        if($goods_check['goods_bidtimes']!=0 && $goods_check['goods_nowprice']>=$newbidprice){
            $this->errno=10;
            $data['errno']=$this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"商品已经有最新出价";
            exit;
        }

    #判断商品是否存在
        if (empty($goods_check)) {
            $this->errno=2;
            $data['errno']=$this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"商品不存在";
            exit;
        }

    #判断商品是否过期或已经被拍走
        if($goods_check['goods_endtime']<time()){
            $this->errno=3;
            $data['errno']=$this->errno;
            $data['errorinfo']=$this->error[$this->errno];
            echo json_encode($data); //"商品已经过期或者已经被拍走";
            exit;
        }
    
        

    #得到用户账户金额
        //提现审核动结
        //$moneys = M("paimai_deposit")->where(array('uid'=>$uid,'status'=>0))->sum('money');
        //$member_cash=M("member","bsm_","DB_BSM")->where(array('mid'=>$uid))->getField('amount');
        //得到用户可用金额+注册返现的
        //注册的返现金额
        $register_amoutn=return_amount($uid);
        $member_cash=$this->user_amount+$register_amoutn;

        $bidrecord = M("PaimaiBidrecord");

    #检查用户之前有没有拍过此商品
        $bidcheck_where['bidrecord_goodsid'] = $goods_id;
        $bidcheck_where['bidrecord_uid'] = $uid;
        $userbidcheck = $bidrecord->field("bidrecord_goodsneedmoney")->where($bidcheck_where)->order("bidrecord_id desc")->find();

        if($goods_check['goods_needmoney']>=return_amount($uid)){
            $goods_check['goods_needmoney']=$goods_check['goods_needmoney']-return_amount($uid);
        }
        //p($goods_check['goods_needmoney']);
        //如果用户没有拍过
        if (empty($userbidcheck)) {

            //查看用户的保证金是否大于本商品的保证金
            //如果保证金不足,
            if ($member_cash < $goods_check['goods_needmoney']) {
                $this->errno=4;
                $data['errno'] = $this->errno;
                //商品保证金
                $data['goods_needmoney'] = $goods_check['goods_needmoney'];
                //返回用户的账户余额
                $data['user_amount'] = $member_cash;
                $data['recharge_money']=$data['goods_needmoney']-$data['user_amount'];
                $data['errorinfo']="您的账户余额为".$data['user_amount']."元,小于拍本拍品的最小金额".$data['goods_needmoney']."元,你还需要充值".format_money($data['recharge_money'])."元,现在是否去充值?";
                echo json_encode($data); //"你的保证金不足,请及时充值";
                exit;
            } else {
            //如果保证金充足则扣除保证金
                //用户第一次参拍,扣除的保证金为除了注册优惠券之外的金额
                //$goods_check['goods_needmoney']=$goods_check['goods_needmoney']-return_amount($uid);
                if(!$this->downneedmoney($uid,$goods_id,$goods_check['goods_needmoney'],4)){
                    $data['errno']=$this->errno;
                    $data['errorinfo']=$this->error[$this->errno];
                    echo json_encode($data); //"";
                    exit;
                }
                //跳出,继续走

            }
        }else{
        //如果用户之前拍过此商品再次参拍

            //得到数据库中最新的一条记录的uid和session中的mid进行对比
            $NewbidrecordObj_where=array(
                'bidrecord_goodsid'=>$goods_id,
                );
            $NewbidrecordObj=$bidrecord->field('bidrecord_uid,bidrecord_price')->where($NewbidrecordObj_where)->order("bidrecord_id desc")->find();

    #判断现在的最高出价是不是此用户
            /*
            (注释掉的代码作用是禁止用户连续出价)
            //注释start
            if($uid==$NewbidrecordObj['bidrecord_uid']){
                $this->errno=5;
                $data['errno']=$this->errno;
                $data['errorinfo']=$this->error[$this->errno];
                echo json_encode($data); //"你已经是出价最高的用户,请稍后出价";
                exit;
            //注释end
            }*/
            //如果不是最高出价者则验证出价价格和数据库中的最高价格进行对比,判断在这个期间是否已经有人再次出价了
                //验证现在的商品保证金和用户之前的保证金做对比
                
    #如果保证金与之前拍缴纳的不同则,再次扣除需要的保证金
            $againrecharge=$goods_check['goods_needmoney']-$userbidcheck['bidrecord_goodsneedmoney'];

            if($againrecharge>0){//需要再次充值

    #再次判断账户余额,给出不同提示
                if($member_cash<$againrecharge){//如果用户账户余额不足
                    $this->errno=6;
                    $data['errno']=$this->errno;

                    //商品现价
                    $data['goods_nowprice']=$goods_check['goods_nowprice'];

                    //商品现在需要的保证金
                    $data['goods_needmoney']=$goods_check['goods_needmoney'];

                    //用户之前交纳的保证金
                    $data['bidrecord_goodsneedmoney']=$userbidcheck['bidrecord_goodsneedmoney'];

                    //用户还需要交纳的保证金
                    $data['needmoney']=$againrecharge;
                    $data['errorinfo']="由于本拍品现价已经为".$goods_check['goods_nowprice']."元,你之前交纳的本拍品的保证金为".$userbidcheck['bidrecord_goodsneedmoney']."元小于本拍品现在所需要的保证金".$goods_check['goods_needmoney']."元,你还需要交纳".format_money($data['needmoney'])."元保证金,是否现在去充值?";
                    echo json_encode($data); //"还需要交纳保证金";
                    exit;
                }else{//用户资金充足则扣除

                    if(!$this->downneedmoney($uid,$goods_id,$againrecharge,5)){
                        $data['errno']=$this->errno;
                        $data['errorinfo']=$this->error[$this->errno];
                        echo json_encode($data); //"";
                        exit;
                    }
                }
            }
            //走到这一步则保证金都正常跳出
            
        }



    #到这里说明条件都符合,进行入库操作

        //如果保证金扣除成功,组织数据入yishu_paimai_bidrecord库
        $Bidrecord_data=array(
            'bidrecord_uid'=>$uid,
            'bidrecord_price'=>$newbidprice,
            'bidrecord_goodsid'=>$goods_id,
            'bidrecord_ip'=>get_client_ip(),
            'bidrecord_time'=>time(),
            'bidrecord_goodsneedmoney'=>$goods_check['goods_needmoney'],
            );
        if ($bidrecord->data($Bidrecord_data)->add()) {
            //注册返现后把这个券的状态更改下,为已经使用
            //change_status_false($uid);(20150213chen)
    #如果成功则重新修改此时商品的保证金,现价,加价幅度


            //得到最新加价幅度然后入库
            if($goods_check['goods_everypricestyle']==1){
                $goods_everyprice=geteveryprice($newbidprice);
            }else{
                $goods_everyprice=$goods_check['goods_everyprice'];
            }
            $ChargeGoodsObj = D("PaimaiGoods");

    #组织数据更改商品表
            //where条件
            $ChargeGoodsObj_where=array(
                'goods_id'=>$goods_id,
                );

            //data数组
            $ChargeGoodsObj_data=array(
                'goods_needmoney'=>$goods_check['goods_needmoney'],//保证金
                'goods_everyprice'=>$goods_everyprice,//加价幅度
                'goods_nowprice'=>$newbidprice,//重新更改现价
                'goods_bidtimes'=>$goods_check['goods_bidtimes']+1,//次数+1
                );

            //更改保证金和加价幅度
            $ChargeGoodsObj->where($ChargeGoodsObj_where)->save($ChargeGoodsObj_data);
			
			//代理出价
			$agent_info = M('PaimaiAutoagent')->field('autoagent_price, autoagent_uid, autoagent_createtime')->order('autoagent_price desc, autoagent_createtime asc')->where(array('autoagent_uid' => array('NEQ', $uid), 'autoagent_goodsid' => $goods_id, 'autoagent_price' => array('EGT', $newbidprice + $goods_everyprice)))->select();
			//成本价
			$g_cost = $goods_check['goods_cost'];
			$rnd_uname = '';
			$agent_biding_price = 0;
			if(count($agent_info) > 0){
				if(count($agent_info) == 1){
					if($g_cost > $agent_info[0]['autoagent_price']){
					    $tmp_price = $g_cost;
						$rnd_uname = $this->getAutoBidUname($goods_id);//'YS_'.sprintf("%06d", rand(1,999999));
					}else{
					    $tmp_price = $agent_info[0]['autoagent_price'];
					}
					//只有一人设置代理价
					$agent_biding_price = $tmp_price <= ($newbidprice + $goods_everyprice) ? $tmp_price : ($newbidprice + $goods_everyprice);
				}else{
					//多人设置代理价
					$price_arr = array($g_cost, $agent_info[0]['autoagent_price'], $agent_info[1]['autoagent_price']);
					rsort($price_arr, SORT_NUMERIC);
					$tmp_1st_price = $price_arr[0];
					$tmp_2nd_price = $price_arr[1];
					if($g_cost > $agent_info[0]['autoagent_price']){
					    $rnd_uname = $this->getAutoBidUname($goods_id);//'YS_'.sprintf("%06d", rand(1,999999));
					}
					if(($tmp_1st_price <= ($newbidprice + $goods_everyprice)) || ($tmp_1st_price <= ($tmp_2nd_price + $goods_everyprice))){
					    $agent_biding_price = $tmp_1st_price;
					}else{
					    $agent_biding_price = $tmp_2nd_price + $goods_everyprice;
					}
				}
			}else{
				if($g_cost > $newbidprice){
					$agent_biding_price = $newbidprice + $goods_everyprice;
					$rnd_uname = $this->getAutoBidUname($goods_id);//'YS_'.sprintf("%06d", rand(1,999999));
				}
			}
			if($agent_biding_price){
				$agent_bidrecord_data = array(
					'bidrecord_uid' => $agent_info[0]['autoagent_uid'],
					'bidrecord_price' => $agent_biding_price,
					'bidrecord_goodsid' => $goods_id,
					'bidrecord_time' => time(),
					'bidrecord_goodsneedmoney' => getneedmoney($agent_biding_price),
					'bidrecord_type' => 1
				);
				if($rnd_uname){
				    $agent_bidrecord_data['bidrecord_uid'] = 0;
					$agent_bidrecord_data['bidrecord_uname'] = $rnd_uname;
				}
				if($bidrecord->data($agent_bidrecord_data)->add()){
					$goods_everyprice = $goods_check['goods_everypricestyle'] == 1 ? geteveryprice($agent_biding_price) : $goods_check['goods_everyprice'];
					$goods_data = array(
						'goods_needmoney' => $agent_bidrecord_data['bidrecord_goodsneedmoney'],
						'goods_everyprice' => $goods_everyprice,
						'goods_nowprice' => $agent_biding_price,
						'goods_bidtimes' => $goods_check['goods_bidtimes'] + 2
					);
					$ChargeGoodsObj->where(array('goods_id' => $goods_id))->save($goods_data);
				}
			}
            $this->errno=9;//成功
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"出价成功";
            exit;
        }else{
            $this->errno=110;
            $data['errno'] = $this->errno;
            $data['errorinfo'] = $this->error[$this->errno];
            echo json_encode($data); //"出价失败";
            exit;
        }
    }
    /*
    扣除保证金生成订单
    */
    public function downneedmoney($uid,$goods_id,$needmoney,$style=4){
        
        //在保证金充值表中生成一条扣除保证金的记录
        $deduct_needmoney_data=array(
            'recharge_sn'=>D("PaimaiGoods")->CreateRechargeSn(),
            'recharge_uid'=>$uid,
            'recharge_money'=>-$needmoney,
            'recharge_createtime'=>time(),
            'recharge_style'=>$style,//4为拍商品时候冻结的保证金,5为随着价格上涨补的保证金
            'recharge_ip'=>get_client_ip(),
            'recharge_returngid'=>$goods_id,
            );

        if ($recharge_id=M("PaimaiRecharge")->data($deduct_needmoney_data)->add()) {
            return M("PaimaiRecharge")->where("recharge_id=$recharge_id")->setField("recharge_status","2");
            /*$sql = "update bsm.bsm_member set amount=amount-$needmoney,frozen=frozen+$needmoney where mid=$uid";
            //如果扣除bsm_member表中的金额,加上冻结的金额
            if (!M()->db(5, 'DB_BSM')->execute($sql)) {
                $this->errno=7;
                return false;
            }else{
            //如果没有出错修改刚才保证金生成订单状态为2,支付成功就向下走
                return M("PaimaiRecharge")->where("recharge_id=$recharge_id")->setField("recharge_status","2");
            }  */
        } else {
            $this->errno=8;
            return false;
        }
    }
    /*
    记录用户点击状态,用户跟踪,不返回值,也不进行报错判断,即可ajax也可php函数调用
    */
    public function user_record($goods_id=0,$status=0,$uid=0){
        //商品id
        $goods_id=$goods_id?$goods_id:I('gid',0,'intval');
        //状态码
        $status=$status?$status:I('status',0,'intval');
        //用户
        $uid=$uid?$uid:I('uid',0,'intval');

        //如果有一个不为0则返回false
        if($goods_id==0||$status==0 || $uid==0) return false;

        // 商品id 和 状态码 和 用户uid 都不为0,则走下面
        $bidstatus_data=array(
            'bidstatus_status'=>$status,
            'bidstatus_gid'=>$goods_id,
            'bidstatus_uid'=>$uid,
            'bidstatus_time'=>time(),
            );
        v($bidstatus_data);
        M('PaimaiBidstatus')->add($bidstatus_data);
    }
 
    
    /*
     * 拍卖模块全局进行描描商品到期时间,和提示每个用户已经成功拍得哪个商品
     */
    public function scan_user_success_goods()
    {
		 //对入口数据进行验证过滤
        if (!IS_AJAX) $this->error("此页面不存在:提示代码:10");
        $goodsObj = M("PaimaiGoods");
        
		$goodsstatus_field=array(
			'goods_id',
			'goods_name',
			'goods_nowprice',
			'goods_successid',
			'recordid',
		);
        //下面最好对where条件进行优化
		$goodsstatus_where=array(
			'goods_status'=>2,
			'goods_endtime'=>array("LT",time()),
		);
		$goodsstatus_2Arr = $goodsObj->field($goodsstatus_field)->where($goodsstatus_where)->select();
		
        $uid = $this->uid;;
        $maxbidmangoods = array();
        foreach ($goodsstatus_2Arr as $k => $v) {
            //如果状态为2的商品的最后竞拍者id和当前用户session中的uid一样,则把本条记录给$maxbidmangoods数组
            if ($v['goods_successid'] == $uid) {
                $maxbidmangoods[] = $goodsstatus_2Arr[$k];
            }
        }
        //p($maxbidmangoods);
        foreach ($maxbidmangoods as $k => $v) {
			if($v['third_platform']){
				$third = 1;
			}else{
				$third = 0;
			}
            $maxbidmangoods[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v["goods_id"],5,$third);
        }
		
        //返回商品名字,返回商品最高价格和图片
        echo json_encode($maxbidmangoods);
        exit;
    }

    /*
     * 创建唯一充值单号,这里可以写一个model直接调用 model中的这个方法最好
     */
    public function createrechargesn()
    {
        //创建唯一订单号
        $orderinfo = M("PaimaiRecharge");
        $info_sn = 'CZ' . date("Ymd") . mt_rand(10000, 99999);
        return $orderinfo->where("info_sn=$info_sn")->count() ? $this->createordersn() : $info_sn;
    }

    /*
     * 验证码
     */
    public function verify()
    {
        $config = array(
            'fontSize' => 15, // 验证码字体大小
            'useImgBg' => false,
            'imageW' => 100,
            'imageH' => 30,
            'length' => 4, // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }
#ajax
    /*
     * 传入商品goods_id
     * 动态得到商品信息:价格,出价次数
     * 商品详情页面动态变化商品价格和出价次数
     */
    public function getGoodsinfo()
    {
        if(!IS_AJAX)$this->error("你请求的页面不存在");
        $goods_id = I("get.id", 0, "intval");
        $field = array(
            'goods_nowprice',
            'goods_bidtimes',
            'goods_everyprice',
            'goods_needmoney',
        );

        $goods = M("PaimaiGoods")->field($field)->where("goods_id=" . $goods_id)->find();
        echo json_encode($goods);
    }

    /*
     * 会话商品goods_id
     * 返回拍卖人信息昵称,价格,时间,口号,状态,ip
     */
    public function getbidrecordinfo()
    {
        if(!IS_AJAX)$this->error("你请求的页面不存在");
        $goods_id = I("get.id", 0, "intval");
        $field = array(
            "bidrecord_id",
            'bidrecord_uname',
            'bidrecord_uid',
            'bidrecord_price',
            'bidrecord_slogan',
            'bidrecord_time',
			'bidrecord_type'
        );
        $bidrecord = M("PaimaiBidrecord")->field($field)->where("bidrecord_goodsid=" . $goods_id)->order("bidrecord_id desc")->limit(8)->select();
        

        //隐藏ip,换算时间
        foreach ($bidrecord as $k => $v) {
            if($v['bidrecord_uid']==0){
                $username=hideusername($v['bidrecord_uname']);
            }else{
                $username=getUsername($v['bidrecord_uid']);
            }
            $bidrecord[$k]['bidrecord_uname']=$username;
            //$bidrecord[$k]['bidrecord_time'] = date("y/m/d H:i:s", $v['bidrecord_time']);
			$bidrecord[$k]['bidrecord_time'] = date("y/m/d", $v['bidrecord_time']);
        }
        //p($bidrecord);
        echo json_encode($bidrecord);
    }
	//收藏数
	public function cs(){
		if(IS_AJAX){
			$id = I('post.id');
			if(! session('mid')){
				$data['status'] = 2;
				$data['info'] = '请先登录再操作';
				$this->ajaxReturn($data,'JSON');
			}
			if($id){
				$gid = M('paimai_collect')->where(array('collect_goodsid'=>$id,'collect_uid'=>session('mid')))->getField('collect_id');
				if($gid){
					$data['status'] = 0;
					$data['info'] = '您已收藏过该拍品';
					$this->ajaxReturn($data,'JSON');
				}else{
					$datas = array(
						'collect_goodsid' => $id,
						'collect_uid' => session('mid'),
						'collect_time' => time()
					);
					if(M('paimai_collect')->add($datas)){
						$data['status'] = 1;
						$data['info'] = '收藏成功';
						$this->ajaxReturn($data,'JSON');
					}else{
						$data['status'] = 0;
						$data['info'] = '收藏失败';
						$this->ajaxReturn($data,'JSON');
					}
				}
			}else{
				$data['status'] = 0;
				$data['info'] = '收藏失败...';
				$this->ajaxReturn($data,'JSON');
			}
		}
	}
	//开拍提醒
	public function tx(){
		if(IS_AJAX){
			if(! session('mid')){
				$data['status'] = 0;
				$data['info'] = '请先登录再操作';
				$this->ajaxReturn($data,'JSON');
			}
			$id = I('post.id');
			$gdsid = I('post.gdsid');
			$remind_style = I('post.remind_style');
			$remind_content = I('post.remind_content');
			if(! $id || ! $gdsid || ! $remind_style || ! $remind_content){
				$data['status'] = 0;
				$data['info'] = '非法操作...';
				$this->ajaxReturn($data,'JSON');
			}
			if($remind_style == 1){
				if(! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",$remind_content)){  
					$data['status'] = 0;
					$data['info'] = '邮箱格式不正确';
					$this->ajaxReturn($data,'JSON');
				}
				$gid = M('paimai_remind')->where(array('gid'=>$id,'uid'=>session('mid'),'email'=>$remind_content))->getField('id');
				if($gid){
					$data['status'] = 0;
					$data['info'] = '您已设置过...';
					$this->ajaxReturn($data,'JSON');
				}
				$datas = array(
					'uid' => session('mid'),
					'gid' => $id,
					'starttime' => $gdsid,
					'time' => time(),
					'email' => $remind_content,
				);
			}else if($remind_style == 2){
				if(! preg_match("/^[1]{1}+[3-9]{1}+[0-9]{9}$/",$remind_content)){  
					$data['status'] = 0;
					$data['info'] = '手机格式不正确';
					$this->ajaxReturn($data,'JSON');
				}
				$gid = M('paimai_remind')->where(array('gid'=>$id,'uid'=>session('mid'),'phone'=>$remind_content))->getField('id');
				if($gid){
					$data['status'] = 0;
					$data['info'] = '您已设置过...';
					$this->ajaxReturn($data,'JSON');
				}
				$datas = array(
					'uid' => session('mid'),
					'gid' => $id,
					'starttime' => $gdsid,
					'time' => time(),
					'phone' => $remind_content,
				);
			}
			if(M('paimai_remind')->add($datas)){
				$data['status'] = 1;
				$data['info'] = '设置成功...';
				$this->ajaxReturn($data,'JSON');
			}else{
				$data['status'] = 0;
				$data['info'] = '网络快，请刷新后再试...';
				$this->ajaxReturn($data,'JSON');
			}
		}else{
			$data['status'] = 0;
			$data['info'] = '非法操作...';
			$this->ajaxReturn($data,'JSON');
		}
	}

	//得到拍品的代理出价用户名
	public function getAutoBidUname($goods_id = 0){
	    if(!$goods_id){
		    return false;
		}
		$record_where = array(
		    'bidrecord_uid' => 0,
			'bidrecord_type' => 1,
			'bidrecord_goodsid' => $goods_id
		);
		$uname = M('PaimaiBidrecord')->where($record_where)->getField('bidrecord_uname');
		if(!$uname){
		    return 'YS_'.sprintf("%06d", rand(1,999999));
		}else{
		    return $uname;
		}
	}
}
