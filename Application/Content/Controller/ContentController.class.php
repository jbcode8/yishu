<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Content\Controller;
use Home\Controller\HomeController;
/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class ContentController extends HomeController {
    public function _initialize() {
        $auth = getLoginStatus();
        $this->assign('auth',$auth);
        $this->assign('title','中国艺术网-资讯');

    }
    /**
     * 文档模型频道页
     */
	public function index()
    {
		/* 分类信息 */
		$category = $this->category();
        /* 专题信息 */
        $special = M('SpecialTemp')->where(array('status'=>1))->field('recordid,name,title')->order('update_time DESC')->limit(2)->select();
        foreach($special as $k=>$v) {
            $special[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }

        /* 艺术品投资与收藏 */

        /* 名家推荐 */
        $Artist = D('Artist/Library');
        $this->assign('gh', $Artist->getLibrary(6, array('status'=>1, 'cid'=>14), 'createtime DESC'));
        $this->assign('yh', $Artist->getLibrary(6, array('status'=>1, 'cid'=>17), 'createtime DESC'));
        $this->assign('sf', $Artist->getLibrary(6, array('status'=>1, 'cid'=>19), 'createtime DESC'));
        $this->assign('sc', $Artist->getLibrary(6, array('status'=>1, 'cid'=>20), 'createtime DESC'));
        $this->assign('bh', $Artist->getLibrary(6, array('status'=>1, 'cid'=>21), 'createtime DESC'));
        $this->assign('yq', $Artist->getLibrary(6, array('status'=>1, 'cid'=>22), 'createtime DESC'));
        $this->assign('zs', $Artist->getLibrary(6, array('status'=>1, 'cid'=>23), 'createtime DESC'));

        /* 佳作推荐 */
        $this->assign('works',   D('Gallery/GalleryWorks')->getWorks(7, array('status'=>2)));
		/* 获取模板 */
		$tmpl = $category['template_index'];
        if($category['name'] == 'news') {
            //资讯栏目页推荐文章
            $Category = D('Category');
            $childId = $Category->getChildrenId($category['catid']);
            $news = D('Document')->getIndexPos($childId);
        }
		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
        $this->assign('news', $news);
        $this->assign('special', $special);
		$this->display($tmpl);
	}

    /**
     * 文档模型列表页
     */
	public function lists()
    {
		/* 分类信息 */
		$category = $this->category();
		/* 获取当前分类列表 */
		$Document = D('Document');
        $page = I('get.p') ? I('get.p'):1;
		$list = $Document->page($page, $category['list_row'])->lists($category['catid']);

        /* 名家列表 */
        $artist =  D('Artist/Library')->getLibrary(12, array('status'=>1), 'createtime DESC');
        /* 名家列表 */
        $gallery =  D('Gallery/GalleryList')->getGallery(12, array('status'=>array('neq',0)));
		/* 获取模板 */
		$tmpl = $category['template_lists'];
		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('lists', $list);
        $this->assign('artist', $artist);
        $this->assign('gallery', $gallery);
		$this->display($tmpl);
	}

    /**
     * 文档模型详情页
     */
	public function detail($id = 0)
    {
		/* 标识正确性检测 */
		if(!($id && is_numeric($id))) {
			$this->error('文档ID错误！');
		}
		/* 获取详细信息 */
		$Document = D('Document');
		$info = $Document->detail($id);
		if(!$info) {
			$this->error($Document->getError());
		}
        /* 内容分页 */
        $p = I('get.p', '', 'intval');
        $p = empty($p) ? 1 : $p;
        if(strpos ($info ['content'], '[page]')) {
            if(I('get.type') == 'all') {
                $info['content'] = str_replace('[page]', '', $info['content']);
            } else {
                $contents = array_filter(explode('[page]', $info ['content'])); //按分页标记分段
                $pagenumber = count ($contents); //分页数
                $page = new \Think\Page($pagenumber, 1);
                $info['content'] = $contents[$p - 1];
                $this->assign('page', $page->show()); //页码
            }
        }
		/* 分类信息 */
		$category = $this->category($info['catid']);
		/* 获取模板 */
		if(!empty($info['template'])){//已定制模板
			$tmpl = $info['template'];
		} elseif (!empty($category['template_detail'])){ //分类已定制模板
			$tmpl = $category['template_detail'];
		} else { //使用默认模板
			$tmpl = 'Article/'. get_document_model($info['model'], 'name') .'/detail';
		}
        /* 专题信息 */
        $special = M('SpecialTemp')->where(array('status'=>1))->field('recordid,name,title')->order('update_time DESC')->limit(2)->select();
        foreach($special as $k=>$v) {
            $special[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        $ForumThread = D('ForumThread');
        $forumList = $ForumThread->limit(10)->where(array('status'=>32))->field('tid,subject')->order('heats DESC')->select();
		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('info', $info);
        $this->assign('special', $special);
        $this->assign('forumList', $forumList);
        $this->assign('css', 'article-detail');
		$this->display($tmpl);
	}

    /**
     * 文档分类检测
     */
	private function category($id = 0)
    {
		/* 标识正确性检测 */
		$id = $id ? $id : I('get.category', 0);
		if(empty($id)){
			$this->error('请指定文档分类！');
		}


		/* 获取分类信息 */
		$category = D('Category')->info($id);
		if($category){
			return $category;
		} else {
			$this->error('该分类不存在！');
		}
	}

}
