<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class ContentController extends HomeController {
    /**
     * 文档模型频道页
     */
	public function index()
    {
		/* 分类信息 */
		$category = $this->category();
		//频道页只显示模板，默认不读取任何内容
		//内容可以通过模板标签自行定制
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

		/* 获取模板 */
		$tmpl = $category['template_lists'];
		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('lists', $list);
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
		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('info', $info);
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
