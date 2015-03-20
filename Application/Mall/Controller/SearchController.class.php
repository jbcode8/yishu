<?php

// +----------------------------------------------------------------------
// | 前端 搜索 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen
// +----------------------------------------------------------------------
// | update Author: Kaiwei sun
// +----------------------------------------------------------------------

namespace Mall\Controller;

class SearchController extends FpublicController {

    public function _initialize(){

        parent::_initialize();
    }

    public function index(){

        // 获取URL或FORM的传值
        //isset($_GET['kw']) AND $kw = trim($_GET['kw']) AND
        //(isset($kw) && !empty($kw)) AND $kw = filterStr(urldecode($_GET['kw']));
		$kw = I('get.kw');
        isset($kw) OR $this->error('请先输入搜索关键字！');
        //$this->kw = urldecode($kw);
        // 写入关键字数据库前线检测是否存在
        //D('Keywords')->addWords($kw);
        $kw = D('keywords')->where(array('key_id'=>$kw))->getField('words');
		if(!empty($kw)){
			$this->kw = $kw;
		}else{
			$this->kw = '全部';
		}
        // 检索条件和显示字段
        $where['goods_name'] = array('like', '%'.$kw.'%');
        $where['status'] = 2;
        $fields = 'goods_id, goods_name, default_img, goods_price, market_price';

        // 数据库检索
        $list = D('Goods')->field($fields)->where($where)->select();
        //empty($list) OR $this->aryList = $list;
        if(empty($list)){
            $map['status'] = 2;
            foreach(get_tags_arr($kw) as $keywords){
                $map['goods_name'] = array('like', '%'.$keywords.'%');
                if($goog = D('Goods')->field($fields)->where($map)->order('goods_id desc')->limit(2)->select()){
                    $googs[] = $goog;
                }
            }
            foreach($googs as $v){
                $googs = $v;
            }
            if(! $googs){
                $googs = D('Goods')->field($fields)->order('goods_id desc')->limit(3)->select();
                $this->googs = $googs;
                $this->type = '新品推荐';
            }else{
                $this->googs = $googs;
                $this->type = '你是不是想找';
            }
            //热卖中的
            $hot['recommend'] = 1;
            $hot['status'] = 2;
            $hotgoods = D('Goods')->field($fields)->where($hot)->order('goods_id desc')->limit(6)->select();
            $this->hot = $hotgoods;
        }else{
            $this->aryList = $list;
        }
       
        $this->display();
    }

	//根据搜索内容反查搜索词表id作为搜索链接
	public function getKeywordId(){
		$kw = I('get.kw');
		if(empty($kw)){
			echo 0;
		}else{
			$id = D('Keywords')->addWords($kw);
			echo $id;
		}
	}
} 