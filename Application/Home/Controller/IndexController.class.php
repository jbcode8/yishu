<?php
// +----------------------------------------------------------------------
// | IndexController.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Home\Controller;


class IndexController extends HomeController{
    public function _initialize() {
        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth',$auth);
    }


    public function index(){

        $bsm_prefix = 'bsm_';
        $v9_prefix = 'v9_';
		$yishu_prefix = 'yishu_';
		
		//今日要闻
		//百叶窗要闻
		$news_field = 'news_id, news_name, news_createtime, recordid, news_summary';
		$today_news_byc = M('news_list')->field($news_field)->where(array('news_isshow' => 0, 'news_isdelete' => 0, 'news_arrposid' => 1))->order('news_order, news_id desc')->limit(6)->select();
		foreach ($today_news_byc as $k => $v) {
			$today_news_byc[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}
        $this->assign('today_news_byc',$today_news_byc);

		//文字要闻
		$today_news_normal = M('news_list')->field($news_field)->where(array('news_isshow' => 0, 'news_isdelete' => 0, 'news_arrposid' => 6))->order('news_order, news_id desc')->limit(6)->select();

		$this->assign('today_news_normal',$today_news_normal);

		//行业热点 置顶
		$hot_news_top = M('news_list')->field($news_field)->where(array('news_isshow' => 0, 'news_isdelete' => 0, 'news_recommend' => 1, 'news_type' => 1))->order('news_id desc')->find();
		
		//行业热点
        $hot_news = M('news_list')->field($news_field)->where(array('news_isshow' => 0, 'news_isdelete' => 0, 'news_recommend' => 0, 'news_type' => 1))->order('news_order, news_id desc')->limit(5)->select();

		$this->assign('hot_news_top', $hot_news_top);
		$this->assign('hot_news', $hot_news);
		
		//名家大师
		$famous = M('news_list')->field($news_field)->where(array('news_isshow' => 0, 'news_isdelete' => 0, 'news_type' => 3))->order('news_recommend desc, news_order, news_id desc')->limit(10)->select();
        foreach($famous as $k => $v){
		    $famous[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['news_id']);
		}

		$this->assign('famous', $famous);

		//拍卖资讯
		$paimai_news = M('news_list')->field($news_field)->where(array('news_isshow' => 0, 'news_isdelete' => 0, 'news_type' => 2))->order('news_order, news_id desc')->limit(5)->select();

		$this->assign('paimai_news',$paimai_news);

		//展览时讯
		$zhanlan_news = M()->db(1,'DB_V9')->table($v9_prefix.'news')->field('url,title,description,thumb')->where(array('catid'=>46))->order('updatetime desc')->limit(3)->select();
        
		$this->assign('zhanlan_news',$zhanlan_news);

        //艺术家
		$artists5 = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0 and category=7')->order('listorder desc')->limit(9)->select();
		$this->assign('artists5',$artists5);
		
		$artists7 = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0 and category=2')->order('listorder desc')->limit(9)->select();
		$this->assign('artists7',$artists7);

        //推荐专场
		$Special = D('Paimai/PaimaiSpecial')->getRecommendSpecial(6);
		$this->assign("Special",$Special);

		//今日专场
		$today_Special = D('Paimai/PaimaiSpecial')->getTodaySpecial(5);
		$this->assign("today_Special",$today_Special);

		//金融
		$finance = D('Jinrong/GoodsRelation')->getGoods(3);
		//p($finance);
		$this->assign('finance', $finance);

		//寄售
		$consignment = D('Jishou/JishouGoods')->getIndexGoods();
        $this->assign('consignment', $consignment);

        //在线鉴定
        $identifyOnline = M()->db(2,'DB_BSM')->table($bsm_prefix.'identify_data')->field('`id`,`name`,`thumb`,`createtime`,`hits`,`question`')->where('`isok` > 0')->order('`createtime` DESC')->limit(8)->select();
        $identifyOnlineN = M()->db(2,'DB_BSM')->table($bsm_prefix.'identify_data')->field('`id`,`name`,`thumb`,`createtime`,`hits`,`question`')->where('`isok` = 0')->order('`createtime` DESC')->limit(8)->select();
        $this->assign('identifyOnline',$identifyOnline);
        $this->assign('identifyOnlineN',$identifyOnlineN);

        //问答
        $askSolved = D('Ask/HomeQuestion')->getQuestions(0,1,9);
        $askUnsolved = D('Ask/HomeQuestion')->getQuestions(0,2,9);
        $this->assign('askSolved',$askSolved);
        $this->assign('askUnsolved',$askUnsolved);

        //百科
        $baike = D('Baike/Category')->cateone();
		$baikenew = M('baike_category')->field('cid,pid,short_name,name')->select();
		$baikes = catebaike($baikenew);
        $this->assign('baike',$baikes);

        //古玩城
		//默认商品
		$mall_goods = M('MallGoods')->field('goods_id, goods_name, default_img')->where(array('status' => 2, 'is_delete' => 1))->order(array('create_time' => 'desc'))->limit(9)->select();
		//陶瓷商品
        $mall_goods_tc = M('MallGoods')->field('goods_id, goods_name, default_img')->where(array('status' => 2, 'is_delete' => 1, 'cate_id' => array('IN', '(137,138,139,140,141,142,143)')) )->order(array('create_time' => 'desc'))->limit(9)->select();
        //钱币商品
		$mall_goods_qb = M('MallGoods')->field('goods_id, goods_name, default_img')->where(array('status' => 2, 'is_delete' => 1, 'cate_id' => array('IN', '(423,424,425,426,427,428,429,430,431,432,433,434,435)')) )->order(array('create_time' => 'desc'))->limit(9)->select();
		
		$this->assign('mall_goods',$mall_goods);
		$this->assign('mall_goods_tc',$mall_goods_tc);
        $this->assign('mall_goods_qb',$mall_goods_qb);

        //收藏知识
		$collection_know_pos = D('Document')->getCollectionKnow('',8,1);
		$collection_know_pos5 = D('Document')->getCollectionKnow('玛瑙',8,1);
		$collection_know_pos6 = D('Document')->getCollectionKnow('玉髓',8,1);
        
		$this->assign('collection_know_pos',$collection_know_pos);
        $this->assign('collection_know_pos5',$collection_know_pos5);
        $this->assign('collection_know_pos6',$collection_know_pos6);
        
        //友情链接
        $friend_links = M()->db(1,'DB_V9')->table($v9_prefix.'link')->field('name,url')->where('typeid=0')->order('listorder desc')->limit(100)->select();
        $this->assign('friend_links',$friend_links);
        $this->display('Front:index_sz_new');
    }

    //首页今日开拍异步调用当天所有拍品
    public function getTodayAuction(){
        $offset = I('get.offset')?I('get.offset'):0;
        $auctionOnlineToday = D('Auction/HomeAuction')->auctionOnline($offset,1);
        $auctionOnlineToday[0]['time'] = date('Y-m-d H:i:s');
        $auctionOnlineToday[0]['goods_endtime'] = date('Y-m-d H:i:s',$auctionOnlineToday[0]['goods_endtime']);
        echo json_encode($auctionOnlineToday[0]);
		exit;
    }
    //选择城市
    public function city(){
        session('city_name', null);
        session('city_name', I('get.city','上海','strip_tags'), 'expire', 60*60*24*100);
    }

	/*加盟网提交表单*/
	public function jiameng_contactsave () {
		if(!IS_AJAX){
			echo 'error';
			return false;
		}
		//接收数据
		$name = I('post.name');
		$email = I('post.email');
		$tel = I('post.tel');
		$content = I('post.content');


		//组织插入数据库数据
		$data = array(
			'name' => $name,
			'email' => $email,
			'phone' => $tel,
			'city' => $content,
			'subtime' => time()
			);

		//插入数据库操作
		//M('jiameng_contact')->add($data)
		if(M('jiameng_contact')->add($data)){
			$this->ajaxReturn(array('status' => 'y','statusText'=>'提交成功！'),'JSON');
		}
	}

}