<?php
// +----------------------------------------------------------------------
// | IndexController.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Home\Controller;


class IndexController extends HomeController{
    public function _initialize() {
        //parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth',$auth);
    }


    public function index(){
        //exit("test");
        //print_r($_COOKIE);
        //print_r($_SESSION);
        //D('Test')->add(array('id'=>1,'name'=>'luyanhua'));
        //藏品频道
        $arrSubSite = array(
            array('name'=>'国画', 'url'=>'http://cang.yishu.com/t60', 'sel'=>1),
            array('name'=>'书法', 'url'=>'http://cang.yishu.com/t62', 'sel'=>0),
            array('name'=>'油画', 'url'=>'http://cang.yishu.com/t61', 'sel'=>0),
            array('name'=>'玉器', 'url'=>'http://cang.yishu.com/t65', 'sel'=>0),
            array('name'=>'瓷器', 'url'=>'http://cang.yishu.com/t64', 'sel'=>0),
            array('name'=>'木器', 'url'=>'http://cang.yishu.com/t63', 'sel'=>0),
            array('name'=>'寿山石', 'url'=>'http://cang.yishu.com/t131', 'sel'=>0),
        );
        $this->assign('arrSubSite',$arrSubSite);

        //导航分类
        $category = D('Category')->getAll();
        $this->assign('category',$category);

        $bsm_prefix = 'bsm_';
        $v9_prefix = 'v9_';

		//拍卖资讯 展览时讯
		$paimai_news = M()->db(1,'DB_V9')->table($v9_prefix.'news')->field('url,title,description')->where(array('catid'=>40))->order('updatetime desc')->limit(5)->select();
		$zhanlan_news = M()->db(1,'DB_V9')->table($v9_prefix.'news')->field('url,title,description')->where(array('catid'=>46))->order('updatetime desc')->limit(5)->select();
        $this->assign('paimai_news',$paimai_news);
		$this->assign('zhanlan_news',$zhanlan_news);

        //艺术家分类
        $artist_category = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist_category')->order('id desc')->select();
        //$artist_category = D('Artist/Category')->getCategory();
        $this->assign('artist_category',$artist_category);

        //艺术家
        $artists = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0')->order('listorder desc')->limit(10)->select();
        //$artists = D('Artist/Library')->getLibrary(22,'status > 0','id desc');
        $this->assign('artists',$artists);

		$artists1 = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0 and category=13')->order('listorder desc')->limit(10)->select();
		$this->assign('artists1',$artists1);
		$artists2 = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0 and category=12')->order('listorder desc')->limit(10)->select();
		$this->assign('artists2',$artists2);
		$artists3 = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0 and category=11')->order('listorder desc')->limit(10)->select();
		$this->assign('artists3',$artists3);
		$artists4 = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0 and category=8')->order('listorder desc')->limit(10)->select();
		$this->assign('artists4',$artists4);
		$artists5 = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0 and category=7')->order('listorder desc')->limit(10)->select();
		$this->assign('artists5',$artists5);
		$artists6 = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0 and category=3')->order('listorder desc')->limit(10)->select();
		$this->assign('artists6',$artists6);
		$artists7 = M()->db(2,'DB_BSM')->table($bsm_prefix.'artist')->field('id,name,thumb')->where('status>0 and category=2')->order('listorder desc')->limit(10)->select();
		$this->assign('artists7',$artists7);
        //热门专场
        $hot_special = D('Document')->getHotSpecial(102);
		foreach($hot_special as $key=>&$val){
			switch($key){
				case 0:$val['linkurl'] = C(WEB_URL).'paimai/guohua.html';$val['name'] = str_replace('专场','拍卖',$val['name']);break;
				case 1:$val['linkurl'] = C(WEB_URL).'paimai/shufa.html';$val['name'] = str_replace('专场','拍卖',$val['name']);break;
				case 2:$val['linkurl'] = C(WEB_URL).'paimai/yuqi.html';$val['name'] = str_replace('专场','拍卖',$val['name']);break;
				case 3:$val['linkurl'] = C(WEB_URL).'paimai/ciqi.html';$val['name'] = str_replace('专场','拍卖',$val['name']);break;
				case 4:$val['linkurl'] = C(WEB_URL).'paimai/muqi.html';$val['name'] = str_replace('专场','拍卖',$val['name']);break;
				case 5:$val['linkurl'] = C(WEB_URL).'paimai/shoushanshi.html';$val['name'] = str_replace('专场','拍卖',$val['name']);break;
				case 6:$val['linkurl'] = C(WEB_URL).'paimai/youhua.html';$val['name'] = str_replace('专场','拍卖',$val['name']);break;
				default:break;
			}
		}
        $this->assign('hot_special',$hot_special);

        //热门画廊
        $gallery1 = M()->db(2,'DB_BSM')->table($bsm_prefix.'gallery')->field('id,name,thumb')->where('disable>0 and area=2')->order('id desc')->limit(6)->select();
        $this->assign('gallery1',$gallery1);
		$gallery2 = M()->db(2,'DB_BSM')->table($bsm_prefix.'gallery')->field('id,name,thumb')->where('disable>0 and area=3')->order('id desc')->limit(6)->select();
		$this->assign('gallery2',$gallery2);
		$gallery3 = M()->db(2,'DB_BSM')->table($bsm_prefix.'gallery')->field('id,name,thumb')->where('disable>0 and area=21')->order('id desc')->limit(6)->select();
		$this->assign('gallery3',$gallery3);

        //拍卖
        $auctionCollection = D('Auction/HomeAuction')->auctionCollection(10);
        $this->assign('auctionCollection',$auctionCollection);

        $auctionHots = D('Auction/HomeAuction')->auctionHots(10);
        $this->assign('auctionHots',$auctionHots);

        //在线拍卖
        $auctionOnline = D('Auction/HomeAuction')->auctionOnline(10);
        $this->assign('auctionOnline',$auctionOnline);

        //拍卖专场
        $auctionSpecial = D('Auction/HomeAuction')->auctionSpecial(0,2);
        $this->assign('auctionSpecial',$auctionSpecial);

        $auctionSpecialPre = D('Auction/HomeAuction')->auctionSpecial(1,2);
        $this->assign('auctionSpecialPre',$auctionSpecialPre);


        //在线鉴定
        $identifyOnline = M()->db(2,'DB_BSM')->table($bsm_prefix.'identify_data')->field('`id`,`name`,`thumb`,`createtime`,`hits`,`question`')->where('`isok` > 0')->order('`createtime` DESC')->limit(5)->select();
        $identifyOnlineN = M()->db(2,'DB_BSM')->table($bsm_prefix.'identify_data')->field('`id`,`name`,`thumb`,`createtime`,`hits`,`question`')->where('`isok` = 0')->order('`createtime` DESC')->limit(5)->select();
        $this->assign('identifyOnline',$identifyOnline);
        $this->assign('identifyOnlineN',$identifyOnlineN);

        //问答
        $askHot = D('Ask/HomeQuestion')->getQuestions(1,0,6);
        $askSolved = D('Ask/HomeQuestion')->getQuestions(0,1,6);
        $askUnsolved = D('Ask/HomeQuestion')->getQuestions(0,2,6);
        $this->assign('askHot',$askHot);
        $this->assign('askSolved',$askSolved);
        $this->assign('askUnsolved',$askUnsolved);

        //百科
        $baike = D('Baike/Category')->cateone();
		$baikenew = M('baike_category')->field('cid,pid,short_name,name')->select();
		$baikes = catebaike($baikenew);
		//dump($baikes);
        $this->assign('baike',$baikes);

        //论坛热贴
        $forum_hots = D('Ultrax')->getForumHots();
        $this->assign('forum_hots',$forum_hots);

        //一周排行
        $week_top = D('Ultrax')->getWeekTop();
        $this->assign('week_top',$week_top);

        //网友晒宝
        $user_show = D('Ultrax')->getUserShow();
        $this->assign('user_show',$user_show);

        //古玩城
        $mall_stores = D('Mall')->getStoresInfo(0,1);
        $mall_stores_adv = D('Mall')->getStoresInfo(1,10);
        $mall_stores_category = D('Mall')->getStoresCategory();
        $mall_stores_goods = D('Mall')->getStoresGoods(12);
        $this->assign('mall_stores',$mall_stores);
        $this->assign('mall_stores_adv',$mall_stores_adv);
        $this->assign('mall_stores_category',$mall_stores_category);
        $this->assign('mall_stores_goods',$mall_stores_goods);

        //收藏知识
        $collection_know = D('Document')->getCollectionKnow('',30);
        $collection_know_pos = D('Document')->getCollectionKnow('',5,1);
		
		$collection_know1 = D('Document')->getCollectionKnow('翡翠',30);
		$collection_know_pos1 = D('Document')->getCollectionKnow('翡翠',5,1);
		$collection_know2 = D('Document')->getCollectionKnow('宝石',30);
		$collection_know_pos2 = D('Document')->getCollectionKnow('宝石',5,1);
		$collection_know3 = D('Document')->getCollectionKnow('琥珀',30);
		$collection_know_pos3 = D('Document')->getCollectionKnow('琥珀',5,1);
		$collection_know4 = D('Document')->getCollectionKnow('碧玺',30);
		$collection_know_pos4 = D('Document')->getCollectionKnow('碧玺',5,1);
		$collection_know5 = D('Document')->getCollectionKnow('玉髓',30);
		$collection_know_pos5 = D('Document')->getCollectionKnow('玉髓',5,1);
		$collection_know6 = D('Document')->getCollectionKnow('瓷器',30);
		$collection_know_pos6 = D('Document')->getCollectionKnow('瓷器',5,1);


        $this->assign('collection_know',$collection_know);
        $this->assign('collection_know_pos',$collection_know_pos);
		$this->assign('collection_know1',$collection_know1);
        $this->assign('collection_know_pos1',$collection_know_pos1);
		$this->assign('collection_know2',$collection_know2);
        $this->assign('collection_know_pos2',$collection_know_pos2);
		$this->assign('collection_know3',$collection_know3);
        $this->assign('collection_know_pos3',$collection_know_pos3);
		$this->assign('collection_know4',$collection_know4);
        $this->assign('collection_know_pos4',$collection_know_pos4);
		$this->assign('collection_know5',$collection_know5);
        $this->assign('collection_know_pos5',$collection_know_pos5);
		$this->assign('collection_know6',$collection_know6);
        $this->assign('collection_know_pos6',$collection_know_pos6);
        //print_r($mall_stores);
        $footNav = D('Document')->footerNav();
        $this->assign('footNav',$footNav);

        //友情链接
        $friend_links = M()->db(1,'DB_V9')->table($v9_prefix.'link')->field('name,url')->where('typeid=0')->order('listorder desc')->limit(100)->select();
        $this->assign('friend_links',$friend_links);
        $this->display();
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