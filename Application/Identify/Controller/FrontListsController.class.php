<?php

// +----------------------------------------------------------------------
// | 鉴定模块_前端_列表页_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Identify\Controller;
use Think\Model;
class FrontListsController extends BaseController {
    public function _initialize() {
        parent::_initialize();
        $auth = getLoginStatus();
        $uid = $auth['uid'];
        $this->auth = $auth;
    }
    public function index() {
        
        // 类别数组
        if(!S('IdentifyCategory')){
            D('IdentifyCategory')->createIdentifyCategoryCache();
        }
        $this->arrCategory = S('IdentifyCategory');
        
        // 获取类别ID
        $cid = I('get.cid', '0', 'int');
        empty($cid) OR $where['category'] = $cid;
        empty($cid) OR $this->cid = $cid;

		switch($cid){
			case 1;
				$seoTitle = '陶瓷_陶瓷鉴定_古陶瓷的鉴定_瓷器鉴别-中艺鉴定网';
				$seoKword = '陶瓷,陶瓷鉴定,古陶瓷鉴定,瓷器鉴别,陶瓷鉴别方法';
				$seoDesc = '中艺鉴定网陶瓷鉴定估价栏目,专业为您提供瓷器鉴别、古陶瓷的鉴定以及估价服务，并提供陶瓷价格行情,陶瓷估价图片等相关各类陶瓷鉴定估价的信息。';
			break;
			case 2;
				$seoTitle = '玉器鉴别_玉石鉴别_玉器鉴定_玉器鉴定师-中艺鉴定网';
				$seoKword = '玉器鉴别,玉石鉴定,玉器鉴定,玉器鉴定,玉器鉴定师';
				$seoDesc = '中艺鉴定网聘请专业的玉器鉴定师团队为您随时解答各类玉石鉴别的疑问，更有详细的玉器鉴别知识供您学习参考，我们致力于为您打造一个专业的玉器鉴定平台。';
			break;
			case 3;
				$seoTitle = '字画鉴定_古画鉴定_免费鉴定书画_书画鉴定专家-中艺鉴定网';
				$seoKword = '字画鉴定,古画鉴定,书画鉴定专家,免费鉴定书画';
				$seoDesc = '中艺鉴定网提供最新古画鉴定的收藏信息，永久为您免费鉴定书画或字画鉴定服务，专业的书画鉴定专家为您供您最权威的鉴定建议。';
			break;
			case 6;
				$seoTitle = '鸡血石鉴定_奇石鉴定_玛瑙鉴别_琥珀鉴别_邮票鉴定-中艺鉴定网';
				$seoKword = '鸡血石鉴定,奇石鉴定,玛瑙鉴别,琥珀鉴别,邮票鉴定';
				$seoDesc = '中艺鉴定网发布了大量鸡血石鉴定、琥珀鉴别、邮票鉴定、玛瑙鉴定、奇石鉴定的知识供各位收藏爱好者学习阅读，有疑问还可以资讯专业客服为您解答各项疑问。';
			break;
			case 5;
				$seoTitle = '古钱币价格_收藏钱币_古钱币鉴定_钱币鉴定-中艺鉴定网';
				$seoKword = '古钱币价格,收藏钱币,古钱币鉴定,钱币鉴定';
				$seoDesc = '中艺鉴定网为您提供钱币鉴定、古钱币鉴定的特殊服务，同时还会告知您古钱币价格的市场行情以及收藏钱币的各项专业建议供您参考';
			break;
			case 4;
				$seoTitle = '铜器_铜器鉴定_铜器收藏_铜器鉴别_铜器年代-中艺鉴定网';
				$seoKword = '铜器,铜器鉴定,铜器收藏,铜器鉴别,铜器年代';
				$seoDesc = '中艺鉴定网为广大藏友提供铜器收藏收藏圈最新信息，还有专业人员为您免费进行铜器鉴定、估价及判断铜器年代，同时还有铜器鉴别的海量知识供您参考。';
			break;
			default:
				$seoTitle = '陶瓷_玉器_书画_铜器_钱币_杂项-中艺鉴定网';
				$seoKword = '陶瓷,玉器,书画,铜器,钱币,杂项';
				$seoDesc = '';
		}
		$this->seoTitle = $seoTitle;
		$this->seoKword = $seoKword;
		$this->seoDesc = $seoDesc;
        
        // 分页需要定义为 $this->Model
        $this->Model = D('IdentifyData');
        
        // 分页信息
        $this->page = $this->pages($where);
        
        // 分页排序
        $oid = I('get.oid', '0', 'int');
		switch($oid){
			case 1: $order = '`createtime` DESC'; break;
			case 2: $order = '`hits` DESC'; break;
			default: $order = '`id` DESC';
		}
        $this->oid = $oid;

		// 鉴定状态
		$oid == 3 AND $where['isok'] = 1;
		$oid == 4 AND $where['isok'] = 0;
        
        // 分页列表
        $field = array('id', 'name', 'category', 'isok', 'isopen', 'mid', 'createtime');
		$this->page = $this->pages($where);
        $this->list = $this->Model->field('*')->where($where)->limit($this->p->firstRow.', '.$this->p->listRows)->order($order)->select();
        
        // 鉴定专家团
        $this->arrExpert = D('IdentifyExpert')->field('id,username, thumb')->order('id DESC')->limit(6)->select();
        
        // 推荐藏品(推荐且不包含当前ID)
        $where = '`isopen` = 1 AND `isopen` = 1 AND `ispush` = 1';
        $this->arrPush = D('IdentifyData')->field('id, name, thumb, size, createtime')->where($where)->order('id DESC')->limit(5)->select();

        //最新拍卖
        $hotSql = 'SELECT D.currentprice,D.endtime,D.id,D.gid,D.hits,G.name,G.thumb FROM bsm_auction_data D,bsm_auction_goods G WHERE D.gid = G.id ORDER BY D.id DESC LIMIT 2';
        $Model = new Model();
        $this->hotAuction = $Model->query($hotSql);

        $this->display('Front:lists');
    }
    
}
