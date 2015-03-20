<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Paimai\Controller;

use Paimai\Controller\PaimaiPublicController;

class FrontHotController extends PaimaiPublicController
{

    public function _initialize()
    {

        parent::_initialize();
        $auth = getLoginStatus();
        $this->assign('auth', $auth);
        //导航
        $this->nav="hot";

        /*$this->title="【全部拍品】全部拍品拍卖_价格_竞价_买卖_在线竞拍|中国艺术网";
        $this->keywords="全部拍品拍卖,全部拍品价格,全部拍品竞价全部拍品买卖,中国艺术网全部拍品拍卖";
        $this->desc="中国艺术网在线竞拍频道提供全部拍品拍卖、全部拍品价格、全部拍品竞价、全部拍品买卖,中国艺术网在线拍卖是国内专业的网上竞拍网站,提供众多的全部拍品拍卖等古玩藏品供你参考竞拍,安全交易,拍卖首选.";*/
        $this->title="和田玉拍卖_翡翠拍卖_南红玛瑙拍卖_钱币拍卖_瓷器拍卖_在线拍卖_中国艺术网在线竞拍";
        $this->keywords="和田玉拍卖,翡翠拍卖,南红玛瑙拍卖,钱币拍卖,瓷器拍卖,在线拍卖,中国艺术网在线竞拍";
        $this->desc="中国艺术网在线竞拍提供和田玉拍卖,翡翠拍卖,南红玛瑙拍卖,钱币拍卖,瓷器拍卖,书法拍卖 ,油画拍卖,绿松石拍卖,紫砂壶拍卖,寿山石拍卖,琥珀拍卖等,在线拍卖产品齐全,权威拍卖机构, 保证交易安全.
";

        $this->assign('title', '中国艺术网-拍卖');
    }

    /**
     * 列表信息
     */
    public function index()
    {
        
        $param=I('param','','strip_tags');
		//echo $param;
        $cat="";
        if(!empty($param)){
            $cat_field=array(
                "cat_id",
                "cat_name",
                "cat_pid",
                "cat_spell",
            );
            $cat_where=array(
                'cat_spell'=>$param,
            );
            //根据拼音查找本拼音对应分类的信息
            $cat_arr=M("PaimaiCategory")->field($cat_field)->where(array('cat_spell'=>$param))->find();
            $cat=$cat_arr['cat_id'];
        }else{
            $cat = I('get.cat');
        }
        //分配本拼音的id,这个在前台要用到,前台[加载更多]要用到这个cat_id
        $this->cat_id=$cat;
        //404页面，因为tp路由不到404，只能在这里定义
		if($param!="hot"){
			if(empty($cat)){
				header("Location: http://www.yishu.com/search/?m=content&c=index&a=error_404&model=paimai"); 
				exit();
			}
		}
        
        $this->cat_spell=$param;

    #top_ca
        //全局标签是不否是顶级分类
        $is_top_cat_tag=1;
        //判断是否是顶级分类
        if($cat_arr['cat_pid']!=0){//不是顶级分类,则查找顶级分类的名字和id,
            $is_top_cat_tag=0;
            $topcat_field=array(
                "*",
            );
            $topcat_where=array(
                'cat_id'=>$cat_arr['cat_pid'],
            );
            $topcat_arr=M('PaimaiCategory')->field($topcat_field)->where($topcat_where)->find();
            //这个分类的名
            $this->this_cat_name=$cat_arr['cat_name'];
            //这个分类的拼音
            $this->this_cat_spell=$cat_arr['cat_spell'];
            //分配顶级分类名
            $this->topcat_name=$topcat_arr['cat_name'];
            //分配顶级分类拼音
            $this->topcat_spell=$topcat_arr['cat_spell'];
            //顶级分类id
            $topcat_id=$topcat_arr['cat_id'];
            
        }else{ //如果是顶级分类则
            //分配顶级分类名
            $this->topcat_name=$cat_arr['cat_name'];
            //分配顶级分类拼音
            $this->topcat_spell=$cat_arr['cat_spell'];
            //顶级分类id
            $topcat_id=$cat_arr['cat_id'];
            $sub_cat_list_arr=M("PaimaiCategory")->where("cat_pid=".$cat_arr['cat_id'])->select();
            $sub_cat_list_str="";
            foreach ($sub_cat_list_arr as $k => $v) {
                $sub_cat_list_str.=$v['cat_id'].",";
            }
            $sub_cat_list_str=substr($sub_cat_list_str, 0,-1);
            //seo
            $cat_arr['cat_name']=empty($cat_arr['cat_name'])?'全部分类':$cat_arr['cat_name'];

        }
        
        if(!empty($topcat_id)){
            $other_topcat_where=array(
                'cat_id'=>array('NEQ',$topcat_id),
                'cat_pid'=>0,
            );
            $this->other_topcat=M("PaimaiCategory")->where($other_topcat_where)->limit(7)->select();
        }
        //p($this->other_topcat);
        
        $this->is_top_cat_tag=$is_top_cat_tag;

    #seo
        //如果是全部分类
        if($cat_arr['cat_name']=='全部分类'){
            $this->title="和田玉拍卖_翡翠拍卖_南红玛瑙拍卖_钱币拍卖_瓷器拍卖_在线拍卖_中国艺术网在线竞拍";
            $this->keywords="和田玉拍卖,翡翠拍卖,南红玛瑙拍卖,钱币拍卖,瓷器拍卖,在线拍卖,中国艺术网在线竞拍";
            $this->desc="中国艺术网在线竞拍提供和田玉拍卖,翡翠拍卖,南红玛瑙拍卖,钱币拍卖,瓷器拍卖,书法拍卖 ,油画拍卖,绿松石拍卖,紫砂壶拍卖,寿山石拍卖,琥珀拍卖等,在线拍卖产品齐全,权威拍卖机构, 保证交易安全.";
        }else{
            $this->title="【".$cat_arr['cat_name']."】".$cat_arr['cat_name']."拍卖_价格_竞价_买卖_在线竞拍|中国艺术网";
            $this->keywords=$cat_arr['cat_name']."拍卖,".$cat_arr['cat_name']."价格,".$cat_arr['cat_name']."竞价".$cat_arr['cat_name']."买卖,中国艺术网".$cat_arr['cat_name']."拍卖";
            $this->desc="中国艺术网在线竞拍频道提供".$cat_arr['cat_name']."拍卖、".$cat_arr['cat_name']."价格、".$cat_arr['cat_name']."竞价、".$cat_arr['cat_name']."买卖,中国艺术网在线x拍卖是国内专业的网上竞拍网站,提供众多的".$cat_arr['cat_name']."拍卖等古玩藏品供你参考竞拍,安全交易,拍卖首选.";
        }
    #根据顶级分类的id,查找顶级分类下的子分类
        $topsubcat_field=array(
            "cat_id",
            "cat_name",
            "cat_spell",
        );
        $topsubcat_where=array(
            "cat_pid"=>$topcat_id,
            "cat_show_in_front"=>1,
            "cat_id"=>array("NEQ",$cat_arr['cat_id']),
        );
        $this->topsubcat_arr=M('PaimaiCategory')->field($topsubcat_field)->where($topsubcat_where)->select();
    #调用专场
        $special_where = array(
            'special_isshow' => 1,
            //'special_isdelete' => 0,
            'special_starttime' => array('between',array(strtotime(date("Y-m-d", time()))-2*3600-1, strtotime(date("Y-m-d", time()))+22*3600))
            );
        $Special=M('PaimaiSpecial')->where($special_where)->limit("8")->order("special_order desc,special_id desc")->select();

         foreach ($Special as $k => $v) {
            $aa = explode('——',$v['special_name']);
            $Special[$k]['special_name'] = $aa[1];
         }
         $this->Special = $Special;
        //print_r($this->Special);die;
    #where
        $where=array(
            'goods_isshow'=>1,
            );
        //被选中的
        $this->selected_cat=$cat;
        #分类拼接where
        if(!empty($sub_cat_list_str)){
            $where['goods_catid']=array('IN',$sub_cat_list_str);
        }else{
            if($cat==17){//分类ID为17的是其它
                $where['goods_catid']=0;
            }elseif(!empty($cat)){//如果不为空则有分类
                $where['goods_catid']=$cat;
            }
            //如果为空则为全部
        }
        #状态
        $status = I('status',0,'intval');
        //[加载更多要用到]
        $this->status = $status;
        //如果匹配下面条件则进行,匹配不到则默认为空
        if ($status == 3) {//未开始
            $where['goods_starttime']=array('GT',time());
        }
        if ($status == 1) {//正在进行
            $where['goods_starttime']=array('LT',time());
            $where['goods_endtime']=array('GT',time());
        }
        if ($status == 2) {//已经结束
            $where['goods_endtime']=array('LT',time());
        }

    #分类
        $category_where=array(
            'cat_show_in_front'=>1,
            'cat_pid'=>0,
        );
        $category = M("PaimaiCategory")->where($category_where)->order("cat_id asc")->select();
        //分配分类
        $this->category = $category;
       
    #排序order
        $order = "";
        $order .= "goods_id desc";
       
    #field
        $field = array(
            "recordid",
            "goods_id",
            "goods_name",
            "goods_sn",
            "goods_nowprice",
            "goods_endtime",
            "goods_bidtimes",
            "goods_starttime",
            );

    #组织sql进行分页
        //每页显示的数量
        $prePage = 12;
        //分配每页的数量[加载更多]用到
        $this->prepage=$prePage;
        $p = I('p',1,'intval');
       
        $lists = M('PaimaiGoods')->field($field)->where($where)->order($order)->page($p . ',' . $prePage)->select();
        
        foreach ($lists as $k => $v) {
            $lists[$k]['goods_name']=substr_CN($v['goods_name'],17);
            $lists[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
            $lists[$k]['goods_endtime'] = date("Y-m-d H:i:s",$v['goods_endtime']);
            $lists[$k]['leave_time'] = $v['goods_endtime']-time();
        }
       // p($lists);
        
        $this->assign("lists", $lists);
        
        //总条数
        $total_num = M('PaimaiGoods')/*->join("yishu_paimai_goodsattr on yishu_paimai_goodsattr.goodsattr_goodsid=yishu_paimai_goods.goods_id")*/->field($field)->where($where)->select();
        //$this->total_num
        $this->total_num=count($total_num);
        
        //ajax请求状态
        if(!empty($status)){
            //p($lists);
            //这种状态下的商品数量
            $lists[]['count']=$this->total_num;
            exit(json_encode($lists));
        }

        $Page = new \Think\Page($this->total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数

        //$Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('END', '尾页');

        //$Page->setConfig('theme', '%UP_PAGE% %FIRST% %LINK_PAGE% %DOWN_PAGE% %END%');
       
        $show = $Page->show(); // 分页显示输出
        #根据seo把分页链接修改掉


         //读取分类
        $cate_where = array(
            'cat_pid' => array('NEQ', '0'),
            'cat_isshow' => '1',
            );
        $this->cate = M('paimai_category')->field('cat_id,cat_name,cat_spell')->where($cate_where)->select();
        //接受参数,并简单修改
        $suffix=$_SERVER['QUERY_STRING'];
        //去除开头两个字符串,并替换&&为?
        $suffix=str_replace("&&","?&",substr($suffix,3));
        //echo $suffix."<br/>";
        //去除分页 p=3中的数字
        $suffix=preg_replace("/&p=(\d+)*/","",$suffix);
        
        $show=preg_replace("/(.*)Paimai\/FrontHot\/index(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);
        
        $this->assign('page', $show); // 赋值分页输出*/

        $this->display('Front:hot4');
    }

    /**
     * ajax返回列表数据
     */
    public function lists()
    {

        $currentpage = intval($_GET['page']);
        if ($currentpage <= 0) {
            return false;
        }

        // 当前页
        //$currentpage = $page + 1;

        // 计算起始值和显示页数
        $limit = $currentpage == 1 ? $this->size : (($currentpage - 1) * $this->size) . ',' . $this->size;

        // 联表查询
        $lists = $this->Model->query('SELECT ' . $this->field . ' FROM ' . $this->table . ' WHERE ' . $this->where . ' ORDER BY ' . $this->order . ' LIMIT ' . $limit);

        // 获取每个拍卖信息的拍卖次数
        foreach ($lists as $rs) {
            $arrCount[$rs['id']] = getPriceCount($rs['id']);
        }

        //$this->display('Front:hot');

        $this->ajaxReturn(array('status' => 3, 'lists' => $lists, 'counts' => $arrCount));
    }

    /*
  * 前台属性筛选
  * 传入attr_id返回对应的商品属性值
  */
    public function get_attr_value()
    {
        if (!IS_AJAX) $this->error("你请求的页面不存在");
        $goodsattr = M('PaimaiGoodsattr')->field("goodsattr_id,goodsattr_value")->where("goodsattr_attrid=" . $_GET['goodsattr_attrid'])->select();
        foreach ($goodsattr as $k => $v) {
            if ($v['goodsattr_value'] == "") {
                unset($goodsattr[$k]);
            }
        }
        //调用本控制器方法去除返回属性值中的重复值
        echo json_encode($this->array_unique_fb($goodsattr));
    }

    /*
     * (算法)
     * 二维数组去除重复值
     */
    public function array_unique_fb($array2D)
    {
        $arr = array();
        //把二维数组转成一维
        foreach ($array2D as $k => $v) {
            $arr[$v['goodsattr_id']] = $v['goodsattr_value'];
        }
        //去除一维数组重复值
        $temp = array_unique($arr); //去掉重复的字符串,也就是重复的一维数组
        //生成二维数组
        $tag = 0;
        foreach ($temp as $k => $v) {
            $arrarr[$tag]['goodsattr_id'] = $k;
            $arrarr[$tag]['goodsattr_value'] = $v;
            $tag++;
        }
        return $arrarr;
    }
     /*加载更多*/
    public function ajax_loadmore(){

        if(!IS_AJAX)$this->error("你请求的页面不存在");
        //从$p为从哪个开始
        $p=I('p',0,'intval');
        //请求的数量
        $num=I('num',0,'intval');

        $cat_id=I('id',0,'intval');
        $status=I('status',0,'intval');

    #查找这个分类是不是顶级分类
        $cat_arr=M("PaimaiCategory")->find($cat_id);
        if(!empty($cat_arr)&&$cat_arr['cat_pid']==0){
            $subcat_id_str="";
            $subcat_arr=M("PaimaiCategory")->where("cat_pid=".$cat_id)->select();
            foreach ($subcat_arr as $k => $v) {
                $subcat_id_str.=$v['cat_id'].",";
            }
            $subcat_id_str=substr($subcat_id_str, 0,-1);
        }

        $catgoods_field=array(//字段后期要优化
            "recordid",
            "goods_id",
            "goods_name",
            "goods_sn",
            "goods_nowprice",
            "goods_endtime",
            "goods_bidtimes",
            "goods_starttime",
        );
         
    #是不是顶级分类判断:
        $catgoods_where['goods_isshow']=1;
        if(empty($subcat_id_str)){//如果subcat_id_str为空则不是顶级分类
            if(!empty($cat_id)){
                //如果$cat_id=17则为其它,把$cat_id置为0
                $cat_id=$cat_id==17?0:$cat_id;
                //追加where
                $catgoods_where['goods_catid']=$cat_id;
            }
            //如果传递过来的$cat_id=0,则为全部

        }else{//如果不是顶级分类
            //如果subcat_id_str不为空则为顶级分类,顶级分类会查找字符串
            $catgoods_where=array(
                //追加where
                'goods_catid'=>array("IN",$subcat_id_str),
            );
        }
        
    #判断状态,追加where条件
        if($status==3){//未开拍
            $catgoods_where['goods_starttime']=array('GT',time());
        }else if($status==1){//正在进行
            $catgoods_where['goods_starttime']=array('LT',time());
            $catgoods_where['goods_endtime']=array('GT',time());
        }elseif($status==2){//结束
            $catgoods_where['goods_endtime']=array('LT',time());
        }

    #limit
        $catgoods_limit=$p.",".$num;
        
        $lists = M('PaimaiGoods')->field($catgoods_field)->where($catgoods_where)->order("goods_id desc")->limit($catgoods_limit)->select();
        if(empty($lists)){//如果为空则返回状态为0
            $data['status']=0;
            exit(json_encode($data));
        }
        foreach ($lists as $k => &$v) {
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
            $v['leave_time']=$v['goods_endtime']-time();
            if($v['goods_starttime']>time()){
                //未开始
                $v['status']=1;
                $v['price']="<span class='price_type'>起拍价</span><b class='font_color green'>￥".$v['goods_nowprice']."</span></b>";
            }elseif($v['goods_starttime']<time()&&$v['goods_endtime']>time()){
                //正在进行
                $v['status']=2;
                $v['price']="<span class='price_type'>当前价</span><b class='font_color red'>￥".$v['goods_nowprice']."</span></b>";
            }elseif($v['goods_endtime']<time()){
                //已经结束
                $v['status']=3;
                $v['price']="<span class='price_type'>成交价</span><b class='font_color black'>￥".$v['goods_nowprice']."</span></b>";
            }
        }
        exit(json_encode($lists));
    }

}
