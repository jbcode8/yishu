<?php
// +----------------------------------------------------------------------
// | 艺术问答前台控制器文件
// +----------------------------------------------------------------------
// | Author: tangyong <695841701@qq.com>
// +----------------------------------------------------------------------
namespace Ask\Controller;
use Home\Controller\HomeController;
use Think\Page;
class IndexController extends HomeController {
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
        $this->top_threes = D('QuestionView')->getAll($wheres,$order,18);

        //获取所有问题分类
        $category_list = D('Category')->field('cate_id, name, parent_id, short_name')->where(array('if_show' => 1))->select();
        $this->category_list = cates($category_list);
        
        //热门问答
        $this->hot_list = D('Question')->field('id, title')->where(array('tag' => 1))->limit(8)->order('input_time desc')->select();

        //精彩问答
        $this->excellent_list = D('Question')->field('id, title')->where(array('tag' => 2))->limit(8)->order('input_time desc')->select();

        //活跃达人
        $this->super_man = D('Question')->field('id, user_id')->group('user_id')->order('count(user_id) DESC')->select();
        //dump($this->super_man);

        //获取所有已解决的问题的数目
        $total_number = D('Question')->where(array('status' => 2))->count();
        $this->total_num = (int)$total_number;
        /*
         * 首页最下方，未解决，已解决，零回答
         */
        $where = 'status = 1'; //默认条件（未解 决）

        //接受前台传递的条件
        $status = str_replace('.html', '', I('get.status'));

        if($status) {
            if($status == 'besolved'){
                $where = 'status = 2'; //已解决
            } elseif($status == 'no_reply'){
                $where = '`id` not in(SELECT `question_id` FROM yishu_ask_reply)';//零回答
            }
        }

        $this->list = D('QuestionRelation')->relation(true)->where($where)->field('id,title,user_id,status,cate_id')->order('id desc')->limit(10)->select();
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

            //获取所有分类
            $category_list = D('Category')->field('cate_id, name, parent_id, short_name')->where(array('if_show' => 1))->select();
            $this->category_list = cates($category_list);//dump($this->category_list);die;
            /*
             * /获取所有问题
             */
            /*
            $this->Model = D('Question');

            //筛选条件
            $con = 'status = 1';

            //接受前台传递的条件
            $status = str_replace('.html', '', I('get.status'));

            if($status) {
                if($status == 'besolved'){
                    $con = 'status = 2'; //已解决
                } elseif($status == 'no_reply'){
                    $con = '`id` not in(SELECT `question_id` FROM yishu_ask_reply)';//零回答
                }
            }

            $list = $this->Model->field('id, title, cate_id, user_id')->where($con)->select();
            foreach($list as $k => $v){
                $map['question_id'] = $v['id'];
                $list[$k]['ans'] = D('Reply')->field('id, user_id, input_time, content')->where($map)->order('input_time desc')->select();
            }*/
            $where['status'] = 1;
            $status = I('get.status');
            $reply = M('reply')->field('question_id')->group('question_id')->select();
            foreach ($reply as $v){
                $replyid .=  $v['question_id'].',';
            }
            if($status) {
                if($status == 'besolved'){
                    $where['status'] = '2'; //已解决
                }else if($status == 'no_reply'){
                    $where['id'] = array('not in',substr($replyid, 0, -1));//零回答
                }
            }
            if(I('get.cstatus','','intval')){
                if(I('get.cstatus','','intval') == 3){
                    $where['id'] = array('not in',substr($replyid, 0, -1));
                }else{
                    $where['cstatus'] = I('get.cstatus','','intval');
                }
            }
            if(I('get.keyword','','trim')){
                $where['title'] = array('like',"%" . I('get.keyword','','trim') . "%");
            }
            if(I('get.cid','','intval')){
                //$where['cate_id'] = I('get.cid','','intval');
				$cate_id = I('get.cid','','intval');
				$children_ids = M('category')->field('cate_id, short_name')->where(array('parent_id'=>$cate_id))->select();
				if(empty($children_ids)){
					$where['cate_id'] = I('get.cid','','intval');
				}else{
					foreach($children_ids as $rs){
						$inids[] = $rs['cate_id'];
					}
					$where['cate_id'] = array('in', $inids);
				}
				//dump($where);
                $cate = M('category')->where('cate_id='.I('get.cid','','intval'))->select();

				//echo M('category')->getLastSql();
                $this->place = getParents($category_list,I('get.cid','','intval'));
				
                $this->title = $cate[0]['name'].' - 艺术问答平台';
		//如果关键字和描述都为空则默认为名称
		$this->keywords=!empty($cate[0]['keywords'])?$cate[0]['keywords']: $cate[0]['name'];
		$this->desc=!empty($cate[0]['description'])?$cate[0]['description']:$cate[0]['name'];
            }else{
		//全部
                $this->title = '全部问题分类 - 艺术问答平台';
		$this->keywords="问题大全，艺术问答问题大全";
		$this->desc="中国艺术网艺术问答问题大全—问题大全有收藏品、书画、陶器瓷器、世界奇石等问答，帮助用户解决关于艺术的疑惑。";
            }
	    
            ////////////////
            $pages = 8;
            $count = D('QuestionRelation')->relation(true)->where($where)->count();
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
                    where($where)->field('id,title,status,cate_id,user_id')->order('id desc')->page($p, $pages)->select();
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
            //$this->page = $page->show();
            $show = $page->show();

			$suffix=$_SERVER['QUERY_STRING'];
        //去除开头两个字符串,并替换&&为?
        $suffix=str_replace("&&","?&",substr($suffix,3));
        //去除分页 p=3中的数字
        $suffix=preg_replace("/&p=(\d+)*/","",$suffix);
        $show=preg_replace("/(.*)Ask\/Index\/category(.*)p\/(\d+)(\/|\.)(.*)*html(.*)/U","$1".$suffix."&p=$3",$show);
		$this->page = $show;

            $this->pages = $tolpage;
            $this->select = $select;
            $this->list = $list;
            $this->display();
        }
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
	$this->desc="中国艺术网艺术问答之星—问答之星帮助用户解决关于收藏品、书画、陶器瓷器、世界奇石等艺术方面的疑惑。
";

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
        //查询该问题的信息
        $list = M('Question')->field('id, title, cate_id, input_time, user_id, add_content')->find($id);
        if(empty($list)){
            $this->error('<span style="font-size:20px;">你请求的页面不存在,三秒后自动跳转到</span><a href="/wenda/" style="text-decoration: none;color:#000;">问答首页</a>',"/wenda/");
        }
        $category_list = D('Category')->field('cate_id, name, parent_id, short_name')->where(array('if_show' => 1))->select();
        $this->list = $list;
		
        if($id){
            $this->listq = M('Question')->field('id, title, input_time')->where(array('cate_id'=>$list['cate_id']))->limit(5)->select();
            $this->lists = M('Question')->field('id, title, input_time')->where(array('cate_id'=>$list['cate_id'],'status'=>1))->limit(5)->select();
            $this->place = getParents($category_list, $list['cate_id']);
        }
        //最佳答案
        $best_result = M('Reply')->field('id, content, user_id, input_time, praise')->where(array('bast' => '1','question_id' => $id))->limit(1)->select();
        $this->best_result = $best_result;

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
                $this->success('信息添加成功！',U("wenda/question/$boolean"));
            } else {
                $this->error('信息添加失败！');
            }

        } else {

            //获取所有的问答类型
            $this->Model = D('category');
            $cate = $this->Model->field('cate_id, name ,parent_id, short_name')->where('if_show = 1')->select();
			$this->category_list = category_tree($cate);
            $this->user = M('member_info')->where(array('user_id' => $this->uid))->find();
            $this->title = '提问页面 - 艺术问答平台';
            $this->display();
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