<?php 
namespace Jianding\Controller;
use Jianding\Service\CategoryService;

class JiandingGoodsController extends JiandingController
{
	protected $gs;
	protected $gs_attr;
	protected $gs_price;
	protected $gs_com;
	protected $members;
	protected $gs_img;
	
	public function _initialize()
	{
	    parent::_initialize();
		$this->gs = D('JiandingGoods');
		$this->gs_attr = D('JiandingGoodsAttr');
		$this->gs_price = D('JiandingGoodsPrice');
		$this->gs_com = D('JiandingComment');
		$this->members = D('Members');
		$this->gs_img = D('JiandingGoodsImg');

		$cs = new CategoryService();
			//提取所有的目录
		$this->assign('allCategory',$cs->getAllCategory());

	}
	
	public function index()
	{
		
	}
	
	/**
	 * 浏览数加1
	 */
	private function gs_pageView($gs_id)
	{
	    $gs = $this->gs;
	    
	    $val = cookie('page_view');
	    $val = unserialize($val);
    	$val = $val == false ? array() : $val;
    	if (is_string($val))
    	{
    		$val = unserialize($val);
    	}
    	if(!in_array($gs_id, $val))
    	{
    	     $flag = $gs->where(array('goods_id' => $gs_id))
		        ->setInc('page_view');
    	     
		     if ($flag)
		     {
		     	array_push($val, $gs_id);
	            cookie('page_view', serialize($val));
	         }
	    }
	    
	}
	
	/**
	 * 鉴定商品详情
	 */
	public function gs_detail()
	{
	    $gsId = explode('-', I('get.goodsid'));
	    $gs_id = $gsId[2];
	    
	    /**
	     * 增加该鉴定商品的浏览数，关闭浏览器重新打开算浏览一次
	     */
	    $this->gs_pageView($gs_id);
	    
	    $user = I('cookie.');
	    $userinfo = '';
	    if ($user['mid'])
	    {
	        $userinfo = $user['mid'].','.$user['username'];
	    }
	    
	    /*
	     * 鉴定商品数据对象
	     * 鉴定商品属性数据对象 
	     * 鉴定商品评论数据对象
	     * 鉴定商品价格区间数据对象
	     * 会员数据对象
	     */
	    $gs = $this->gs;
	    $gs_attr = $this->gs_attr;
	    $gs_com = $this->gs_com;
	    $gs_price = $this->gs_price;
	    $members = $this->members;
	    $gs_img = $this->gs_img;
	    
	    //所鉴定的商品信息
	    $where = array('goods_id' => $gs_id);
	    $gsOne = $gs->where($where)->find();

	    //获取目录信息
	    $cat = M("JiandingCategory")->where('cat_id='.$gsOne['cat_id'])->find();
	  
	    
	    //所鉴定的商品的属性
	    $gsAttr = $gs_attr->where($where)->select();
	    
	    /*网名支持度*/
	    $supportRate = $gsOne['appreciate']/($gsOne['appreciate']+$gsOne['depreciate']);
	    $supportRate = ceil($supportRate*100);
	    
	    /**
	     * 鉴定商品评论
	     */
	    $gsCom = $gs_com->where(array('goods_id' => $gs_id))
	       ->order('atime desc')->select();
	    
	    /**
	     * 鉴定商品价格区间
	     */
	    $gsPrice = $gs_price->where(array('goods_id' => $gs_id))
	       ->order('range_level asc')->limit(4)->select();
	    
	    //我要估值的参与人数
	    $pricePeople = 0;
	    for ($i = 0;  $i < 4; $i++)
	    {
	    	foreach ($gsPrice[$i] as $k => $v)
	    	{
	    	    if ($k == 'favor_click')
	    	    {
	    	        $pricePeople += $v;
	    	    }
	    	}
	    }
	    
	    
	    /**
	     * 鉴定商品的图片
	     */
	    $imgs = $gs_img->getAllImgByGoodsId($gs_id);
	    
	    /**
	     * 热门鉴品
	     */
	    $hotGs = $gs->where(array('goods_id' => array('neq', $gs_id)))->order('page_view desc')->limit(1)->find();
	    $memInfo = $members->where(array('uid' => $hotGs['user_id']))->getField('uid,username', true);
	    $hotGs['username'] = $memInfo[$hotGs['user_id']];
	    
	    $assign = array(
	        'gsOne' => $gsOne, 
	        'gsAttr' => $gsAttr, 
	        'support' => $supportRate, 
	        'userinfo' => $userinfo, 
	        'gsCom' => $gsCom, 
	        'gsPrice' => $gsPrice, 
	        'pricePeople' => $pricePeople, 
	        'hotGs' => $hotGs, 
	        'imgs' => $imgs,
	        'cat_detail' => $cat
	    );
	    $this->assign($assign);
		$this->display('FrontPage/jd_detailed');
	}
	
	
	/**
	 * 我要估值投票
	 */
	public function gs_vote()
	{
	    $gs_price = $this->gs_price;
	    
	    $post = I('post.value');
	    $value = array($post[0]['name'] => $post[0]['value'], $post[1]['name'] => $post[1]['value']);
	    $goods_id = $value['goods_id'];
	    
	    $val = cookie('gs_vote');
	    if (empty($val))
	    {
	        $flag = $gs_price->where(array('prange_id' => $value['gs_range']))
	           ->setInc('favor_click');
	        if ($flag)
	        {
	            cookie('gs_vote', serialize(array($goods_id)));
	        }
	        echo $flag;
	    }else 
	    {
	    	$val = unserialize($val);
	    	if(in_array($goods_id, $val))
	    	{
		    	echo 'repeat';
		    }else
		    {
		    	$flag = $gs_price->where(array('prange_id' => $value['gs_range']))
			        ->setInc('favor_click');

			    if ($flag)
			    {
			     	array_push($val, $goods_id);
		            cookie('gs_vote', serialize($val));
		        }
		        echo $flag;

		    }
	    }
    	return ;
	}
	
	/**
	 * 获取最后一次鉴定商品评论
	 */
	public function gs_oneCom()
	{
	    $gs_com = $this->gs_com;
	    $gs_id = I('post.goods_id');
	    /**
	     * 鉴定商品评论
	     */
	    $gsCom = $gs_com->where(array('goods_id' => $gs_id))
	       ->order('atime desc')->limit(1)->find();
	    $gsCom['atime'] = toNow($gsCom['atime']);
	    echo json_encode($gsCom);
	}
	
	
	/**
	 * 评论鉴定商品
	 */
	public function gs_addComment()
	{
	    /*评论鉴定商品的数据对象*/
	    $gs_com = $this->gs_com;
	    
		$post = I('post.value');
		
		/**
		 * 评论数据整理
		 */
		$n = count($post);
		$value = array();
		$arr = array();
		for ($i = 0; $i < $n; $i++)
		{
			$k = $post[$i]['name'];
			$v = $post[$i]['value'];
			$arr[$k] = $v;
			$value[] = $arr;
		}
		$value = $value[$n-1];
		$userinfo = explode(',', $value['user_info']);
		$value = array_merge($value, array(
		    'user_id' => $userinfo[0], 
		    'user_name' => $userinfo[1], 
		    'atime' => time()
		));

		if (empty($value['user_info']))
		{
			$value['is_anonymous'] = 1;
			unset($value['user_id']);unset($value['user_name']);
		}
		unset($value['user_info']);
		
		/**
		 * 评论数据写入
		 */
		$gs_com->create($value);
		echo $gs_com->add();
		return ;
	}
	
	
	/**
	 * 点赞，点踩
	 * @param int $goods_id
	 * @param string $action
	 */
	public function gs_great($goods_id, $action)
	{
	    $goods_id = intval($goods_id);
		$arr = array(
			'add' => 'gs_addAppreciate', 
		    'cut' => 'gs_cutDepreciate'
		);
		$gs = $this->gs;
		
		$flag = $this->gs->where(array('goods_id' => $goods_id))
		      ->getField('goods_id');
		
		if (empty($flag))
		{
		    echo 'null';
		    return ;
		}
		$this->$arr[$action]($goods_id, $gs);
	}
	/**
	 * 点赞加1
	 * @param int $goods_id
	 * @param object $gs
	 * @return boolean
	 */
	private function gs_addAppreciate($goods_id, $gs)
	{
	    $val = cookie('gs_appreciate');
	    if (empty($val))
	    {
	        $flag = $gs->where(array('goods_id' => $goods_id))
	           ->setInc('appreciate');
	        if ($flag)
	        {
	            cookie('gs_appreciate', serialize(array($goods_id)));
	        }
	        echo $flag;
	    }else 
	    {
	    	$val = unserialize($val);
	    	if(in_array($goods_id, $val))
	    	{
		    	echo 'repeat';
		    }else
		    {
		    	$flag = $gs->where(array('goods_id' => $goods_id))
			        ->setInc('appreciate');

			     if ($flag)
			     {
			     	array_push($val, $goods_id);
		            cookie('gs_appreciate', serialize($val));
		        }
		        echo $flag;

		    }
	    }
    	return ;
	}
	/**
	 * 点踩减1
	 * @param int $goods_id
	 * @param object $gs
	 * @return boolean
	 */
	private function gs_cutDepreciate($goods_id, $gs)
	{
	    $val = cookie('gs_depreciate');
	    if (!$val)
	    {
	        $flag = $gs->where(array('goods_id' => $goods_id))
	           ->setInc('depreciate');
	        if ($flag)
	        {
	        	cookie('gs_depreciate', serialize(array($goods_id)));
	        }
	        echo $flag;
	    }else 
	    {
	        $val = unserialize($val);
	        if (in_array($goods_id, $val))
	        {
	            echo 'repeat';
	        }else 
	        {
	            $flag = $gs->where(array('goods_id' => $goods_id))
	               ->setInc('depreciate');
	            if ($flag)
	            {
	                array_push($val, $goods_id);
	                cookie('gs_depreciate', serialize($val));
	            }
	            echo $flag;
	        }
	    }
    	return ;
	}
}