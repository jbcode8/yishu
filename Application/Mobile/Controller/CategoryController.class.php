<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖信息_[前端]_控制器(CURD)
// +----------------------------------------------------------------------
namespace Mobile\Controller;

use Mobile\Controller\MobileController;

class CategoryController extends MobileController
{
    public function _initialize()
    {
        parent::_initialize();
		$this->assign('title', '中国艺术网-拍卖');
        $this->keywords = "和田玉拍卖,翡翠拍卖,南红玛瑙拍卖,钱币拍卖,瓷器拍卖,在线拍卖,中国艺术网在线竞拍";
        $this->desc = "中国艺术网在线竞拍提供和田玉拍卖,翡翠拍卖,南红玛瑙拍卖,钱币拍卖,瓷器拍卖,书法拍卖 ,油画拍卖,绿松石拍卖,紫砂壶拍卖,寿山石拍卖,琥珀拍卖等,在线拍卖产品齐全,权威拍卖机构, 保证交易安全.";
		
    }

    /**
     * 列表信息
     */
    public function index()
    {
        $param=I('param','','strip_tags');
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
		$this->cat_name = $this->page_title = $cat_arr['cat_name'];
        //404页面，因为tp路由不到404，只能在这里定义
        if(empty($cat)){
            header("Location: http://www.yishu.com/search/?m=content&c=index&a=error_404&model=paimai"); 
            exit();
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
        $page_num = 10;
		$this->page_num = $page_num;

        $p = I('p',1,'intval');

		$lists = M('PaimaiGoods')->field($field)->where($where)->order($order)->limit(($p-1)*$page_num . ',' . $page_num)->select();
		
		foreach ($lists as $k => $v) {
			$lists[$k]['goods_name'] = substr_CN($lists[$k]['goods_name'],10);
            $lists[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb',$v['goods_id']);
        }

		//ajax请求状态
        if(!empty($status)){
			$this->ajaxReturn($lists);
        }

		$this->assign("lists", $lists);

        $this->display('Front:category');
    }
}
