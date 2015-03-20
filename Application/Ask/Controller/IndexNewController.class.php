<?php
// +----------------------------------------------------------------------
// | 艺术问答前台控制器文件
// +----------------------------------------------------------------------
// | Author: tangyong <695841701@qq.com>
// +----------------------------------------------------------------------
namespace Ask\Controller;
use Home\Controller\HomeController;
use Think\Page;
class IndexNewController extends HomeController {
	private $uid;
    public function _initialize() { 
        parent::_initialize();
        $auth = getLoginStatus();
        $uid = $auth['mid'];
		$this->uid = $uid;
        $this->auth = $auth;
    }

    /*
     * 首页模板页面！
     */
    public function index(){
        #TODO#
        //首页最上方最新三条问题
        //$this->top_three = D('Question')->order('input_time desc')->limit(3)->select();
        $wheres['status'] = 2;
        $wheres['bast'] = 1;
        $order = 'input_time desc';
        $this->top_threes = D('QuestionView')->getAll($wheres,$order,19);
        
        //获取所有问题分类
        $category_list = M('Category')->field('cate_id, name, parent_id, short_name')->where('if_show = 1 and parent_id = 0')->select();
        foreach($category_list as $key => $value){
            $category_list[$key]['children'] = M('Category')->field('cate_id, name, parent_id, short_name')->where('if_show = 1 and parent_id = ' . $value['cate_id'])->select();
        }
        //dump($category_list);exit;
        $this->category_list = cates($category_list);
        
        //dump($this->category_list);die;


        //热门问答
        $this->hot_list = D('Question')->field('id, title')->where(array('tag' => 1))->limit(10)->order('input_time desc')->select();
        $this->hot_list1 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(0,5)->order('input_time desc')->select();
        $this->hot_list2 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(5,10)->order('input_time desc')->select();
        $this->hot_list3 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(10,15)->order('input_time desc')->select();
        
        //精彩问答
        $this->excellent_list1 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(0,5)->order('input_time desc')->select();
        $this->excellent_list2 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(5,10)->order('input_time desc')->select();
        $this->excellent_list3 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(10,15)->order('input_time desc')->select();

        //活跃达人
        $this->super_man = D('Question')->field('id, user_id')->group('user_id')->order('count(user_id) DESC')->select();
        //dump($this->super_man);

        //获取所有已解决的问题的数目
        $total_number = D('Question')->where(array('status' => 2))->count();
        $this->total_num = (int)$total_number;
        /*
         * 首页最下方，未解决，已解决，零回答
         */
        
        $where = 'status = 2'; //（已解决）
        $where1 = 'status = 1'; //（未解决）
        $where2 = '`id` not in(SELECT `question_id` FROM yishu_ask_reply)'; //（零回答）

        $this->list = D('QuestionRelation')->relation(true)->where($where)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(10)->select();
        $this->list1 = D('QuestionRelation')->relation(true)->where($where1)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(10)->select();
        $this->list2 = D('QuestionRelation')->relation(true)->where($where2)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(10)->select();
        $this->asklist = D('QuestionRelation')->relation(true)->where($where)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(7)->select();
        //dump($this->list);die;
        
        $this->title = '艺术问答—中国最具人气的艺术问答平台';
	   $this->keywords="艺术问答，中国艺术网艺术问答 ";
	   $this->desc="中国艺术网艺术问答—中国最具人气的艺术问答平台，有收藏品、书画、陶器瓷器、世界奇石等问答，帮助用户解决关于艺术的疑惑。";
        $this->display();
    }

    public function category(){
       
        if(IS_POST){
            //判断用户是否登录
            if(! $this->uid) {
                $this->error('请先登录！');//exit;
            }
            if($this->uid==I('get.uid')) {
                $this->error('操作错误！');//exit;
            }
			
            $this->Model = D('Reply');
            $cate_id = I('get.cate_id', '', 'intval'); //获取问题分类
            $qid = I('get.qid', '', 'intval'); //获取问题ID
            $data = $this->Model->create();
			//if(M('reply')->where(array('bast' => 1,))->select()){
             //   $this->error('该问题已解决,请回答未解决的问题...');
            //}
            if($data) {
                $data['user_id'] = $this->uid;
                $data['question_id'] = $qid;
                $data['category_id'] = $cate_id;
                $res = $this->Model->data($data)->add();
                if($res) {
                    if($minfo = M('member_info ')->find($this->uid)){
                    $mdata = array(
                        'input_time'   => time(),
                        'exp'          => ($minfo['exp'] + 2),
                        'prestige'     => ($minfo['prestige'] + 1),
                        'replay_num' => ($minfo['replay_num'] + 1)
                    );
                    M('member_info')->where(array('user_id'=>$this->uid))->setField($mdata);
                }else{
                    $mdata = array(
                        'input_time'   => time(),
                        'exp'          => 2,
                        'prestige'     => 1,
                        'replay_num' => ($minfo['replay_num'] + 1)
                    );
                    M('member_info')->where(array('user_id'=>$this->uid))->add($mdata);
                }
                    $this->success('回答成功!'); 
                }
            } else {
                $this->error($this->Model->getError());
            }
        } else {
            //全部分类上周排行榜
            $this->week_list = D('Reply')->field('user_id')->where('bast = 1')->group('user_id')->order('count(user_id) DESC')->select();

            //问问优秀专家
            $this->expert_list = D('Reply')->field('user_id')->group('user_id')->order('count(user_id) DESC')->select();

            //热门问答
            $this->hot_list1 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(0,5)->order('input_time desc')->select();
            $this->hot_list2 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(5,10)->order('input_time desc')->select();
            $this->hot_list3 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(10,15)->order('input_time desc')->select();
        
            //精彩问答
            $this->excellent_list1 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(0,5)->order('input_time desc')->select();
            $this->excellent_list2 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(5,10)->order('input_time desc')->select();
            $this->excellent_list3 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(10,15)->order('input_time desc')->select();

            //获取所有分类
            
            $category_list = M('Category')->field('cate_id, name, parent_id, short_name')->where('if_show = 1 and parent_id = 0')->select();
            foreach($category_list as $key => $value){
                $category_list[$key]['children'] = M('Category')->field('cate_id, name, parent_id, short_name')->where('if_show = 1 and parent_id = ' . $value['cate_id'])->select();
            }
            
            //dump($category_list);exit;
            $this->category_list = cates($category_list);

            $where1 = 'status = 2'; //（已解决）
            $this->asklist = D('QuestionRelation')->relation(true)->where($where1)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(7)->select();

            //零回答
            $where = '`id` not in(SELECT `question_id` FROM yishu_ask_reply)'; //（零回答）

            $zerolist = D('QuestionRelation')->relation(true)->where($where)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(6)->select();

            
            foreach($zerolist as $k => $v){
                $cat_id = $v['cate_id'];
                    $zerolist[$k]['name'] = M('Category')->where("cate_id = $cat_id")->getField('name');
            }
            $this->zerolist = $zerolist;
            //dump($this->zerolist);die;

           $this->title = '全部问题分类 - 艺术问答平台';
           $this->keywords="问题大全，艺术问答问题大全";
           $this->desc="中国艺术网艺术问答问题大全—问题大全有收藏品、书画、陶器瓷器、世界奇石等问答，帮助用户解决关于艺术的疑惑。";
            $this->display();
        }
    }

    public function category1(){

        $cate_id = I('cate_id');

        //获取所有分类
            $category_list = M('Category')->field('cate_id, name, parent_id, short_name')->where('if_show = 1 and parent_id = 0')->select();
            foreach($category_list as $key => $value){
                $category_list[$key]['children'] = M('Category')->field('cate_id, name, parent_id, short_name')->where('if_show = 1 and parent_id = ' . $value['cate_id'])->select();
            }
            //dump($category_list);exit;
            $this->category_list = cates($category_list);

        //未解决，已解决，零回答
            $where = 'status = 2 and cate_id = ' . $cate_id; //（已解决）
            $where1 = 'status = 1 and cate_id = ' . $cate_id; //（未解决）
            $where2 = '`id` not in(SELECT `question_id` FROM yishu_ask_reply) and cate_id = ' .$cate_id; //（零回答）

            //$this->list1 = D('QuestionRelation')->relation(true)->where($where)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(10)->select();
            //$this->list2 = D('QuestionRelation')->relation(true)->where($where1)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(10)->select();
            //$this->list3 = D('QuestionRelation')->relation(true)->where($where2)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(10)->select();
            //dump($this->list1);die;
        
            //热门问答
            $this->hot_list1 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(0,5)->order('input_time desc')->select();
            $this->hot_list2 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(5,10)->order('input_time desc')->select();
            $this->hot_list3 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(10,15)->order('input_time desc')->select();
        
            //精彩问答
            $this->excellent_list1 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(0,5)->order('input_time desc')->select();
            $this->excellent_list2 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(5,10)->order('input_time desc')->select();
            $this->excellent_list3 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(10,15)->order('input_time desc')->select();

            $where4 = 'status = 2'; //（已解决）
            $this->asklist = D('QuestionRelation')->relation(true)->where($where4)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(7)->select();

            //////////////////////////////////////////////////////
            //分页
            //已解决
             $news=D("QuestionRelation");
             //dump($news);exit;
             $count = $news->where($where)->count();   //查询记录的总条数
             $p = new Page($count, 10);
             $list = $news->relation(true)->where($where)->field('id,title,user_id,status,cate_id')->order('id desc')->limit($p->firstRow . ',' . $p->listRows)->select(); 
             //$sql = $news->getLastSql();
             //dump($sql);die;
             $p->setConfig('header', '条数据');   //分页样式可自定义
             $p->setConfig('prev', "<上一页"); 
             $p->setConfig('next', '下一页>'); 
             $p->setConfig('first', '首页'); 
             $p->setConfig('last', '尾页'); 
             $page = $p->show();            //分页的导航条的输出变量
             $this->assign("page",$page);   //在模板页面中输出分页
             $this->assign('list1',$list);       //查询的信息反馈到模板页面中

             //未解决
             $news=D("QuestionRelation");
             //dump($news);exit;
             $count = $news->where($where1)->count();   //查询记录的总条数
             $p = new Page($count, 10);
             $list = $news->relation(true)->where($where1)->field('id,title,user_id,status,cate_id')->order('id desc')->limit($p->firstRow . ',' . $p->listRows)->select(); 
             //$sql = $news->getLastSql();
             //dump($sql);die;
             $p->setConfig('header', '条数据');   //分页样式可自定义
             $p->setConfig('prev', "<上一页"); 
             $p->setConfig('next', '下一页>'); 
             $p->setConfig('first', '首页'); 
             $p->setConfig('last', '尾页'); 
             $page = $p->show();            //分页的导航条的输出变量
             $this->assign("page",$page);   //在模板页面中输出分页
             $this->assign('list2',$list);       //查询的信息反馈到模板页面中
             
             //零回答
             $news=D("QuestionRelation");
             //dump($news);exit;
             $count = $news->where($where2)->count();   //查询记录的总条数
             $p = new Page($count, 10);
             $list = $news->relation(true)->where($where2)->field('id,title,user_id,status,cate_id')->order('id desc')->limit($p->firstRow . ',' . $p->listRows)->select(); 
             //$sql = $news->getLastSql();
             //dump($sql);die;
             $p->setConfig('header', '条数据');   //分页样式可自定义
             $p->setConfig('prev', "<上一页"); 
             $p->setConfig('next', '下一页>'); 
             $p->setConfig('first', '首页'); 
             $p->setConfig('last', '尾页'); 
             $page = $p->show();            //分页的导航条的输出变量
             $this->assign("page",$page);   //在模板页面中输出分页
             $this->assign('list3',$list);       //查询的信息反馈到模板页面中


            $title = '';
            $cate = D('Category')->field('cate_id, name')->where(array('if_show' => 1))->select();
            $i = 0;
           foreach ($cate as $key => $value) {
               if ($value['cate_id'] == $cate_id)
               {
                    $title = $cate[$i]['name'];
               }
               $i++;
           }
            $this->title = $title.' - 艺术问答平台';
            $this->keywords="问题大全，艺术问答问题大全";
            $this->desc="中国艺术网艺术问答问题大全—问题大全有收藏品、书画、陶器瓷器、世界奇石等问答，帮助用户解决关于艺术的疑惑。";

             $this->display();
    }



    public function star(){
        #TODO#
        //取出所有问题的一级分类
        $cate_list = D('Category')->field('cate_id, name, short_name')->where('if_show = 1 && parent_id = 0')->select();
        foreach ($cate_list as &$v) {
            $v['pp'] = D('Reply')->field('user_id, category_id,  praise')->where(array('category_id' => $v['cate_id']))->group('user_id')->order('count(user_id) DESC')->limit(10)->select();
        }
        $this->star_list = array_chunk($cate_list, 3);
        $this->title = '问答之星 - 艺术问答平台';
    	$this->keywords="问答之星，艺术问答之星";
    	$this->desc="中国艺术网艺术问答之星—问答之星帮助用户解决关于收藏品、书画、陶器瓷器、世界奇石等艺术方面的疑惑。";

        $this->display();
    }

    public function activity(){
        #TODO#
        //echo "问答首页前台模板4!";
        $this->display();
    }

    public function mall(){
        #TODO#
        //echo "问答首页前台模板5!";
        $this->display();
    }

    public function detail(){
	
        #TODO#
        //获取问题的ID
        $id = I('get.qid', '', 'intval');
        $this->qid = $id;

        //查询该问题的信息
        $list = M('Question')->field('id, title, cate_id, input_time, user_id, add_content')->find($id);
        if(empty($list)){
            $this->error('<span style="font-size:20px;">你请求的页面不存在,三秒后自动跳转到</span><a href="/wenda/" style="text-decoration: none;color:#000;">问答首页</a>',"/wenda/");
        }
        $category_list = D('Category')->field('cate_id, name, parent_id, short_name')->where(array('if_show' => 1))->select();
        $this->list = $list;
		
        if($id){
            $this->listq = M('Question')->field('id, title, input_time,cate_id')->where(array('cate_id'=>$list['cate_id']))->limit(8)->select();
            $this->lists = M('Question')->field('id, title, input_time')->where(array('cate_id'=>$list['cate_id'],'status'=>1))->limit(5)->select();
            $this->place = getParents($category_list, $list['cate_id']);
        }
        $listq = $this->listq;
         foreach($listq as $k => $v){
                $cat_id = $v['cate_id'];
                    $listq[$k]['name'] = M('Category')->where("cate_id = $cat_id")->getField('name');
            }
            $this->listq = $listq;
            //dump($this->listq);die;

        //热门问答
        $this->hot_list1 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(0,5)->order('input_time desc')->select();
        $this->hot_list2 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(5,10)->order('input_time desc')->select();
        $this->hot_list3 = D('Question')->field('id, title')->where(array('tag' => 1))->limit(10,15)->order('input_time desc')->select();
        
        //精彩问答
        $this->excellent_list1 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(0,5)->order('input_time desc')->select();
        $this->excellent_list2 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(5,10)->order('input_time desc')->select();
        $this->excellent_list3 = D('Question')->field('id, title')->where(array('tag' => 2))->limit(10,15)->order('input_time desc')->select();

         $where = 'status = 2'; //（已解决）
         $this->asklist = D('QuestionRelation')->relation(true)->where($where)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(7)->select();

        //获取所有分类
            
            $category_list = M('Category')->field('cate_id, name, parent_id, short_name')->where('if_show = 1 and parent_id = 0')->select();
            foreach($category_list as $key => $value){
                $category_list[$key]['children'] = M('Category')->field('cate_id, name, parent_id, short_name')->where('if_show = 1 and parent_id = ' . $value['cate_id'])->select();
            }
            
            //dump($category_list);exit;
            $this->category_list = cates($category_list);

        //最佳答案
        $best_result = M('Reply')->field('id, content, user_id, input_time, praise,no_praise')->where(array('bast' => '1','question_id' => $id))->limit(1)->select();
        $this->best_result = $best_result;
        //dump($best_result);die;

        //最佳答案的评论
        $model = M('Comment');
        $map = array('reply_id' => $best_result[0]['id']);
        $count = $model->where($map)->count();
        $page = new Page($count, 10);
        $show  = $page->show();
        $doc = $model->field('user_id,content,inputime')->where($map)->order('comment_id DESC')->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('page', $show);
        $this->assign('doc', $doc);
        $sort = I('get.sort','','intval');
        if($sort){
            $input_time = 'input_time desc';
        }else{
            $input_time = 'input_time';
        }
        //其他几条回答
        //$this->other_answers = M('Reply')->field('id, content, user_id, input_time, praise')->where(array('bast'=>'0','question_id'=>$id))->order($input_time)->select();
        $this->other_reply = D('QuestionRelations')->relation(true)->field('id, content, user_id, input_time, praise')->where(array('bast'=>'0','question_id'=>$id))->order($input_time)->select();
	   $this->title = $list['title'].' - 艺术问答平台';
	   $this->asknav=$list['title'];
	   $this->keywords=$list['title'];
	//print_r( D('Category')->field("description")->where("cate_id=".$list['cate_id'])->find());
	// $title_info=;

	$this->desc=D('Category')->where("cate_id=".$list['cate_id'])->getField("description");
        $this->display();
    }
    //增加赞同数
    public function add_count(){
        if(M('reply')->where(array('id'=>I('get.rid')))->setInc('praise', 1)){
			return true;
		}else{
			return false;
		}
    }
    //增加反对数
    public function add_count_against(){
        if(M('reply')->where(array('id'=>I('get.rid')))->setInc('no_praise', 1)){
            return true;
        }else{
            return false;
        }
    }

	//采纳最佳答案
	public function cn(){
        if(M('question')->where(array('id'=>I('get.qid')))->setField('status', 2)){ 
			if(M('reply')->where(array('id'=>I('get.rid')))->setField('bast', 1)){
				echo 1;
			}else{
				echo 0;
			}
		}else{
			echo 0;
		}
    }
    public function category_detail(){
        #TODO#
        //获取所有的问答类型
        $this->Model = D('Category');
        $category_list = $this->Model->select();

        $this->list = category_tree($category_list);
        //echo "<pre>";
        //print_r($this->list);
//        foreach($category_list as $k => $v){
//            $map['parent_id'] = $v['cate_id'];
//            $category_list[$k]['child_cate'] = D('Category')->field('cate_id, time')->where($map)->order('input_time desc')->select();
//        }
	 $this->title = '全部问题分类 - 艺术问答平台';
	$this->keywords="问题大全，艺术问答问题大全";
	$this->desc="中国艺术网艺术问答问题大全—问题大全有收藏品、书画、陶器瓷器、世界奇石等问答，帮助用户解决关于艺术的疑惑。
";
        $this->display();
	

    }

    public function mall_detail(){
        #TODO#

        $this->display();
    }

    public function solved_list() {
        $keywords = I('get.keywords');
        $status = I('get.status');
        $map['title'] = array('like',"%".$keywords."%");
        //增加历史搜索记录
        $asks = session('keywords');
        if(empty($asks)){
            $asks = array($keywords);
            session('keywords', $asks);
        }else{
            if(! in_array($keywords, $asks)){
                array_unshift($asks, $keywords);
                session('keywords', $asks);
            }
        }
        $ask = array_chunk($asks,1,FALSE);
        //over history
        if(!$status) {
            $map['status'] = 2;
        } else {
            $map['status'] = 1;
        }
        //////////////////////////////////////////////////////////////新增分页
        $pages = 8;
        $count = D('QuestionRelation')->relation(true)->where($map)->count();
        $p = I('get.p',1,'intval') ? I('get.p',1,'intval') : 1;

        if($p <= 0 || $p > $count){
            $p = 1;
        }
        $page = new Pages($count,$pages);
        $page->setConfig('prev',"◀ 上一页");   
        $page->setConfig('next','下一页 ▶');   
        $page->setConfig('first','◀ 首页');   
        $page->setConfig('last','尾页 ▶'); 
        $tolpage = ceil($count / $pages);
        $list = D('QuestionRelation')->relation(true)->
                where($map)->field('id,title,status,cate_id,user_id,input_time')->order('id desc')->page($p, $pages)->select();
        $i = 1;
        $select = '';
        while ($i <= $tolpage) {
            if(I('get.p')==$i){
                $select .= "<option value='".$i."' selected>".$i."</option>";
            }else{
                $select .= "<option value='".$i."'>".$i."</option>";
            }
            $i++;
        }
        $this->count = $count;
        $this->page = $page->show();
        $this->pages = $tolpage;
        $this->select = $select;
        $this->list = $list;
        $this->love = M('Question')->where(array('tag'=>1))->field('id,title')->order('id desc')->limit(10)->select();
        $this->asks = $ask;
        $this->title = '搜索 '.$keywords.'  - 艺术问答平台';
        $this->display();
    }
    //清空历史
    public function clear(){
        session('keywords',null);
        echo "无搜索记录...";
    }
    public function besolved_list() {
        #TODO#

        $this->display();
    }

    /*
    * 我要提问页面
    */
    public function ask(){
        /*
         * 测试用户登录，假定用户ID=1
         */
        #TODO#
        if(IS_POST){
            //判断用户是否登录
            if(! $this->uid){
                $this->error('您还没有登录！');exit;
            }
            $this->Model = D('Question');

            $arr = $_POST;

            if($arr['title'] == ''){
                $this->error('请填写问题名称！');
            }
            if($arr['cate'][0] == '') {
                $this->error('请选择问题分类！');
            }
            //获取分类中后一个分类的ID

            if(end($arr['cate']) == '') {
                $new_arr = array_pop($arr['cate']);

            }
            $cate_id = end($arr['cate']);

            //将数据放到一个数组中
            $data['cate_id'] = $cate_id;
            $data['title'] = trim(I('post.title'));
            $data['add_content'] = trim(I('post.add_content'));
            $data['user_id'] = $this->uid;
            $data['input_time'] = time();

            $boolean = $this->Model->add($data);
            if ($boolean !== false) {
                if($minfo = M('member_info ')->find($this->uid)){
                    $mdata = array(
                        'input_time'   => time(),
                        'exp'          => ($minfo['exp'] + 2),
                        'question_num' => ($minfo['question_num'] + 1)
                    );
                    M('member_info')->where(array('user_id'=>$this->uid))->setField($mdata);
                }else{
                    $mdata = array(
                        'input_time'   => time(),
                        'exp'          => 2,
                        'question_num' => ($minfo['question_num'] + 1)
                    );
                    M('member_info')->where(array('user_id'=>$this->uid))->add($mdata);
                }
                $this->success('信息添加成功！',U("/wenda/IndexNew/detail/qid/$boolean"));
            } else {
                $this->error('信息添加失败！');
            }
        }else{
            echo '页面不存在';
        }
    }


    //满意回答的评论
    public function add_comment(){
        //获取用户ID和评论内容，答案ID
        $user_id = I('get.user_id', '', 'intval');
        $content = I('get.content');
        $reply_id = I('get.reply_id');
        if(! $this->uid){
            $this->error('您还没有登录！');
            exit;
        }
        if(!empty($user_id) AND !empty($content) AND !empty($reply_id)) {
            $data['user_id'] = $this->uid;
            $data['content'] = $content;
            $data['inputime'] = time();
            $data['reply_id'] = $reply_id;
            $res = M('Comment')->add($data);
            if($res) {
                $ans = M('Comment')->where(array('reply_id' => $reply_id))->order('comment_id desc')->select();
                if($ans) {
                    $this->success('评论成功！');
                }
            }
        }

    }

    //无限极分类的ajax
    public function ajax() {
        $this->Model = D('Category');
        $cate_id = I('get.pid', '', 'int');
        //echo $cate_id;
        $where = array('parent_id' => $cate_id);
        $res = $this->Model->where($where)->select();
        if (empty($res)) {
            $res = 0;
        }
        echo json_encode($res);
    }

    //满意回答的AJAX
    public function solve_ajax() {
        //获取问题的ID和回答的ID
        if(!$this->uid){
            $this->error('您还没有登录！');
            exit;
        }
        $qid = I('get.qid', '', 'intval');
        $rid = I('get.rid', '', 'intval');
        if($qid && $rid) {
            //将该问题设置为已解决
            $data['status'] = 2;
            $res = D('Question')->where(array('id' => $qid))->save($data);

            //将该答案设置为最佳答案
            $data2['bast'] = 1;
            $res2 = D('Reply')->where(array('id' => $rid))->save($data2);
            if($res && $res2) {
                echo '提交成功！';
            }
        }
    }

}