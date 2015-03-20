<?php

    namespace Seckill\Controller;
    use Seckill\Controller\SeckillController;

    class SpecialListController extends SeckillController{


        //抢拍专场的页面，列出所有的此专场的商品
        public function index(){
              $special_id = I("get.id", 0, "intval");
             $this->special_id=$special_id;

            $where['skspecial_id'] = $special_id;
            $where['skspecial_isshow'] = 0;
            //查找专场
            $special = M("SeckillSpecial")->where($where)->find();

            //专场不存在则返回
            if(empty($special)){
                $this->error('数据错误，没有该次抢拍专场!');
            }

            //专场点击量加1
            M('SeckillSpecial')->where($where)->setInc('skspecial_hits');

            //p($special);

        #seo,如果没有seo相关信息，则默认
            //seo标题
            if(!empty($special['skspecial_seotitle'])){
                $this->title=$special['skspecial_seotitle']."_抢拍专场_中国艺术网在线抢拍";
            }else{
                $this->title=$special['skspecial_name']."_抢拍专场_中国艺术网在线抢拍";
            }
    	
    	//seo关键字
            if(!empty($special['skspecial_keywords'])){
                $this->keywords=$special['skspecial_keywords'].",抢拍专场";
            }else{
                $this->keywords=$special['skspecial_keywords'].",抢拍专场";
            }
    	
        //seo描述
            if(!empty($special['special_seodesc'])){
                $this->desc="中国艺术网在线抢拍隆重上线".$special['special_seodesc']."抢拍专场活动，玩家可以在".$special['special_seodesc']."抢拍专场上拍卖到最新最有价值的藏品，数量有限，先到先得，中国艺术网在线竞拍安全交易，值得信赖。";
            }else{
                $this->desc="中国艺术网在线抢拍隆重上线".$special['special_name']."抢拍专场活动，玩家可以在".$special['special_name']."抢拍专场上拍卖到最新最有价值的藏品，数量有限，先到先得，中国艺术网在线竞拍安全交易，值得信赖。";
            }
    	

            $special['thumb'] = D('Content/Document')->getPic($special['recordid'], 'thumb');
            

            $this->assign("special", $special);
        //p($special);
        //分配当前时间
            $this->assign("shijian", time());
		
            $where['goods_id'] = array("not in", $special_id);
            $this->otherspecial = M("SeckillSpecial")->where($where)->find();


        /*******************order开始**************************/
        //按数量排序
            $order = "";
            $count = $_GET['count'];
            $this->count = $count;
            $this->filter_count = $count;
            if (isset($count) && $count == 1) { //降序
                $order .= "goods_bidtimes desc,";
            }
            if (isset($count) && $count == 2) { //升序
                $order .= "goods_bidtimes asc,";
            }
        //按价格排序
            $price = $_GET['price'];
            $this->price = $price;
            $this->filter_price = $price;
            if (isset($price) && $price == 1) {
                $order .= "goods_nowprice desc,";
            }
            if (isset($price) && $price == 2) {
                $order .= "goods_nowprice asc,";
            }
            //$order .= "goods_id desc ";
            $order = "skgoods_id desc ";
        /* echo "where条件".$where."<br/>";
         echo "order条件:".$order."<br/>";*/

        /*************order结束***************/
        //查找本专场下的商品

        //$this->goods=M('PaimaiGoods')->where($where2)->order($order)->select();

            $p = isset($_GET['p']) ? $_GET['p'] : 1;
        //每页显示的数量
            $prePage = 9;
            $where2['skgoods_specialid'] = $special_id;
            $where2['skgoods_isshow'] = 0;
            //$list = M('PaimaiGoods')->where($where2)->page($p . ',' . $prePage)->order($order)->select();
            //不要分页
            $list = M('SeckillGoods')->where($where2)->order($order)/*->limit(15)*/->select();
		//不要分页结束
            foreach ($list as $k => $v) {
                $list[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
        }

            $page_total_count=M('SeckillGoods')->where($where2)->count();
            $Page = new \Think\Page($page_total_count, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('header', '共%TOTAL_ROW%条记录');
            $Page->setConfig('prev', '上一页');
            $Page->setConfig('next', '下一页');
            $Page->setConfig('first', '首页');
            $Page->setConfig('last', '尾页');

            $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $show = $Page->show(); // 分页显示输出
            //p($show);
            $suffix=$_SERVER['QUERY_STRING'];
            //echo $suffix."<br/>";
            //去除开头两个字符串,并替换&&为?
            $suffix=str_replace("&&","?&",substr($suffix,3));
            //echo $suffix."<br/>";
            //去除分页 p=3中的数字
            $suffix=preg_replace("/&p=(\d+)*/","",$suffix);
            
            $show=preg_replace("/(.*)Seckill\/GoodsShow\/index(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/Ui","$1".$suffix."&p=$3",$show);
            $this->assign('page', $show); // 赋值分页输出
            //分配body css
            $this->assign("css", 'auction-ol auction-topic');
            $this->shijian = time();
            //p($this->goods);
            //$this->display('Front:topic4');
            $this->assign("lists", $list);
            $this->display('FrontPage:specialgoods');
        }
    
    }
