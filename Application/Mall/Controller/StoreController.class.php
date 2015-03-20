<?php

// +--------------------------------------
// | 前端店铺 控制器文件
// +--------------------------------------
// | Author: Rain.Zen
// +--------------------------------------

namespace Mall\Controller;
use Think\Model;

class StoreController extends FpublicController {

    // 初始化
    public function _initialize(){

        parent::_initialize();
        $this->Model = D('Store');

        // 店铺ID
        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) OR $this->error('店铺不存在或者已被锁定');
        $this->where = array('store_id' => $id);

        // 店铺信息
        $store = $this->Model->where($this->where)->find();
        empty($store) AND $this->error('店铺不存在或者已被锁定');
        $this->store = $store;

        // 店铺其他信息
        $storeStatic = M('MallStoreCount')->where($this->where)->find();
        empty($storeStatic) OR $this->storeStatic = $storeStatic;

        // 店铺焦点图
        $aryFocus = D('StoreFocus')->field('id,title,img,url')->where(array('store_id'=>$id))->order('listorder DESC')->limit(5)->select();
        empty($aryFocus) OR $this->aryFocus = $aryFocus;

        // 获取该产品所属的店铺自有的类别
        $storeCate = getAryStoreCate($id);
        if(!empty($storeCate)){
            $this->navCate = $storeCate; // 导航的分类显示
            $storeCate = list_to_tree($storeCate);
            $this->storeCate = $storeCate;
        }

        // 热门推荐(5条数据)
        $condition = $this->where;
        $condition['recommend'] = 1;
        $aryRecom = D('Goods')->field('goods_id, goods_name, default_img, goods_price')->where($condition)->order('goods_id DESC')->limit(5)->select();
        empty($aryRecom) OR $this->aryRecom = $aryRecom;
        unset($condition, $aryRecom);

        // 咨询信息的分页数
        $this->questionSize = 1;

        // 更新浏览次数
        updateModelCount('MallStoreCount', 'views', array('store_id' => $this->where['store_id']));
    }

    /**
     * 店铺搜索
     */
    public function search(){

        // 自定义列表查询字段
        $goodsTable = 'yishu_mall_goods g';
        $goodsStaticTable = 'yishu_mall_goods_count c';
        $tableOn = 'g.goods_id = c.goods_id';
        $M = new Model();

        // 店铺ID的条件
        $where = ' AND g.store_id = '.$this->where['store_id'];

        $uri = '';
        if(isset($_GET['kw']) && trim($_GET['kw']) != ''){
            $kw = filterStr($_GET['kw']);
            $where.= " AND g.goods_name LIKE '%".$kw."%'";
            $uri.= '&kw='.$kw;
            $this->kw = $kw;
        }
        if(isset($_GET['min']) && intval($_GET['min']) > 0){
            $min = max(intval($_GET['min']),0);
            $where.= ' AND g.goods_price > '.$min;
            $uri.= '&min='.$min;
            $this->min = $min;
        }
        if(isset($_GET['max']) && intval($_GET['max']) > 0){
            $max = max(intval($_GET['max']),0);
            $where.= ' AND g.goods_price <= '.$max;
            $uri.= '&max='.$max;
            $this->max = $max;
        }

        $aryCount = $M->query('SELECT COUNT(1) FROM '.$goodsTable.','.$goodsStaticTable.' WHERE '.$tableOn.$where);
        $count = $aryCount[0]['COUNT(1)'];

        // 类别下的产品数组
        if($count){

            $size = 1;
            $zPage = new \Org\Util\Zpage($count, $size, 11, $_GET['page'], '?page=', $uri);
            $this->htmlPage = $zPage->html();
            $this->allPage = $zPage->allPage;

            $start = ($zPage->thenPage - 1) * $size;
            $limit = $start == 0 ? $size : $start.','.$size;

            $this->oInt12 = 1;
            $this->oInt34 = 3;
            $this->oInt56 = 5;
            $this->oInt78 = 7;
            $orderInt = isset($_GET['O']) ? max(intval($_GET['O']), 1) : 1;
            if($orderInt == 1 || $orderInt == 2){
                $order = $orderInt == 1 ? 'g.goods_id DESC' : 'g.goods_id ASC';
                $this->oInt12 = $orderInt == 1 ? 2 : 1;
            }else if($orderInt == 3 || $orderInt == 4){
                $order = $orderInt == 3 ? 'c.views DESC' : 'c.views ASC';
                $this->oInt34 = $orderInt == 3 ? 4 : 3;
            }else if($orderInt == 5 || $orderInt == 6){
                $order = $orderInt == 5 ? 'c.comment DESC' : 'c.comment ASC';
                $this->oInt56 = $orderInt == 5 ? 6 : 5;
            }else if($orderInt == 7 || $orderInt == 8){
                $order = $orderInt == 7 ? 'g.goods_price DESC' : 'g.goods_price ASC';
                $this->oInt78 = $orderInt == 8 ? 7 : 8;
            }else{
                $order = 'g.goods_id DESC';
            }

            $field = 'g.goods_id, g.goods_name, g.default_img, g.goods_price';
            $aryList = $M->query('SELECT '.$field.' FROM '.$goodsTable.','.$goodsStaticTable.' WHERE '.$tableOn.$where.' ORDER BY '.$order.' LIMIT '.$limit);
            empty($aryList) OR $this->aryList = $aryList;
            unset($aryList);
        }

        $this->display();
    }

    /**
     * 店铺首页输出
     */
    public function index(){

        // 查询的字段
        $field = array('goods_id','goods_name','default_img', 'goods_price');
        $where = array('status'=>2); // 审核通过
        $where['store_id'] = $this->where['store_id'];

        // 新品上市
        $aryNew = D('Goods')->field($field)->where($where)->order('create_time DESC')->limit(8)->select();
        empty($aryNew) OR $this->aryNew = $aryNew;

        // 类别下的列表
        $storeCate = $this->storeCate;
        $aryCateGoods = array();
        foreach($storeCate as $rs){
            $aryTmp = $this->_cateForGoods($rs['id'], $field, $where);
            empty($aryTmp) OR $aryCateGoods[$rs['id']] = $aryTmp;
        }
        empty($aryCateGoods) OR $this->aryCateGoods = $aryCateGoods;

		$seo_title = $this->store['store_name']."_".C('WEB_SITE.name').$this->store['store_name']."官网店铺";
		$seo_keyword = $this->store['store_name'].",".C('WEB_SITE.name').$this->store['store_name'];
		$seo_description = $this->store['store_name']."隆重入驻中艺古玩城，盛大开业之际为藏家提供".$this->store['store_name']."本店的最新最具珍藏价值的古玩藏品，欢迎广大藏友观摩、欣赏、购买，".$this->store['store_name']."必定竭诚为藏友服务，金牌店铺，值得信赖。";
		$this->assign('seo_title',$seo_title);
		$this->assign('seo_keyword',$seo_keyword);
		$this->assign('seo_description',$seo_description);

        // 输出模板
        $this->display();
    }

    /**
     * [私有]获取类别下的产品列表(如果一级类别下有二级类别则获取二级类别的ID)
     * @param $cateId
     * @param $field
     * @param $where
     * @param string $order
     * @return mixed
     */
    private function _cateForGoods($cateId, $field, $where, $order = 'create_time DESC'){
        $aryCate = $this->storeCate;
        $arySub = $aryCate[$cateId]['_child'];
        if(isset($arySub) && is_array($arySub)){
            $subIds = '';
            foreach($arySub as $rs){
                $subIds .= ','.$rs['id'];
            }
            $subIds = trim($subIds, ',');
            $where['store_cate'] = array('in', $subIds);
        }else{
            $where['store_cate'] = $cateId;
        }
        return D('Goods')->field($field)->where($where)->order($order)->limit(8)->select();
    }

    /**
     * 分类下的店铺产品数组
     *
     */
    public function lists(){

        // 检测是否为登录用户
        if(isset($_SESSION['user_auth']['uid']) && $_SESSION['user_auth']['uid'] > 0){
            $uid = $_SESSION['user_auth']['uid'];
            empty($uid) OR $this->loginUser = getUserInfo($uid);
        }

        // 自定义列表查询字段
        $goodsTable = 'yishu_mall_goods g';
        $goodsStaticTable = 'yishu_mall_goods_count c';
        $tableOn = 'g.goods_id = c.goods_id';
        $M = new Model();

        // 店铺ID的条件
        $where = ' AND g.store_id = '.$this->where['store_id'];

        // 店铺分类ID
        isset($_GET['cid']) AND $cid = intval($_GET['cid']);
        isset($cid) OR $this->error('类别不存在或者已被锁定');
        $this->cate_id = $cid;

        // 获取类别的名称以及判断是否存在子类别
        $aryCate = $this->storeCate;
		
        $arySub = $aryCate[$cid]['_child'];
		
        if(isset($arySub) && is_array($arySub)){
            $this->arySubCate = $arySub;
            $this->cateName = $aryCate[$cid]['cate_name'];
            $subIds = '';
            foreach($arySub as $rs){
                $subIds .= ','.$rs['id'];
            }
            $subIds = trim($subIds, ',');
            empty($subIds) OR $where.= ' AND g.store_cate IN('.$subIds.')';
        }else{
			
            $pid = getParentStoreCateId($cid);
            $this->arySubCate = $aryCate[$pid]['_child'];
            $this->cateName = $aryCate[$pid]['cate_name'];
            $where.= ' AND g.store_cate = '.$cid;
        }

		$this->cid = $cid;

        $uri = '';
        if(isset($_GET['kw']) && trim($_GET['kw']) != ''){
            $kw = filterStr($_GET['kw']);
            $where.= " AND g.goods_name LIKE '%".$kw."%'";
            $uri.= '&kw='.$kw;
            $this->kw = $kw;
        }
        if(isset($_GET['min']) && intval($_GET['min']) > 0){
            $min = max(intval($_GET['min']),0);
            $where.= ' AND g.goods_price > '.$min;
            $uri.= '&min='.$min;
            $this->min = $min;
        }
        if(isset($_GET['max']) && intval($_GET['max']) > 0){
            $max = max(intval($_GET['max']),0);
            $where.= ' AND g.goods_price <= '.$max;
            $uri.= '&max='.$max;
            $this->max = $max;
        }

        $aryCount = $M->query('SELECT COUNT(1) FROM '.$goodsTable.','.$goodsStaticTable.' WHERE '.$tableOn.$where);
        $count = $aryCount[0]['COUNT(1)'];

        // 类别下的产品数组
        if($count){

            $size = 20;
            $zPage = new \Org\Util\Zpage($count, $size, 11, $_GET['page'], '?page=', $uri);
            $this->htmlPage = $zPage->html();
            $this->allPage = $zPage->allPage;

            $start = ($zPage->thenPage - 1) * $size;
            $limit = $start == 0 ? $size : $start.','.$size;

            $this->oInt12 = 1;
            $this->oInt34 = 3;
            $this->oInt56 = 5;
            $this->oInt78 = 7;
            $orderInt = isset($_GET['O']) ? max(intval($_GET['O']), 1) : 1;
            if($orderInt == 1 || $orderInt == 2){
                $order = $orderInt == 1 ? 'g.goods_id DESC' : 'g.goods_id ASC';
                $this->oInt12 = $orderInt == 1 ? 2 : 1;
            }else if($orderInt == 3 || $orderInt == 4){
                $order = $orderInt == 3 ? 'c.views DESC' : 'c.views ASC';
                $this->oInt34 = $orderInt == 3 ? 4 : 3;
            }else if($orderInt == 5 || $orderInt == 6){
                $order = $orderInt == 5 ? 'c.comment DESC' : 'c.comment ASC';
                $this->oInt56 = $orderInt == 5 ? 6 : 5;
            }else if($orderInt == 7 || $orderInt == 8){
                $order = $orderInt == 7 ? 'g.goods_price DESC' : 'g.goods_price ASC';
                $this->oInt78 = $orderInt == 8 ? 7 : 8;
            }else{
                $order = 'g.goods_id DESC';
            }

            $field = 'g.goods_id, g.goods_name, g.default_img, g.goods_price';
            $aryList = $M->query('SELECT '.$field.' FROM '.$goodsTable.','.$goodsStaticTable.' WHERE '.$tableOn.$where.' ORDER BY '.$order.' LIMIT '.$limit);
            empty($aryList) OR $this->aryList = $aryList;
            unset($aryList);
        }

        // 咨询信息[前五条信息]
        $condition = $this->where;
        $condition['q_id'] = 0;
        $intQuestion = D('Question')->where($condition)->count();
        if($intQuestion > 0){
            $this->intQuestion = $intQuestion;
            $this->QuesAllPage = ceil($intQuestion / $this->questionSize);
            $ary = $this->_aryQuestion($condition);
            empty($ary['q']) OR $this->aryQues = $ary['q'];
            empty($ary['r']) OR $this->aryReply = $ary['r'];
            empty($ary['u']) OR $this->aryUsername = $ary['u'];
        }
		$cate_name = $aryCate[$cid]['cate_name'];

		$seo_title = $cate_name."_".$this->store['store_name']."_".C('WEB_SITE.name');
		$seo_keyword = $cate_name.",".$this->store['store_name'].$cate_name.",".C('WEB_SITE.name').$this->store['store_name'].$cate_name;
		$seo_description = $this->store['store_name']."隆重入驻中艺古玩城，盛大开业之际为藏家提供".$cate_name."等最新最具珍藏价值的古玩藏品，欢迎广大藏友观摩、欣赏、购买，必定竭诚为藏友服务，金牌店铺，值得信赖。";
		$this->assign('seo_title',$seo_title);
		$this->assign('seo_keyword',$seo_keyword);
		$this->assign('seo_description',$seo_description);

        $this->display();
    }

    /**
     * ajax动态获取的咨询列表
     */
    public function question(){

        // 获取当前页
        $thenPage = max(intval($_GET['tpage']), 1);
        $allPage = intval($_GET['apage']);
        $thenPage >= $allPage AND $thenPage = $allPage;

        // 组装条件
        $condition = $this->where;
        $condition['q_id'] = 0;

        // 读取数据
        $ary = $this->_aryQuestion($condition, $thenPage);
        empty($ary) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>$ary)) .')');
    }

    /**
     * 获取咨询信息的列表[私有方法]
     * @param $condition SQL语句的条件
     * @param int $thenPage 当前页码数
     * @return array 返回符合条件且当期页的数组
     */
    private function _aryQuestion($condition, $thenPage = 1){

        // 预定义返回值
        $ary = array();

        // 当前页判断读取范围
        if($thenPage == 1){
            $limit = $this->questionSize;
        }else{
            // 起始值
            $start = ($thenPage - 1) * $this->questionSize;
            $limit = $start.','.$this->questionSize;
        }
        $ary['tp'] = $thenPage;

        // 读取数据
        $question = D('Question')->field('question_id, uid, content, create_time')->where($condition)->order('create_time DESC')->limit($limit)->select();

        // 处理数组为符合要求的格式
        if($question){

            // 预定义需要的容器
            $aryQues = $aryQid = $aryUid = array();

            // 获取回复ID和用户ID及当前数组
            foreach($question as $rs){
                $rs['create_time'] = date('Y-m-d H:i:s', $rs['create_time']);
                $aryQues[] = $rs;
                $aryUid[] = $rs['uid'];
                $aryQid[] = $rs['question_id'];
            }

            // 组装进预返回数组
            $ary['q'] = $aryQues;

            // 获取回复数组
            $aryQid = array_unique($aryQid);
            $inQid = implode(',', $aryQid);
            $reply = D('Question')->field('q_id, content, create_time')->where(array('q_id'=>array('in', $inQid)))->select();
            if($reply){
                foreach($reply as $rs){
                    $rs['create_time'] = date('Y-m-d H:i:s', $rs['create_time']);
                    $aryReply[$rs['q_id']] = $rs;
                }
                $ary['r'] = $aryReply; // 组装进预返回数组
            }

            // 获取用户数组
            $aryUid = array_unique($aryUid);
            $inUid = implode(',', $aryUid);
            $ucMember = new \User\Model\UcenterMemberModel();
            $aryUser = $ucMember->field('id,username')->where(array('id'=>array('in', $inUid)))->select();
            $aryUsername = array();
            if($aryUser){
                foreach($aryUser as $rs){
                    $aryUsername[$rs['id']] = $rs['username'];
                }
                $ary['u'] = $aryUsername;// 组装进预返回数组
            }
            unset($ucMember, $aryUsername, $aryUser, $aryUid, $inUid);
        }

        return $ary;
    }

    /**
     * 当前控制器下的ajax操作[加入收藏和清除历史记录]
     */
    public function ajaxInit(){

        isset($_GET['act']) AND $act = trim($_GET['act']);
        (isset($act) && in_array($act, array('collect','history','question'))) OR die('Action is not allowed!');

        $act == 'collect' AND $this->_collect(); // 加入收藏
        $act == 'question' AND $this->_question(); // 店铺咨询
        $act == 'history' AND $this->_history();
    }

    /**
     * 店铺商品咨询
     */
    private function _question(){

        // 检测是否为登录用户
        if(isset($_SESSION['user_auth']['uid']) && $_SESSION['user_auth']['uid'] > 0){
            $uid = $_SESSION['user_auth']['uid'];
        }else{
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请先登录后再收藏')) .')');
        }

        isset($_GET['content']) AND $content = filterStr($_GET['content']);
        empty($content) AND die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请填写咨询内容')) .')');

        // 店铺ID
        $sid = $this->store['store_id'];

        // 不能咨询自己
        if($this->store['uid'] == $uid){
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'很抱歉，您不能咨询自己店铺')) .')');
        }

        // 组装入库信息
        $data['content'] = $content;
        $data['store_id'] = $sid;
        $data['uid'] = $uid;
        $data['create_time'] = time();
        $bool = D('Question')->data($data)->add();

        // 入库后操作
        if($bool){

            // 返回成功信息
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>'咨询信息已经提交成功，我们会尽快恢复您！')) .')');

        }else{

            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'咨询信息提交失败')) .')');
        }
    }

    /**
     * 店铺加入收藏[私有方法]
     */
    private function _collect(){

        // 检测是否为登录用户
        if(isset($_SESSION['user_auth']['uid']) && $_SESSION['user_auth']['uid'] > 0){
            $uid = $_SESSION['user_auth']['uid'];
        }else{
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请先登录后再收藏')) .')');
        }

        // 收藏类型: 0为产品，1为店铺
        $type = intval($_GET['type']);
        $sid = $this->store['store_id'];

        // 收藏夹数据库检测
        $info = D('collect')->where(array('collect_type'=>$type,'item_id'=>$sid,'uid'=>$uid))->find();
        empty($info) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'您已经收藏过了')) .')');
        unset($info);

        // 不能收藏自己
        if($this->store['uid'] == $uid){
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'很抱歉，您不能收藏自己店铺')) .')');
        }

        // 组装入库信息
        $data['collect_type'] = $type;
        $data['collect_title'] = $this->store['store_name'];
        $data['item_id'] = $sid;
        $data['uid'] = $uid;
        $data['create_time'] = time();
        $bool = D('Collect')->data($data)->add();

        // 入库后操作
        if($bool){

            // 更新统计表的静态值(更改店铺统计表里面的collect字段)
            updateModelCount('MallStoreCount', 'collect', array('store_id' => $sid));

            // 返回成功信息
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>'收藏成功')) .')');

        }else{

            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'收藏失败')) .')');
        }
    }

    /**
     * 清除历史记录[私有方法]
     */
    private function _history(){
        resetHistory();
        die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1')) .')');
    }
}