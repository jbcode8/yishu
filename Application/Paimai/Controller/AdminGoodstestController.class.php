<?php
/**
 * Created by PhpStorm.
 * User: luxiaofei
 * Date: 14-8-8
 * Time: 下午1:51
 */

namespace Paimai\Controller;


use Admin\Controller\AdminController;

class AdminGoodstestController extends AdminController
{
    public function _initialize()
    {
        parent::_initialize();
        //管理员uid
        $this->adminuid=$_SESSION['admin_auth']['uid'];
        //p($this->adminuid);
    }
    public function index()
    {
        //v($_SESSION['admin_auth']['uid']);
        //v($goods_name);
        $field = array(
            "*",
            "case
                when goods_isshow=0 then '不显示'
                else '显示' end"
            => "goods_isshow",
            "case
                when goods_starttime > unix_timestamp() then '未开拍'
                when unix_timestamp() < goods_endtime and unix_timestamp() > goods_starttime then '正在进行中'
                when unix_timestamp() > goods_endtime then '已经结束'
                else '时间待定' end"
            => "goods_status",
        );
    #接受参数
        $where="";
        //如果不是超级管理员则只显示本管理员的商品
        if($this->adminuid!=1){
            $where.="goods_adminuid=".$this->adminuid." and ";
        }
        $where.="goods_isdelete=0 and ";

        //商品分类筛选
        $cat_id=I('cat',0,'intval');
        if(!empty($cat_id)){
            $where.="goods_catid=$cat_id and ";
        }
        //商品分类列表
        $this->category = M('PaimaiCategory')->field("cat_id,cat_name")->where("cat_isshow=1 and cat_pid<>0")->order("cat_id asc")->select();
        $cat_str="";
        foreach($this->category as $k=>&$v){
            if($cat_id==$v['cat_id']){
                $cat_str.="<option selected='selected' value=".$v['cat_id'].">".$v['cat_name']."</option>";
            }else{
                $cat_str.="<option value=".$v['cat_id'].">".$v['cat_name']."</option>";
            }
        }
        $this->cat_str=$cat_str;

        //商品名称筛选
        $goods_name=I('goodsname');
        //p($goods_name);
        if(!empty($goods_name)){
            $where.="goods_name like '%".$goods_name."%' and ";
            $this->goods_name=$goods_name;
        }
        //商品货号筛选
        $goods_sn=I('goodssn');
        if(!empty($goods_sn)){
            $where.="goods_sn like '%".$goods_sn."%' and ";
            $this->goods_sn=$goods_sn;
        }
        //商品ID筛选
        $goods_id=I('goodsid');
        if(!empty($goods_id)){
            $where.="goods_id in (".$goods_id.") and ";
            $this->goods_id=$goods_id;
        }
        //专场ID筛选
        $special_id=I('special');
        if(!empty($special_id)){
            $where.="goods_specialid in (".$special_id.") and ";
            $this->goods_specialid=$special_id;
        }
        //上传者筛选
       $adminname=I('adminname');
        if(!empty($adminname)){
            $this->adminname=$adminname;
        #得到传过来的上传者的用户名
            $admin_where="nickname like'%".$adminname."%' and ";
            $admin_where=substr($admin_where, 0,-4);
            $admin_arr=M('Admin')->where($admin_where)->select();
            $adminuid_str="";
            foreach($admin_arr as $k=>$v){
                $adminuid_str.=$v['uid'].",";
            }
            $adminuid_str=substr($adminuid_str, 0,-1);
            
            $where.="goods_adminuid in (".$adminuid_str.") and ";
        }
        
        $where=substr($where, 0,-4);
        //p($where);
		//分页
		$p = I('p',1,'intval');
        $this->p=$p;
		//每页显示的数量
        $prePage = 15;
		//分页商品
		$this->lists = M("PaimaiGoods")->field($field)->page($p . ',' . $prePage)->where($where)->order("goods_id desc")->select();
		//分页商品总数
		$total_num=M("PaimaiGoods")->where($where)->count();
        
		$Page = new \Think\Page($total_num, $prePage); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header', '共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show(); // 分页显示输出

        $suffix=$_SERVER['QUERY_STRING'];
        //去除开头两个字符串,并替换&&为?
        $suffix=str_replace("&&","?&",substr($suffix,3));
        //去除分页 p=3中的数字
        $suffix=preg_replace("/&p=(\d+)*/","",$suffix);
        $show=preg_replace("/(.*)Paimai\/AdminGoods\/index(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);

        $this->assign('page', $show); // 赋值分页输出
        
        $this->display('indextest');
    }


    public function add()
    {
        //商品分类列表
        $this->category = M('PaimaiCategory')->field("cat_id,cat_name")->where("cat_isshow=1")->order("cat_id desc")->select();
        //商品类型列表
        $this->goodstype = A("AdminAttribute")->getGoodsType();
        //商品品牌
        $this->brand = M("PaimaiBrand")->field("brand_id,brand_name")->where("brand_isshow=1")->order("brand_id desc")->select();
        //拍卖专场
        $this->special = M('PaimaiSpecial')->field("special_id,special_name")->where("special_isshow=1")->order("special_id desc")->select();
        /*商品口号*/
        $this->slogan = M("PaimaiSlogan")->field("slogan_id,slogan_name")->where("slogan_isshow=1")->order("slogan_id desc")->select();
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        hook('uploadComplete', $param);
        $this->display('addtest');
    }

	private function _indeximg($img){ 
		$upload = new \Think\Upload();
		$upload->maxSize   =     1024 * 1024 * 2;
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
		$upload->savePath  =     '../Public/upload/Paimai/';
		$upload->subName    =    array('date','Ymd');
		// 上传文件     
		$info   =   $upload->uploadOne($img);    
		if(!$info) {
			// 上传错误提示错误信息        
			return $upload->getError();
		}else{
			// 上传成功        
			return $info;
		}
	}
	//复制
	public function copy(){
		$ids = explode(',',substr(I('get.ids'), 0, -1));
		if($ids){
			foreach ($ids as $key => $v) {
				$data = M('PaimaiGoods')->where(array('goods_id'=>$v))->find();
				//print_r($data);die;
				unset($data['goods_isshow']);
				$data['goods_isshow'] = 0;
				unset($data['goods_nowprice']);
				$data['goods_nowprice'] = 1.00;
				unset($data['goods_specialid']);
				unset($data['goods_id']);
				unset($data['goods_sn']);
				$data['goods_sn'] = $this->createGoodsSn();
				unset($data['goods_status']);
				$data['goods_status'] = 0;
				unset($data['goods_bidtimes']);
				$data['goods_bidtimes'] = 0;
				unset($data['goods_successid']);
				$data['goods_successid'] = 0;
				unset($data['goods_bidcreatetime']);
				$data['goods_bidcreatetime'] = 0;
				unset($data['goods_bidid']);
				$data['goods_bidid'] = 0;
				unset($data['goods_starttime']);
				$data['goods_starttime'] = 0;
				unset($data['goods_endtime']);
				$data['goods_endtime'] = 0;
				unset($data['goods_createtime']);
				$data['goods_createtime'] = 0;
				unset($data['goods_updatetime']);
				$data['goods_updatetime'] = 0;

				$id = M('PaimaiGoods')->add($data);
				$imgs = M('PaimaiGallery')->where(array('goods_id'=>$v))->select();
				if(!empty($imgs)){
					foreach($imgs as $vb){
						unset($vb['img_id']);
						unset($vb['goods_id']);
						$vb['goods_id'] = $id;
						M('PaimaiGallery')->add($vb);
					}
				} 
			}
			$this->success('复制成功');
		}else{
			$this->error('复制出错');
		}
	}
    public function insert()
    {//print_r($_POST);die;
        //p($_POST);
		if(I('post.goods_cost') == 0){
			$this->error("拍品成本价不得为0");
		}
		$img = $this->_indeximg($_FILES['index_imgs']);
		if($img){
			$index_img = $img['savepath'].','.$img['savename'];
		}else{
			$index_img = '';
		}
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        #  把一些用到的数组暂存起来,防止create过滤掉
        $slogan = array_unique($_POST['slogan']); //口号数组去除重复
        //p($slogan);
        $goods_att_key = $_POST['goods_att_key']; //属性id数组
        $goods_att_val = $_POST['goods_att_val']; //属性值数组
        //判断加价幅度
        //$goods_everyprice=I("goods_everyprice",0,"intval");

        //if(I())

        $goods = D("PaimaiGoods");
		
        #对要入库的goods字段进行名种验证,各种填充
        if ($goods->create()) {
			if($index_img){
				$goods->index_img = $index_img;
			}
            //本商品是由哪个管理员上传的
            $goods->goods_adminuid=$this->adminuid;
            //调用自身方法创建唯一货号并对create后的字段进行填加
            $goods->goods_sn = $this->createGoodsSn();
            //新增商品现在价格==起始价格
            $goods->goods_nowprice = $goods->goods_startprice;
            //根据规则函数生成保证金
            $goods->goods_needmoney=getneedmoney($goods->goods_nowprice);
            //如果自动竞价,根据规则自动生成加价幅度
            if($_POST['goods_everypricestyle']==1){
                $goods->goods_everyprice=geteveryprice($goods->goods_nowprice);
            }
            //添加后返回刚生成的商品id
            if ($goods_id = $goods->add()) {
                #    组织数据 写入商品-口号对照表
                $goodsslogan = M("PaimaiGoodsSlogan");
                $goodssloganArr['gs_goodsid'] = $goods_id;
                foreach ($slogan as $v) {
                    //如果口号的id为0就是没有选择口号,退出本次循环
                    if ($v == 0) continue;
                    $goodssloganArr['gs_sloganid'] = $v;
                    $goodsslogan->data($goodssloganArr)->add();
                }
                //p($goodssloganArr);
                #    组织数据 写入商品-属性对照表
                $goodsattr = M('PaimaiGoodsattr');
                $goodsattrArr['goodsattr_goodsid'] = $goods_id;
                for ($i = 0; $i < count($goods_att_val); $i++) {
                    //如果没有选择属性就退出本次循环
                    if($goods_att_val[$i]==0) continue;
                    $goodsattrArr['goodsattr_attrid'] = $goods_att_key[$i];
                    $goodsattrArr['goodsattr_value'] = $goods_att_val[$i];
                    $goodsattr->data($goodsattrArr)->add();
                }
                $this->success("拍品信息写入成功");
            } else {
                $this->error("拍品基本信息写入失败");
            }
        } else {
            $this->error($goods->getError());
        }
    }

    #编辑数据
    public function edit()
    {	
        $goods_id = I("get.goods_id", "0", "intval");
        $this->goods = M("PaimaiGoods")->where("goods_id=" . $goods_id)->find();
        //商品分类列表
        $this->category = M('PaimaiCategory')->field("cat_id,cat_name")->where("cat_isshow=1")->order("cat_id desc")->select();
        //商品类型列表
        $this->goodstype = A("AdminAttribute")->getGoodsType();
        //商品品牌
        $this->brand = M("PaimaiBrand")->field("brand_id,brand_name")->where("brand_isshow=1")->order("brand_id desc")->select();
        //拍卖专场
        $this->special = M('PaimaiSpecial')->field("special_id,special_name")->where("special_isshow=1")->order("special_id desc")->select();
        //商品口号
        $this->slogan = M("PaimaiSlogan")->field("slogan_id,slogan_name")->where("slogan_isshow=1")->order("slogan_id desc")->select();
        //商品口号对照表yishu_paimai_goods_slogan,本商品对应的口号
        $this->goodsslogan = M("PaimaiGoodsSlogan")->where("gs_goodsid=" . $goods_id)->select();
        /* p($this->goodsslogan);*/
        //p($this->goods);
        $this->display('edittest');
    }


    /*
     * 修改商品
     */
    public function update()
    {
        //v($_POST);
		//print_r($_POST);die;
		$img = $this->_indeximg($_FILES['index_imgs']);
		if($img){
			$index_img = $img['savepath'].','.$img['savename'];
		}
		 
        $goods_id = I('post.goods_id', '0', 'intval');
        //先把之前的商品-口号对照表yishu_paimai_goods_slogan的口号删除
        M("PaimaiGoodsSlogan")->where("gs_goodsid")->delete();
        //把商品-属性对照表yishu_paimai_goodsattr中对应的商品属性goodsattr_isshow字段置为0然后把goodsattr_isdelete字段置为1
        M("PaimaiGoodsattr")->where("goodsattr_goodsid=" . $goods_id)->setField(array('goodsattr_isshow' => 0, 'goodsattr_isdelete' => 1));
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        $param['addonDesc'] = I('post.addonDesc');
        hook('uploadComplete', $param);
        #  把一些用到的数组暂存起来,防止create过滤掉
        $slogan = array_unique($_POST['slogan']); //口号数组去除重复
        $goods_att_key = $_POST['goods_att_key']; //属性id数组
        $goods_att_val = $_POST['goods_att_val']; //属性值数组
        //如果拍卖次数为0则可以修改现价 
		if(I('goods_bidtimes',0,'intval')==0){
				$_POST['goods_nowprice']=I('goods_startprice');
		}
        $goods = D("PaimaiGoods");//echo $index_img;
		//$goods->startTrans();
        #对要入库的goods字段进行名种验证,各种填充
		if(empty($_FILES['index_imgs']['name'])){
			unset($_POST['index_img']);
		}
        if ($goods->create()) {
			if(! empty($_FILES['index_imgs']['name'])){
				$goods->index_img = $index_img;
			}
            //本商品是由哪个管理员上传的
            //$goods->goods_adminuid=$this->adminuid;
			//根据规则函数生成保证金
            $goods->goods_needmoney=getneedmoney(I("goods_nowprice"));
            //如果自动竞价,根据规则自动生成加价幅度
            if($_POST['goods_everypricestyle']==1){
                $goods->goods_everyprice=geteveryprice($goods->goods_nowprice);
            }
			$goods->goods_sellername=I("goods_sellername");
            //更新数据
			
            if ($goods->where("goods_id=" . $goods_id)->save()) {
                #    组织数据 写入商品-口号对照表
                $goodsslogan = M("PaimaiGoodsSlogan");
                $goodssloganArr['gs_goodsid'] = $goods_id;
                foreach ($slogan as $v) {
                    //如果口号的id为0就是没有选择口号,退出本次循环
                    if ($v == 0) continue;
                    $goodssloganArr['gs_sloganid'] = $v;
                    $goodsslogan->data($goodssloganArr)->add();
                }
                #    组织数据 写入商品-属性对照表
                $goodsattr = M('PaimaiGoodsattr');
                $goodsattrArr['goodsattr_goodsid'] = $goods_id;
                //删除原来属性
                $goodsattr->where(array('goodsattr_goodsid'=>$goods_id))->delete();
                for ($i = 0; $i < count($goods_att_val); $i++) {
                    $goodsattrArr['goodsattr_attrid'] = $goods_att_key[$i];
                    $goodsattrArr['goodsattr_value'] = $goods_att_val[$i];
                    $goodsattr->data($goodsattrArr)->add();
                }
                $this->success("拍品信息写入成功");
            } else {
				//$goods->rollback();
                $this->error("拍品基本信息写入失败");
            }
			//$goods->commit();
        } else {
            $this->error($goods->getError());
        }
        //p($_POST);
    }

    #删除数据
    public function delete()
    {
        $goods_id = I('post.goods_id', '0', 'intval');
        //先把之前的商品-口号对照表yishu_paimai_goods_slogan的口号删除

        M("PaimaiGoodsSlogan")->where("gs_goodsid=" . $goods_id)->delete();

        //把商品-属性对照表yishu_paimai_goodsattr中对应的商品属性goodsattr_isshow字段置为0然后把goodsattr_isdelete字段置为1
        M("PaimaiGoodsattr")->where("goodsattr_goodsid=" . $goods_id)->setField(array('goodsattr_isshow' => 0, 'goodsattr_isdelete' => 1));

        //删除商品
        if (!M("PaimaiGoods")->where("goods_id=" . $_GET['goods_id'])->delete()) {
            $this->error("商品删除失败");
           
        }
        $this->success("商品删除成功");


    }
#一些方法
    /*
     * 创建唯一商品货号
     */
    private function createGoodsSn()
    {
        //创建唯一商品货号
        $goods = M("PaimaiGoods");
        $goods_sn = 'YS' . date("Ymd") . mt_rand(10000, 99999);
        return $goods->where("goods_sn=$goods_sn")->count() ? $this->createGoodsSn() : $goods_sn;
    }
    /*
    用户跟踪明细
    */
    public function goodsuserdetail(){
        $this->goods_id=I('goods_id',0,'intval');
        $bidstatus_field=array(
            "*",
            "case
                when bidstatus_status=1 then '点击出价,然后点击取消'
                when bidstatus_status=2 then '点击出价,然后点击确认'
                when bidstatus_status=3 then '提示用户去初次充值,没有出价成功'
                when bidstatus_status=4 then '提示用户去再次充值,没有出价成功'
                when bidstatus_status=5 then '有最高出价提示用户再次出价'
                when bidstatus_status=9 then '出价成功'
                else '未知状况' 
            end"
            =>"style",
            );
        $bidstatus_where=array(
            'bidstatus_gid'=>$this->goods_id,
            );
        $this->bidstatus_arr=M("PaimaiBidstatus")->field($bidstatus_field)->where($bidstatus_where)->order("bidstatus_time desc")->select();
        //p($this->bidstatus_arr);
        $this->display();
    }
    #ajax
    /*商品添加页面
     * 传入商品类型id
     * 返回json格式的属性列表
     */
    public function ajax_getattrbygoodstype()
    {
        $map['attr_isshow'] = 1;
        $map['attr_goodstypeid'] = I('get.goodstype_id', 0, 'intval');
        //获得该分类下的商品属性名
        $data = M("PaimaiAttribute")->where($map)->select();
        for($i=0;$i<count($data);$i++){
            //根据每个名选择该属性名下的的属性值
            $goods_attr=M('PaimaiGoodsattr')->field("goodsattr_id,goodsattr_attrid,goodsattr_value")->where(array("goodsattr_attrid"=>$data[$i]['attr_id'],'goodsattr_goodsid'=>0))->select();
            //组建属性值列表
            $str="<option value='0'>--请选择--</option>";
            foreach($goods_attr as $k=>$v){
                $str.="<option value='".$goods_attr[$k]['goodsattr_id']."'>".$goods_attr[$k]['goodsattr_value']."</option>";
            }
            $data[$i]['goodsattr']=$str;
        }
        $this->ajaxReturn($data);
        //echo json_encode(A('AdminAttribute')->getAttrByGoodstype($_GET['goodstype_id']));
    }

    /*
     * 商品编辑页面ajax返回本商品对应属性
     */
    public function ajax_getattrvalue()
    {
        $map['attr_isshow'] = 1;
        $map['attr_goodstypeid'] = I('get.goodstype_id', 0, 'intval');
        //获得该分类下的商品属性名
        $data = M("PaimaiAttribute")->where($map)->select();
        //该商品对应的属性值
        $thisgoodsattr=M("PaimaiGoodsattr")->field("goodsattr_id,goodsattr_value")->where(array('goodsattr_goodsid'=>$_GET['goodsattr_goodsid']))->select();
        //p($thisgoodsattr);
        for($i=0;$i<count($data);$i++){
            //根据每个名选择该属性名下的的属性值
            $goods_attr=M('PaimaiGoodsattr')->field("goodsattr_id,goodsattr_attrid,goodsattr_value")->where(array("goodsattr_attrid"=>$data[$i]['attr_id'],'goodsattr_goodsid'=>0))->select();
            //组建属性值列表
            $str="<option value='0'>--请选择--</option>";
            foreach($goods_attr as $k=>$v){
                $tag=false;
                foreach($thisgoodsattr as $p=>$q){
                    //v($q['goodsattr_value']."里面");
                    if($goods_attr[$k]['goodsattr_id']==$q['goodsattr_value']){
                        //break;
                        //v("1".$q['goodsattr_value']);
                        $str.="<option selected='selected' value='".$goods_attr[$k]['goodsattr_id']."'>".$goods_attr[$k]['goodsattr_value']."</option>";
                        $tag=true;
                        break;
                    }
                    //if($tag==true)break;
                }
                if($tag==true)continue;
                    $str.="<option value='".$goods_attr[$k]['goodsattr_id']."'>".$goods_attr[$k]['goodsattr_value']."</option>";

                //v($str);
            }
            $data[$i]['goodsattr']=$str;
        }

        /*$map['attr_isshow'] = 1;
        $map['attr_goodstypeid'] = I('get.goodstype_id', 0, 'intval');
        //获得类型下的属性名
        $data = M("PaimaiAttribute")->where($map)->select();
        //v($data);
        $thisgoodsattr=M("PaimaiGoodsattr")->field("goodsattr_value")->where(array('goodsattr_goodsid'=>$_GET['goodsattr_goodsid']))->select();
        //v($thisgoodsattr);
        for($i=0;$i<count($data);$i++){
            //获得每个属性名下的属性值
            $goods_attr=M('PaimaiGoodsattr')->field("goodsattr_value")->where(array("goodsattr_attrid"=>$data[$i]['attr_id'],'goodsattr_goodsid'=>$_GET['goodsattr_goodsid']))->select();
            //组建属性列表
            $str="<option value='0'>--请选择--</option>";
            foreach($goods_attr as $k=>$v){
                $tag=false;
               foreach($thisgoodsattr as $p=>$q){
                    if($goods_attr[$k]['goodsattr_value']==$q['goodsattr_value']){
                        $str.="<option selected='selected' value='".$goods_attr[$k]['goodsattr_value']."'>".$goods_attr[$k]['goodsattr_value']."</option>";
                        $tag=true;
                        break;
                    }
                }
                if($tag==true)continue;
                $str.="<option value='".$goods_attr[$k]['goodsattr_value']."'>".$goods_attr[$k]['goodsattr_value']."</option>";
            }
            $data[$i]['goodsattr']=$str;
            }
        //p($data);*/
        echo json_encode($data);
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

	//批量把商品显示可用
	public function action_show(){
		$ids = I('get.ids');
		$goods = M('PaimaiGoods');
		$data['goods_isshow'] = 1;
		$goods->where(array('goods_id'=>array('in',substr($ids,0,-1))))->save($data);
		$this->success('更新成功');
	}
	//批量把商品不显示可用
	public function action_noshow(){
		$ids = I('get.ids');
		$goods = M('PaimaiGoods');
		$data['goods_isshow'] = 0;
		$goods->where(array('goods_id'=>array('in',substr($ids,0,-1))))->save($data);
		$this->success('更新成功');
	}
    //批量修改商品[开始时间][结束时间][起拍价],和[所属专场]
    public function batch_chargegoodstime(){
        
        //要修改的商品ID,这个是字符串
        $ids=I('ids');
        if(empty($ids))exit("请选择商品");

        //专场id
        $special_id=I('special_id',0,'intval');
        //if(empty($special_id))exit("请正确输入专场ID");
        $special_arr=M('PaimaiSpecial')->field("special_starttime,special_endtime")->find($special_id);
        //if(empty($special_arr))exit("此专场不存在");

        //起始时间,如果为空则默认为本专场时间
        $starttime=I('starttime','','strip_tags');
        if(empty($starttime)){
            $starttime=$special_arr['special_starttime'];
        }else{
            $starttime=strtotime($starttime);
        }
        
        //结束时间,如果为空则默认为本专场时间
        $endtime=I('endtime','','strip_tags');
        if(empty($endtime)){
            $endtime=$special_arr['special_endtime'];
        }else{
            $endtime=strtotime($endtime);
        }
        if($endtime<$starttime)exit("开始时间时间怎么能比结束时间还小呢?");
        $chargegoods_where=array(
            'goods_id'=>array('IN',$ids),
        );
        $chargegoods_data=array(
            'goods_starttime'=>$starttime,
            'goods_endtime'=>$endtime,
            'goods_specialid'=>$special_id,
        );
		
        if(!M('PaimaiGoods')->data($chargegoods_data)->where($chargegoods_where)->save())exit("没有修改内容或修改失败");
        exit("修改成功");
    }

	/**
     * 排序
     */
    public function listorder(){
        $model = M('PaimaiGoods');
        $pk = $model->getPk();
        foreach ($_POST['listorder'] as $id => $v) {
            $condition = array($pk => $id);
            $model->where($condition)->setField('goods_order', $v);
        }
        $this->success('更新排序成功');
    }
	//
	public function settime(){
		$this->display();
	}
	public function do_settime(){ 
		F('time',NULL);
		F('time',$_POST);
		$this->success('设置成功');

	}
}