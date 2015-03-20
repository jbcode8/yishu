<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Home\Controller\HomeController;
class FrontDataController extends HomeController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionData');
	$this->assign('title', '中国艺术网-拍卖');

    }

    /**
     * 列表信息
     */
    public function index() {

        // 拍卖信息
        $aid = I('get.id', '0', 'int');

        $data = $this->Model->where(array('id' => $aid))->find();
        empty($data) AND $this->error('此信息不存在或已经删除！');
        $this->data = $data;

        // 商品信息
        $goods = D('AuctionGoods')->where(array('id' => $data['gid']))->find();
        $this->goods = $goods;

        /* 获取当前价格(最高价格) */
        // 先从竞价记录表里获取此拍卖信息对应的产品的最高价格
        $currentPrice = D('AuctionPriceRecord')->where(array('aid' => $data['id']))->max('price');
        // 如果没有记录，则说明未出价，那么则取起拍价
        empty($currentPrice) AND $currentPrice = $data['startprice'];
        $this->currentPrice = $currentPrice;

        /* 获取次数 */
        $recordCount = D('AuctionPriceRecord')->where(array('aid' => $data['id']))->count('id');
        empty($recordCount) AND $recordCount = 0;
        $this->recordCount = $recordCount;

        /* 剩余时间 */
        $laveTime = $data['endtime'] - time();
        $this->laveTime = ($laveTime > 0) ? $laveTime : 0;

        /* 出价记录 */
        if ($recordCount) {
            $arrRecord = D('AuctionPriceRecord')->where(array('aid' => $data['id']))->order('`price` DESC')->select();
        }
        
        // 循环获取用户的昵称
        if(is_array($arrRecord)){
            $newArr = array();
            foreach($arrRecord as $k => $v){
                $newArr[$k] = $v;
                $newArr[$k]['nickname'] = getNickName($v['mid']);
            }
            $this->arrRecord = $newArr;
        }
        
        // 竞价口号
        $Slogan = D('AuctionData')->arrSlogan();
        //print_r($Slogan);
        $this->Slogan = json_encode($Slogan);
        //print_r($this->Slogan);

        $sql = "SELECT M.username , C.* FROM bsm_auction_comment C LEFT JOIN bsm_member M ON M.mid = C.mid WHERE C.isopen=1 AND C.auctionid=".$aid."  ORDER BY C.createtime DESC LIMIT 10";
        $this->comment = D('AuctionComment')->query($sql);
//        $this->commentcount = M('AuctionComment')->where("isopen=1 AND auctionid=".$aid)->count();
        $this->commentCount = D('AuctionComment')->where(array('auctionid' => $aid, 'isopen' => '1'))->count();

        $this->display('Front:data_detail');
    }

    /**
     * 拍卖执行操作
     * 返回状态码()
     *  1.请先登录后再参加拍卖活动
     *  2.请填写正确的价格
     *  3.此拍卖信息不存在或者已经被删除
     *  4.此拍卖信息已经过期
     *  5.此拍卖信息已经结束
     *  6.此商品的拍卖活动需要最低保证金()或积分()，您的余额不足()
     *  7.您的价格低于最低竞拍价()
     *  8.此拍卖物品设置了封顶价()，您的价格不能超过封顶价
     *  9.您的价格()已经是当期最高价了，请稍后再竞价
     *  10. 您的自动竞价价格(代理金额)已经不是最高价格，自动竞价失效
     */
    public function doit() {

        // 1.判断 是否登录
        if (session('mid')) {

            // 2.判断 填写正确的价格
            $getPrice = intval($_GET['price']);
            ($getPrice <= 0) AND $this->ajaxReturn(array('status' => 2));

            // 3.判断 检查此拍卖信息是否存在
            $auction_id = intval($_GET['aid']);
            $field = array('startprice', 'eachprice', 'endprice', 'endtime', 'isok', 'needintegral', 'needmoney');
            $where = array('id' => $auction_id);
            $info = $this->Model->field($field)->where($where)->find();
            unset($field, $where);
            empty($info) AND $this->ajaxReturn(array('status' => 3));

            // 获取拍卖信息
            $startPrice = intval($info['startprice']); // 起拍价
            $eachPrice = intval($info['eachprice']); // 加价幅度
            $endPrice = intval($info['endprice']); // 封顶价
            $endTime = intval($info['endtime']); // 结束时间
            $isok = intval($info['isok']); // 是否结束
            $needIntegral = intval($info['needintegral']); // 所需积分
            $needMoney = intval($info['needmoney']); // 所需资金(保证金)
            unset($info);

            // 4.判断 是否已经过期
            $nowTime = time();
            $nowTime >= $endTime AND $this->ajaxReturn(array('status' => 4, 'endtime' => $endTime));
            unset($nowTime);

            // 5.判断 是否已经结束
            empty($isok) OR $this->ajaxReturn(array('status' => 5));
            unset($isok);

            // 6.判断 最低保证金或积分
            $m_id = session('mid'); // 用户ID
            $m_integral = 1000; // 积分
            $m_money = 700; // 保证金
            ($m_integral < $needIntegral && $m_money < $needMoney) AND $this->ajaxReturn(array('status' => 6, 'info' => array($m_integral, $m_money, $needIntegral, $needMoney)));
            unset($m_integral, $m_money, $needIntegral, $needMoney);
        } else {

            $this->ajaxReturn(array('status' => 1)); // 请先登录后再参加拍卖活动
        }

        // 从出价记录表里取出当前最高价格
        $maxRec = D('AuctionPriceRecord')->field(array('mid', 'price'))->where(array('aid' => $auction_id))->limit(1)->order('`price` DESC')->select();
        if (empty($maxRec)) {
            $maxPrice = $startPrice;
            $maxMid = 0;
        } else {
            $maxPrice = $maxRec[0]['price'];
            $maxMid = $maxRec[0]['mid'];
        }

        // 7.判断 您的价格低于最低竞拍价
        ($getPrice < ($maxPrice + $eachPrice)) AND $this->ajaxReturn(array('status' => 7, 'minprice' => ($maxPrice + $eachPrice)));

        // 8.判断 此拍卖物品设置了封顶价()，您的价格不能超过封顶价
        ($endPrice > $startPrice && $getPrice > $endPrice) AND $this->ajaxReturn(array('status' => 8, 'endprice' => $endPrice));

        // 9.判断 您的价格()已经是当期最高价了，请稍后再竞价
        ($m_id == $maxMid) AND $this->ajaxReturn(array('status' => 9, 'price' => $maxPrice));

        // 接收Ajax的传值
        $is_auto = intval($_GET['isauto']); // 是否自动竞价
        $is_hide = intval($_GET['ishide']); // 是否匿名
        $slogan = intval($_GET['slogan']); // 口号的标识
        // 选择自动竞价时 将托管的价格写入到自动竞价表
        if ($is_auto > 0) {

            // 获取自动竞价的最高托管价格
            $autoMaxPrice = D('AuctionAutoPrice')->where(array('aid' => $auction_id))->max('`price`');

            // 如果没有此拍卖信息的托管价格 或者 此价格高于已经存在的 那么写入此托管价格
            if (empty($autoMaxPrice) || (!empty($autoMaxPrice) && $getPrice > $autoMaxPrice)) {
                $data['aid'] = $auction_id; // 出价记录ID
                $data['mid'] = $m_id; // 用户ID
                $data['price'] = $getPrice; // 输入框的价格
                $data['time'] = time(); // 自动竞价的生效时间(用于相同价格不同用户时间优先的排在前面)
                $data['ip'] = get_client_ip(); // 地址(因为是自动出价，所以此IP会被连带到出价记录表)
                $data['ishide'] = $is_hide; // 是否隐藏(因为是自动出价，所以此标识被连带到出价记录表)
                D('AuctionAutoPrice')->add($data);
                unset($data);
            }

            // 10.判断 如果输入框的值 小于或等于 自动竞价表里最大的值，则提示您的自动竞价金额(代理金额)无效(不足)
            (!empty($autoMaxPrice) && $getPrice <= $autoMaxPrice) AND $this->ajaxReturn(array('status' => 10, 'automaxprice' => $autoMaxPrice));
        }

        // 查询托管表取最大值的价格的一组信息
        $arrAutoPrice = D('AuctionAutoPrice')->where(array('aid' => $auction_id, 'isopen' => 1))->order('`price` DESC, `time` ASC')->limit(1)->select();

        // 如果 不是自动竞价
        if ($is_auto == 0) {

            // 那么直接将传递的价格 写入到出价表
            $into['aid'] = $auction_id; // 出价记录ID
            $into['mid'] = $m_id; // 用户ID
            $into['price'] = $getPrice; //
            $into['time'] = time(); // 竞价时间
            $into['ip'] = get_client_ip(); // 地址
            $into['ishide'] = $is_hide; // 是否匿名
            $into['isauto'] = $is_auto; // 是否自动
            $into['slogan'] = $slogan; // 口号
            D('AuctionPriceRecord')->add($into);
            unset($into);

            // 且根据最高自动竞价信息里的用户判断是否是当前用户，只有是非当前用户才自动竞价(自增写入出价表)，反之则不操作
            if (!empty($arrAutoPrice) && $arrAutoPrice[0]['mid'] != $m_id && $arrAutoPrice[0]['price'] > $getPrice + $eachPrice) {
                $into['aid'] = $auction_id; // 出价记录ID
                $into['mid'] = $arrAutoPrice[0]['mid']; // 用户ID
                $into['price'] = $getPrice + $eachPrice; //
                $into['time'] = time(); // 竞价时间
                $into['ip'] = $arrAutoPrice[0]['ip']; // 地址
                $into['ishide'] = $arrAutoPrice[0]['ishide']; // 是否匿名
                $into['isauto'] = 1; // 是否自动
                $into['slogan'] = $arrAutoPrice[0]['slogan']; // 口号
                D('AuctionPriceRecord')->add($into);
                unset($into);
            }
        } else {

            // 如果 自动竞价表里托管价格最高的是当前用户，那么自动竞价生效(自增写入出价表)，反之则不操作
            if (empty($arrAutoPrice) || (!empty($arrAutoPrice) && $arrAutoPrice[0]['mid'] == $m_id)) {

                $into['aid'] = $auction_id; // 出价记录ID
                $into['mid'] = $m_id; // 用户ID
                $inPrice = ($maxPrice + $eachPrice >= $arrAutoPrice[0]['price'] ? $arrAutoPrice[0]['price'] : ($maxPrice + $eachPrice));
                $into['price'] = $inPrice; // 如果最高价+加价幅度>=托管价，则取值托管价，反之则自动增加的值
                $into['time'] = time(); // 竞价时间
                $into['ip'] = $arrAutoPrice[0]['ip']; // 地址
                $into['ishide'] = $arrAutoPrice[0]['ishide']; // 是否匿名
                $into['isauto'] = 1; // 是否自动
                $into['slogan'] = $arrAutoPrice[0]['slogan']; // 口号
                D('AuctionPriceRecord')->add($into);
                //$this->ajaxReturn(array('status' => 0, 'arr' => $into));
                unset($into);
            }
        }
        unset($arrAutoPrice);

        // 获取出价记录
        $arrList = D('AuctionPriceRecord')->where(array('aid' => $auction_id))->order('`price` DESC,`time` ASC')->select();
        $maxPrice = $arrList[0]['price'];

        // 如果有封顶价，且最高价达到封顶价，那么拍卖结束； 或者 当前时间达到结束时间，那么拍卖也结束
        if (($endPrice > 0 && $maxPrice >= $endPrice) || (time() >= $endTime)) {
            $this->Model->save(array('id' => $auction_id, 'isok' => 1, 'currentprice' => $maxPrice));
        } else {
            $this->Model->save(array('id' => $auction_id, 'currentprice' => $maxPrice));
        }
        
        // 根据MID循环获取用户昵称
        $newList = array();
        if(is_array($arrList)){
            foreach($arrList as $k => $v){
                $newList[$k]=$v;
                $newList[$k]['nickname'] = getNickName($v['mid']);
				$newList[$k]['ip'] = hideIp($v['ip']);
            }
            unset($arrList, $k, $v);
        }

        // 返回出价记录数据
        $this->ajaxReturn(array('status' => 11, 'list' => $newList, 'maxprice' => $maxPrice));
    }

    /**
     * 拍卖的评论
     */
    public function comment(){
        $content = I('content','','strip_tags');
        if (strlen($content)>5){
            $aid = I('aid');
            $model = D('AuctionComment');
            $model->content = $content;
            $model->auctionid = $aid;
            $model->createtime = time();
            $model->mid = session('mid');
            if($model->where(array('mid'=>$model->mid,'content'=>$content,'auctionid'=>$model->auctionid))->find()){
                $this->success('评论成功');
            }
            else{
                $model->isopen = 1;
                if ($model->add()){
                    $this->success('评论成功');
                }
                else{
                    $this->error("评论失败");
                }
            }
        }
        else{
            $this->error("评论不少于6个字符");
        }

    }


}
