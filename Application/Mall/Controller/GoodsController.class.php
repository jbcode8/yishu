<?php

// +----------------------------------------------------------------------
// | 前端 产品 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------

namespace Mall\Controller;

class GoodsController extends FpublicController {

    public function _initialize(){

        parent::_initialize();
        $this->Goods = D('Goods');

        isset($_GET['id']) AND $id = intval($_GET['id']);
        $info = $this->Goods->where(array('goods_id'=>$id))->find();
        empty($info) AND $this->error('信息不存在或者已经被删除');
        $this->info = $info;
        $this->goods_id = $id;

        $this->commentSize = 1;
    }

    // 详细页
    public function index() {

        // 从初始化函数获取产品信息
        $info = $this->info;

        // 获取属性值
        $aryGoodsAttr = aryGoodsAttr($info['goods_id']); // ## 产品属性数组(属性名ID和属性值ID)
        if($aryGoodsAttr){
            $this->GoodsAttr = $aryGoodsAttr;
            $this->AttrVal = aryAttrVal($aryGoodsAttr); // ## 产品属性数组(属性值ID=>属性值)
            $this->AttrName = aryAttrName($aryGoodsAttr); // ## 产品属性数组(属性名ID=>属性名)
        }

        // 获取产品图片
        $aryGoodsImage = aryGoodsImage($info['goods_id']);
        empty($aryGoodsImage) OR $this->goodsImg = $aryGoodsImage;

        // 获取产品的静态值
        $aryGoodsCount = aryGoodsCount($info['goods_id']);
        empty($aryGoodsCount) OR $this->goodsCount = $aryGoodsCount;

        // 获取店铺所有信息
        $store = D('Store')->where(array('store_id'=>$info['store_id']))->find();
		empty($store) AND $this->error('该商品所属店家不存在或者已经被删除');
        $this->store = $store;

        // 店铺其他信息
        $storeStatic = M('MallStoreCount')->where(array('store_id'=>$store['store_id']))->find();
        empty($storeStatic) OR $this->storeStatic = $storeStatic;

        // 获取该产品所属的店铺自有的类别
        $storeCate = getAryStoreCate($info['store_id']);
        if(!empty($storeCate)){
            $this->navCate = $storeCate; // 导航的分类显示
            $storeCate = list_to_tree($storeCate);
            $this->storeCate = $storeCate;
        }

        // 热门推荐(5条数据)
        $condition['recommend'] = 1;
        $aryRecom = D('Goods')->field('goods_id, goods_name, default_img, goods_price')->where($condition)->order('goods_id DESC')->limit(5)->select();
        empty($aryRecom) OR $this->aryRecom = $aryRecom;
        unset($condition, $aryRecom);

        // 类别名
        $aryCate = D('Category')->getParentCategory($info['cate_id']);
		$aryCate = array_reverse($aryCate);
        $this->aryCate = $aryCate;

        // 评论信息[前五条信息]
        $condition['goods_id'] = $this->goods_id;
        $condition['c_id'] = 0;
        $intComment = D('Comment')->where($condition)->count();
        if($intComment > 0){
            $this->intComment = $intComment;
            $this->CommAllPage = ceil($intComment / $this->commentSize);
            $ary = $this->_aryComment($condition);
            empty($ary['c']) OR $this->aryComment = $ary['c'];
            empty($ary['r']) OR $this->aryReply = $ary['r'];
            empty($ary['u']) OR $this->aryUsername = $ary['u'];
        }

        // 添加历史记录
        addHistory($this->goods_id);

        // 更新浏览次数
        updateModelCount('MallGoodsCount', 'views', array('goods_id' => $this->goods_id));

        $this->display();
    }

    /**
     * ajax读取评论的列表信息
     */
    public function comment(){

        // 获取当前页
        $thenPage = max(intval($_GET['tpage']), 1);
        $allPage = intval($_GET['apage']);
        $thenPage >= $allPage AND $thenPage = $allPage;

        // 组装条件
        $condition['goods_id'] = $this->goods_id;
        $condition['c_id'] = 0;

        // 读取数据
        $ary = $this->_aryComment($condition, $thenPage);
        empty($ary) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>$ary)) .')');
    }

    /**
     * [私有]服务于获取评论列表(评论，回复，用户)
     * @param $condition
     * @param int $thenPage
     * @return array
     */
    private function _aryComment($condition, $thenPage = 1){

        // 预定义返回值
        $ary = array();

        // 当前页判断读取范围
        if($thenPage == 1){
            $limit = $this->commentSize;
        }else{
            // 起始值
            $start = ($thenPage - 1) * $this->commentSize;
            $limit = $start.','.$this->commentSize;
        }
        $ary['tp'] = $thenPage;

        // 读取数据
        $comment = D('Comment')->field('comment_id, uid, content, create_time')->where($condition)->order('create_time DESC')->limit($limit)->select();

        // 处理数组为符合要求的格式
        if($comment){

            // 预定义需要的容器
            $aryComm = $aryCid = $aryUid = array();

            // 获取回复ID和用户ID及当前数组
            foreach($comment as $rs){
                $rs['create_time'] = int2time($rs['create_time']);
                $aryComm[] = $rs;
                $aryUid[] = $rs['uid'];
                $aryCid[] = $rs['comment_id'];
            }

            // 组装进预返回数组
            $ary['c'] = $aryComm;

            // 获取回复数组
            $aryCid = array_unique($aryCid);
            $inCid = implode(',', $aryCid);
            $reply = D('Comment')->field('c_id, content, create_time')->where(array('c_id'=>array('in', $inCid)))->select();
            if($reply){
                foreach($reply as $rs){
                    $rs['create_time'] = int2time($rs['create_time']);
                    $aryReply[$rs['c_id']] = $rs;
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
        (isset($act) && in_array($act, array('collect','history','comment','addcart'))) OR die('Action is not allowed!');

        $act == 'collect' AND $this->_collect(); // 加入收藏
        $act == 'comment' AND $this->_comment(); // 商品评论
        $act == 'history' AND $this->_history(); // 清除历史记录
        $act == 'addcart' AND $this->_addcart(); // 加入购物车
    }

    /**
     * [私有]加入购物车
     */
    private function _addcart(){

        // 检测信息ID
        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');

        // 检测店铺ID
        isset($_GET['sid']) AND $sid = intval($_GET['sid']);
        isset($sid) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'暂无店铺信息')) .')');

        // 检测此店铺下的产品状态
        $data = D('Goods')->field('goods_id')->where(array('goods_id'=>$id,'store_id'=>$sid))->find();
        empty($data) AND die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'此店铺下暂无此信息')) .')');

        // 获取该店铺的店主UID 且 与当前的session的UID 比对
        $aryStore = D('Store')->field('uid')->where(array('store_id'=>$sid))->find();
        if(!empty($aryStore) && $aryStore['uid'] == $_SESSION['user_auth']['uid']){
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'很抱歉，您不能购买自己的商品')) .')');
        }

        // 获取已经存在的产品ID
        if(D('Cart')->add2cart($sid, $id)){
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>'加入成功！')) .')');
        }else{
			if(isset($_GET['isbk']) && intval($_GET['isbk'] == 1)){
				die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1')) .')');
			}else{
				die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'已经加入购物车')) .')');
			}
        }
    }

    /**
     * [私有]商品评论
     */
    private function _comment(){

        // 检测是否为登录用户
        if(isset($_SESSION['user_auth']['uid']) && $_SESSION['user_auth']['uid'] > 0){
            $uid = $_SESSION['user_auth']['uid'];
        }else{
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请先登录后再收藏')) .')');
        }

        isset($_GET['content']) AND $content = filterStr($_GET['content']);
        empty($content) AND die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请填写评论内容')) .')');

        // 根据店铺ID获取店铺的Uid
        $sid = $this->info['store_id'];
        $store = D('Store')->field('uid')->where(array('store_id'=>$sid))->find();
        empty($store) OR $store_uid = $store['uid'];

        // 不能咨询自己
        if($uid == $store_uid){
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'很抱歉，您不能评论自己产品')) .')');
        }

        // 组装入库信息
        $data['content'] = $content;
        $data['store_id'] = $sid;
        $data['uid'] = $uid;
        $data['goods_id'] = $this->goods_id;
        $data['create_time'] = time();
        $bool = D('Comment')->data($data)->add();
        $map['goods_id'] = $this->goods_id;
        // 入库后操作
        if($bool){
            $data['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
            //评论数增加一条
            $goodscount = D('Goods_count');
            $goodscount->where($map)->setInc('comment');
            
            // 返回成功信息
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'1','info'=>$data)) .')');

        }else{

            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'评论信息提交失败')) .')');
        }
    }

    /**
     * [私有]加入收藏夹
     */
    private function _collect(){

        // 检测信息ID
        isset($_GET['id']) AND $id = intval($_GET['id']);
        isset($id) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');
        $where['goods_id'] = $id;

        // 收藏类型: 0为产品，1为店铺
		$type = isset($_GET['type']) && intval($_GET['type']) > 0 ? 1 : 0;

        // 数据库检测
        $data = D('Goods')->field('goods_name')->where($where)->find();
        empty($data) AND die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'无效的信息ID')) .')');
        $collect_title = $data['goods_name'];
        unset($where, $data);

        // 检测是否为登录用户
        if(isset($_SESSION['user_auth']['uid']) && $_SESSION['user_auth']['uid'] > 0){
            $uid = $_SESSION['user_auth']['uid'];
        }else{
            die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'请先登录后再收藏')) .')');
        }

        // 判断是否已经加入
        $where['uid'] = $uid;
        $where['collect_type'] = $type;
        $where['item_id'] = $id;
        $data = D('Collect')->field('collect_id')->where($where)->find();
        empty($data) OR die(trim($_GET['backfunc']).'('. json_encode(array('status'=>'0','info'=>'您已经收藏过了')) .')');
        unset($where, $data);

        // 加入收藏表
        $data['collect_type'] = $type;
        $data['collect_title'] = $collect_title;
        $data['item_id'] = $id;
        $data['uid'] = $uid;
        $data['create_time'] = time();
        $bool = D('Collect')->add($data);

        // 加入成功后，更新产品静态信息表的收藏数
        if($bool){

            // 更新产品静态信息表
            D('GoodsCount')->where(array('goods_id' => $id))->setInc('collect');

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