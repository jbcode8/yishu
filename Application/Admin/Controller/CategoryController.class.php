<?php
// +----------------------------------------------------------------------
// | 栏目列表
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;


class CategoryController extends AdminController{

    /**
     *栏目分类列表
     */
    public function index(){
        $this->assign('tree',D('Category')->getTree());
        $this->display();
    }

    /**
     * 添加栏目
     */
    public function add()
    {
        $catid = I('get.catid','','intval');
        if($catid) {
            $category = D('category')->field('title,catid')->find($catid);
            $this->assign('category',$category);

        }
        $modelList = D('Model')->field('id,title')->select();
        $this->assign('modelList', $modelList);
        $this->display('add');
    }
    /**
     * 更新栏目
     */
    public function edit()
    {
        $Category = D('category');
        $category = $Category->field(true)->find(I('get.catid','','intval'));
        $pCat = $Category->field('title,catid')->find($category['pid']);

        $modelList = D('Model')->field('id,title')->select();
        $this->assign('modelList', $modelList);
        $this->assign('category', $category);
        $this->assign('pCat', $pCat);
        $this->display('edit');
    }

    /**
     * 通用更新,添加方法
     */
    public function update(){
        $Category = D('Category');
        //执行添加操作
        $res = $Category->update();
        if(!$res){
            $this->error($Category->getError());
        }else{
            if($res['catid']){
                $this->success('更新成功!');
            }else{
                $this->success('新增成功!');
            }
        }
    }

    /**
     * 删除栏目
     */
    public function delete()
    {
        $Category = D('category');
        $catid = I('get.catid','','intval');
        //判断该栏目是否有子栏目
        if(D('category')->where(array('pid'=>$catid))->find()) {
            $this->error('请先删除该栏目下的子栏目');
        }
        //判断该栏目下是否有文章
        if(D('Document')->where(array('catid'=>$catid))->find()) {
            $this->error('请先删除该栏目下的文章');
        }
        parent::delete();
    }
}