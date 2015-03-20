<?php
// +----------------------------------------------------------------------
// | 艺术百科前台控制器文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------
namespace Baike\Controller;
use Home\Controller\HomeController;
use Think\Pages;
use Org\Util\String;

class IndexController extends HomeController {
	private $uid;

    public function _initialize() {
        parent::_initialize();
        $auth = getLoginStatus();
        $uid = $auth['mid'];
		$this->uid = $uid;
        $this->auth = $auth;
    }

    /**
     * 百科首页动作
     */
    public function index() {
        $this->title = "艺术百科—中国最权威的艺术品艺术家古玩收藏百科知识平台";
	
        $model = D('Doc');
	
	
	$this->assign("keywords","艺术百科,中国艺术网百科,中国艺术网艺术百科");
	$this->assign("desc","中国艺术网艺术百科提供艺术行业所有词条的百科知识，有艺术品百科、艺术家百科、艺术专业术语百科、物件瓷器百科等知识信息的集合，满足用户的知识查阅和学习。");

        //每日推荐
        $recommend_doc = $model->field('did,title,summary,imginfo')->where(array('visible'=>1, 'locked'=>0, 'type'=>1, 'imginfo'=>array('neq','')))->order('lastedit DESC')->limit(3)->select();
        $this->assign('recommend_doc', $recommend_doc);

        //用户与词条数量
        $user_doc = $model->field('count(did) as ds,count(distinct(authorid)) as us')->select();
        $this->assign('ds', (int)$user_doc[0]['ds']);
        $this->assign('us', (int)$user_doc[0]['us']);

        //热门词条
        $hot_ranking = $model->field('did,title')->where(array('visible'=>1, 'locked'=>0, 'type'=>2))->order('lastedit DESC')->limit(10)->select();
        $this->assign('hot_ranking', $hot_ranking);

        //贡献排行创建词条50贡献编辑词条10贡献
        $contribution = D('Contribution')->order('creates,edits DESC')->limit(10)->select();
        foreach($contribution as $k => $vo){
            $contribution[$k]['value'] = D('Contribution')->getValue($vo['creates'], $vo['edits']);
        }
        $this->assign('contribution', $contribution);

        //最新创建
        $new_map = array('visible'=>1, 'locked'=>0 );
        $new_doc = $model->field('did,title,authorid,time')->where($new_map)->order('time DESC')->limit(10)->select();
        $this->assign('new_doc', $new_doc);
        //最新编辑
        $new_edit_map['time'] = array('exp', '<> `lastedit`');
        $new_edit = $model->field('did,title,authorid,lastedit')->where(array_merge($new_map,$new_edit_map))->order('lastedit DESC')->limit(10)->select();
        $this->assign('new_edit', $new_edit);

        //热点.'imginfo'=>array('neq',''),
        $hot_map = array('visible'=>1, 'locked'=>0 , 'type'=>2);
        $hot_doc = $model->where($hot_map)->order('lastedit DESC')->limit(2)->select();
        if($hot_doc){
            foreach($hot_doc as $key => $doc){
                $hot_doc[$key]['related'] = $this->getRelated($doc['did'],$doc['tag']);
            }
        }
        $this->assign('hot_doc', $hot_doc);
        //分类
        $super_cate = D('Category')->supercate();
        $cate_doc = array();
        $map = array('visible'=>1, 'locked'=>0 ,'imginfo'=>array('neq',''));
        foreach($super_cate as $k => $cate){
           $child = category_childids(D('Category')->category(), $cate['cid']);
           if($child){
               $map['cid'] = array('in', $child);
               $cate_doc[$k]['cate'] = $cate;
               $cate_doc[$k]['doc'] = $model->field('did,imginfo,title,summary,tag')->where($map)->order('lastedit DESC')->limit(4)->select();
               if($cate_doc[$k]['doc']){
                   //获取相关词条
                   foreach($cate_doc[$k]['doc'] as $kk => $doc){
                       $cate_doc[$k]['doc'][$kk]['related'] = $this->getRelated($doc['did'],$doc['tag']);
                   }
               }
           }
        }

        $this->assign('cate_doc', $cate_doc);
		$cate = M('baike_category')->field('name, cid, pid, short_name')->select();
		$this->cates = category_tree($cate);
        $this->display();
    }

    /**
     * 获取词条的相关词条
     * @param $did
     * @param $tag
     * @return bool|array
     */
    private function  getRelated($did,$tag){
        $model = D('doc');
        $tag_arr = explode(',',$tag);
        $where = "`visible`=1 AND `locked`=0 AND `did` <> ".$did;
        $str = '';
        foreach($tag_arr as $key => $tag){
            $str .= $key==0 ? "  FIND_IN_SET('$tag',`tag`) " : " OR FIND_IN_SET('$tag',`tag`) ";
        }
        $where .= " AND (".$str.")";
        return $model->field('did,title')->where($where)->limit(2)->select();
    }

    /**
     * 编辑词条内容动作
     */
    public function edit() {
        $model = D('Doc');
        //TODO 没有用户登录判断,没有管理员身份判断
        //查看用户一个星期内是否有未通过评审的版本，如果有5个未评审侧禁止编辑或者创建
        $t = time()-7*24*60*60;
        $num = $model->where(array('authorid'=>1, 'visible'=>0, 'time'=>array('gt', $t)))->count();//TODO  用户ID标识待完善
        $flag = false;
        if($num > 5){ $flag = true;  }
        else{
            $num += D('Edition')->where(array('authorid'=>1,'visible'=>0, 'time'=>array('gt', $t)))->count();//TODO  用户ID标识待完善
            if($num > 5){ $flag = true; }
        }
        if($flag){
            $this->error('您的首次可创建或编辑词条数的数量已达最大值,请等待管理员审核', U('index'));
            return;
        }
        //分段编辑模板调用
        if(I('get.sec', '', 'intval') && $did = I('get.did', '', 'intval')){
            $sec = I('get.sec', '', 'intval');
            $res = $model->field('did,title,cid,content,tag,summary')->where(array('did'=>$did))->select();
            if(!$res){ $this->error('请求页面不存在，返回百科首页', U('/baike')); return;}
            $doc =$res[0];
            $secList =  $model->splithtml(htmlspecialchars_decode($doc['content']));

            if(!isset($secList[$sec]['value']) && $secList[$sec]['flag']==0){
                header("Location: ".U('terminal&did='.$did));
            }
            $this->assign('content', $secList[$sec+1]['value']);
            $this->assign('doWhat', 'secEdit');
            $this->assign('doc', $doc);
            $this->assign('title', $doc['title']."_艺术百科_中国艺术网");
            $this->assign('sec', $sec);
            $this->display();
            return;
        }
        //分段编辑加入版本库
        if(I('post.doWhat') == 'secEdit'){
            $_POST['type'] = 2;
            $edition = D('Edition');
            if($edition->create()){
                $sec = I('post.sec');
                $did = I('post.did');
                $res = $model->field('did,title,cid,content,tag,summary')->where(array('did'=>$did))->select();
                $doc = $res[0];
                $secList =  $model->splithtml(htmlspecialchars_decode($doc['content']));
                $secList[$sec+1]['value'] = $_POST['content'];
                $new_section = $model->joinhtml($secList);
                $edition->content = htmlspecialchars($new_section);
                if($edition->add()){
                    //增加用户的编辑数量
                    D('Contribution')->addEdits(1);//TODO  用户ID标识待完善
                    $this->success('编辑成功，等待管理员审核！',U('/baike/'.I('get.did')));
                }
                else{
                    $this->error('编辑失败！');
                }
            }
            else{
                $this->error($edition->getError());
            }
            return;
        }
        //全文编辑模板调用
        if($did = I('get.did', '', 'intval')){
            $res = $model->field('did,title,cid,content,tag,summary')->where(array('did'=>$did))->select();
            if(!$res){  $this->error('请求页面不存在，返回百科首页', U('index')); }
            $this->assign('content', htmlspecialchars_decode($res[0]['content']));
            $this->assign('doc', $res[0]);
            $this->assign('doWhat', 'fullEdit');
            $this->assign('title', $res[0]['title']."_艺术百科_中国艺术网");
            $this->display();
            return;
        }
        //全文编辑加入版本库
        if(I('post.doWhat') == 'fullEdit'){
            $_POST['type'] = 1;
            $edition = D('Edition');
            if($edition->create()){
                if($edition->add()){
                    //增加用户的编辑数量
                    D('Contribution')->addEdits($this->uid); //TODO  用户ID标识待完善
                    $this->success('编辑成功，等待管理员审核！',U('/baike/'.I('get.did')));
                }
                else{
                    $this->error('编辑失败！');
                }
            }else {
				if(empty($this->uid)){
						$this->error('请先登录！');
				   }
                $this->error($edition->getError());
            }
            return;
        }
        //创建词条
        if(I('post.doWhat') == 'create' && !I('post.create_submit')) {
		
            if ($model->create()) {
                if ($did = $model->where(array('title' => $model->title))->getField('did')) {
                   header("Location: ".U('terminal&did='.$did));
                } else {
                    $this->assign('doWhat', 'create');
                    $this->assign('create_submit', '1');
                    $doc = array('title'=>$model->title, 'cid'=>$model->cid);
                    $this->title = $model->title;
                    $this->assign('doc', $doc);
                    $this->display('edit');
                }
            } else {
                $this->error($model->getError());
            }
            return;
        }

        //新词条入库动作
        if(I('post.create_submit') == 1) {
           if($model->create()){
               if($model->add()){
                   //刷新词条分类的词条数量
                   D('Category')->refreshDocs(1);
                   //增加用户的创建贡献
                   D('Contribution')->addCreates(1);
                   $this->success('词条创建成功，等待审核！',U('/baike/'));
               }else{
				   if(empty($this->uid)){
						$this->error('请先登录！');
				   }
                   $this->error('词条创建失败！');
               }
           }
           else{
               $this->error($model->getError());
           }
           return;
        }
        //添加词条视图渲染
        $this->title = "添加词条";
        $this->display('add');
    }

    /**
     * 词条分类列表动作
     */
    public function tag() {
        $this->title = "词条分类"."_艺术百科_中国艺术网";

	$this->assign("keywords","词条分类,艺术百科词条分类");
	$this->assign("desc","中国艺术网艺术百科词条分类频道为用户提供最新的词条关键词词条分类别表，用户可以在这里查看到近期网友和艺术爱好者的词条词条编辑结果，分享更多艺术百科知识。");

        $model = D('category');
        $this->catetree = $model->catetree();
        $this->display();
    }

    /**
     * 词条浏览动作
     */
    public function terminal() {
        $model = D('Doc');
	
        $did = I('get.did', '', 'intval');
	
        $doc = $model->field('did,cid,title,summary,tag,content,authorid,lastedit,views,editions,imginfo')->where(array("did" => $did))->select();

		//$res = D('category')->where(array('cid'=>$doc[0]['cid']))->select();
		$res = D('category')->where('cid='.$doc[0]['cid'])->select();
		$cates = D('category')->category();
        if($res && $res[0]['pid']==0) {
            $catelist = category_childs_one($cates, $doc[0]['cid']);
            array_unshift($catelist, $res[0]);
        }else{
            $catelist = category_parents($cates, $doc[0]['cid']);
        }
        $this->parent = $catelist[0];
		$this->catelist = $catelist;

        if(!$doc){
            $this->error('请求页面不存在，返回百科首页', U('index'));
        }
        $doc = $doc[0];
	//title信息
	$title_info=M("BaikeDoc")->field("title,tag,summary")->find($did);
	
        $this->title = $title_info['title']."_艺术百科_中国艺术网";
	$this->keywords=$title_info['tag'];
	$this->desc=$title_info['summary'];
        $this->doc = $doc;

        $content = htmlspecialchars_decode($doc['content']);
        $doc['sectionlist'] = $model->splithtml($content);
        $sectionlist = $model->getsections($doc['sectionlist']);

        $this->assign('sectionlist', $sectionlist);
        $this->assign('doc', $doc);

        if(!empty($doc['imginfo'])){
            $imginfo = stripslashes_imginfo($doc['imginfo']);
            $this->assign('imginfo', $imginfo);
        }
        //合作编辑者
        $editor = D('Edition')->field('DISTINCT(authorid)')->where(array('did'=>$did, 'authorid'=>array('neq', $doc['authorid'])))->select();
        $this->assign('editor', $editor);

        //词条排行榜
        $ranking  = $model->field('did,title')->where(array('visible'=>1, 'locked'=>0))->order('views,lastedit DESC')->limit(10)->select();
        $this->assign('ranking', $ranking);

        //获取词条的称赞信息
        $useful['num'] = D('Useful')->where(array('did'=>$did))->count();
        $useful['isHave'] = D('Useful')->where(array('did'=>$did, 'uid'=>1))->count(); //TODO 用户ID标识未获取
        $this->assign('useful', $useful);

        //增加词条的浏览次数
        $model->addViews($did);
        $this->display();
    }

    /**
     * 词条排行页面动作
     */
    public function hotentry() {
	

        $action = I('get.act', '' , 'intval');
        $arr = array('1'=>'最新词条', '2'=>'热门词条');
        if(!$action || !in_array($action, array_keys($arr))){
            $this->error('请求页面不存在，返回百科首页', U('/baike'));
        }
        $model = D('Doc');
        $map = array('visible'=>1, 'locked'=>0);
        //最新词条
        if($action == 1){
            $this->assign('act', 1);
 		

	$this->assign("keywords","最新词条,艺术百科最新词条");
	$this->assign("desc","中国艺术网艺术百科最新词条频道为用户提供最新艺术关键词词条分类别表，用户可以在这里查看到近期网友和艺术爱好者的最新词条编辑结果，分享更多艺术百科知识。");
        }
        //热门词条
        else if($action == 2){
		
	
            $this->assign('act', 2);
            $map['type'] = 2;
		$this->assign("keywords","热门词条,艺术百科热门词条");
	$this->assign("desc","中国艺术网艺术百科热门词条频道为用户提供最新的热门关键词词条分类别表，用户可以在这里查看到近期网友和艺术爱好者的热门词条编辑结果，分享更多艺术百科知识。");
            

        }
        else{
            return;
        }

        //分类判断
        $cid = I('get.cid', '0', 'intval');
        $this->assign('cid', $cid);
        if($cid>0){
            $childs = category_childids(D('Category')->category(),$cid);
            if($childs){
                $map['cid'] = array('in', $childs);
            }
        }
        //首字母判断
        $letter = I('get.l', 0, 'intval');
        if($letter > 0){
            $map['letter'] = chr($letter);
        }
        $this->assign('letter', $letter);
        $count = $model->where($map)->count();
        $page = new Pages($count, 10);
        $show  = $page->show();
        $doc = $model->field('did,cid,title,authorid,lastedit')->where($map)->order('lastedit DESC')->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('page', $show);
        $this->assign('doc', $doc);
        $this->assign('title', $arr[$action]."_艺术百科_中国艺术网");
        unset($model);
        //获取所有的一级分类
        $cate1 = D('Category')->supercate();
        $this->assign('cate1', $cate1);
        $this->display();
    }

    /**
     * 词条分类消息页面
     */
    public function collectionart() {
        $_cid = I('cid',  false,'intval');
		/*if(isset($_GET['short_name'])){
			$_cid = $this->getCidByShortname($_GET['short_name'])?$this->getCidByShortname($_GET['short_name']):false;
		}*/
        if(!$_cid){
            $this->error('请求页面不存在，返回百科首页', U('/baike'));
        }
        $model = D('category');
        //分类列表
        $this->catetree = $model->catetree();
        //获取所属顶级类别数据
		$res = $model->where("cid=$_cid")->select();
        if(!$res){
            $this->error('请求页面不存在，返回百科首页', U('/baike'));
        }
        $cates = $model->category();
		
        if($res && $res[0]['pid']==0) {
			//echo $res[0]['pid'];
            $catelist = category_childs_one($cates, $_cid);
			//echo "<pre>";print_r($res[0]);
            array_unshift($catelist, $res[0]);
        }else{
            $catelist = category_parents($cates, $_cid);
	}
	
	$this->parent = $catelist[0];
	
        $this->catelist = $catelist;
		//获取栏目标题,名字,描述
		$cid_info=M("BaikeCategory")->field("cid,name,description,keywords,pid,short_name")->where('cid='.$_cid)->find();
		$childs = M('baike_category')->field('cid,name,short_name')->where(array('pid'=>$_cid))->select();
		if($childs){
			$this->nameo = $cid_info['name'];
			$this->cido = $cid_info['cid'];
			$this->short_name = $cid_info['short_name'];
			$this->childs = $childs;
			//echo "<pre>";print_r($childs);
		}else{
			$nochilds = M('baike_category')->field('cid,name,short_name')->where(array('pid'=>$cid_info['pid']))->select();
			$nochild = M('baike_category')->field('cid,name,short_name')->where(array('cid'=>$cid_info['pid']))->find();
			$this->nameo = $nochild['name'];
			$this->cido = $nochild['cid'];
			$this->short_name = $nochild['short_name'];
			$this->childs = $nochilds;
		}
		
        $this->title = $cid_info['name']."_艺术百科_中国艺术网";
		$this->keywords=!empty($cid_info['keywords'])?$cid_info['keywords']:$cid_info['name'];
		$this->desc=$cid_info['description'];
	
	

        //焦点数据
        $last = count($catelist)-1;
        $cid = $catelist[$last]['cid'];
        $this->cid = $cid;
        $this->name = $catelist[$last]['name'];

        $this->foucs = D('Doc')->field('did,cid,title,imginfo,summary,tag,lastedit')->where(array('cid'=>$cid, 'locked'=>0, 'visible'=>1, 'imginfo'=>array('neq','')))->order('lastedit DESC')->limit(3)->select();
	
	
        //词条列表
        $foucs = array();
        if($this->foucs){ foreach($this->foucs as $doc){  $foucs[] = $doc['did']; } }
        $map = array('cid'=>$cid, 'locked'=>0, 'visible'=>1);
        if($foucs){ $map['did'] = array('NOT IN',$foucs); }

        //首字母判断
        $letter = I('get.l', 0, 'intval');
		
        if($letter > 0){
            $map['letter'] = chr($letter);
        }
        $this->assign('letter', $letter);

        $count = D('Doc')->where($map)->count();
        $page = new Pages($count, 10);
        $show  = $page->show();
        //$docList = D('Doc')->field('did,cid,title,imginfo,summary,tag,lastedit')->where($map)->limit($page->firstRow.','.$page->listRows)->select();
		$docList = D('Doc')->field('did,cid,title,imginfo,summary,tag,lastedit')->where($map)->order('lastedit DESC')->limit($page->firstRow.','.$page->listRows)->select();
        if($docList){
            foreach($docList as $key => $doc){
                $docList[$key]['summary'] = String::msubstr($doc['summary'], 0, 35);
            }
        }

        $this->assign('docList', $docList);
        $this->assign('page', $show);
        $this->display();
    }
	
    /**
     * 词条的搜索动作
     */
    public function search(){
        $model = D('Doc');
        //post搜索
        
            
        
        //开放分类跳转
        if($tag = I('get.tag')){
            $res = $model->where("`visible` = 1 AND `locked` = 0 AND  FIND_IN_SET('$tag',`tag`)")->select();
		//print_r($tag);exit;
            $this->assign('search_res', $res);
	   $this->title=$tag."_艺术百科_中国艺术网";
	      $this->keywords=$res[0]['tag'];
	   $this->desc=$res[0]['summary'];
		$this->title = $tag.'_中国艺术网百科';
            $this->display();
        }else{
			$no_res = true;
            $title = I('get.doc');
            $this->assign('search_text', $title);
            //进入词条
            if(I('get.go', 0, 'intval') == 1){
                $did = $model->where(array('title'=>$title))->getField('did');
                if($did){
                    header('Location: '.U('/baike/'.$did)); return;
                }
            }
            //搜索词条
            else if(I('get.search', 0, 'intval') == 1){
                $res = $model->where("`visible` = 1 AND `locked` = 0 AND FIND_IN_SET('$title',`tag`) OR `title` LIKE '%$title%'")->select();
                if($res){
                    $this->assign('search_res', $res);
                    $no_res = false;
                }
				
            }
            //参数错误
            else{
                $this->error('参数错误！');
                return;
            }
			$this->title = '搜索'.$title.'的结果_中国艺术网百科';
            $this->assign('no_res', $no_res);
            $this->display();
		}
        //get方式直接跳转,此接口用于编辑器添加百科链接，暂时不开放
        /*else{
            $title = I('get.search');
            if($title){
                $did = $model->where(array('title'=>$title))->getField('did');
                if($did){
                    header('Location: '.U('/baike/'.$did));
                }
                else{
                    echo "<script>alert('词条不存在');</script>";
                }
            }
            else{
                $this->error('参数错误！');
            }
        }*/
    }

    /**
     * 添加到收藏夹动作
     */
    public function favorite(){
        //TODO 检测用户登录没有登录跳转登录页面
        $did = I('get.did', '', 'intval');
        if(!$did){ $this->error('请求存在，返回当前面！'); return; }
        $model = D('Docfavorite');
        $count = $model->where(array('did'=>$did, 'uid'=>1))->count(); //TODO 用户ID标识待优化
        if($count > 0){
            $this->success('该词条已经收藏过了！');
        }
        else{
            $title = D('Doc')->where(array('did'=>$did))->getField('title');
            $res = $model->add(array('uid'=>1, 'did'=>$did, 'title'=>$title));//TODO 用户ID标识待优化
            if($res)
                $this->success('收藏成功！');
            else
                $this->success('收藏失败！');
        }
    }

    /**
     * 称赞动作
     */
    public function useful(){
        //TODO 检测用户登录没有登录跳转登录页面
        $did = I('get.did', '', 'intval');
        if(!$did){ $this->error('请求存在，返回当前面！'); return; }
        $model = D('Useful');
        $count = $model->where(array('did'=>$did, 'uid'=>1))->count(); //TODO 用户ID标识待优化
        if($count > 0){
            $this->success('已经称赞过了！');
        }
        else{
            $res = $model->add(array('uid'=>1, 'did'=>$did));//TODO 用户ID标识待优化
            if($res)
                $this->success('称赞成功！');
            else
                $this->success('称赞失败！');
        }
    }

    /**
     * 预览词条
     */
    public function preview(){
        if(isset($_GET['t'])){
            $model = D('Doc');
            $data =S(session_id());
            $content = htmlspecialchars_decode($data['content']);
            $doc['sectionlist'] = $model->splithtml($content);
            $sectionlist = $model->getsections($doc['sectionlist']);
            $doc['summary'] = $data['summary'];
            $doc['title'] = $data['title'];
            $this->assign('doc', $doc);
            $this->assign('sectionlist', $sectionlist);
            $this->assign('preview' , 1);
            $this->display('terminal');
            S(session_id(),null);
        }else{
            $s = session_id();
            $arr['title'] = I('title');
            $arr['content'] = I('content');
            $arr['summary'] = I('summary');
            S($s,$arr);
        }
    }
	public function jqe_upimg(){
		$upload = new \Think\Upload();// 实例化上传类    
		$upload->maxSize   =     1024*2*1024 ;// 设置附件上传大小    
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
		//$upload->savePath  =      '/Uploads/'; // 设置附件上传目录    // 上传文件     
		$info   =   $upload->upload();
		if($info) {
			// 上传成功 获取上传文件信息    
			foreach($info as $file){        
				$images = $file['savename'];
				$imgPath = '/Uploads/'.$file['savepath'];
			}
			echo '<script type="text/javascript">parent.jQEditor.plugin("HdImage").insert("'.$imgPath.$images.'","'.$imgPath.$images.'");</script>';
		}else{
			// 上传错误提示错误信息    
			//echo $upload->getError();
			echo 'error';
		}
	}

	//根据short_name反查cid
	private function getCidByShortname($short_name){
		return M('baike_category')->where(array('short_name'=>$short_name))->getField('cid');
	}
}
