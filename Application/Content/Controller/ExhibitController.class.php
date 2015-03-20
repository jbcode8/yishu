<?php
namespace Content\Controller;
use Home\Controller\HomeController;
/**
 * 展讯控制器
 */
class ExhibitController extends HomeController
{
    public function _initialize() {


    }

    /**
     * 文档模型频道页
     */
    public function index()
    {
        //专题信息
        $special = M('SpecialTemp')->where(array('status'=>1))->field('recordid,name,title')->order('update_time DESC')->limit(4)->select();
        foreach($special as $k=>$v) {
            $special[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        //画廊展览
        $hl = M('document')
            ->alias('a')
            ->join("LEFT JOIN yishu_document_exhibit b ON a.id = b.id")
            ->field('a.id, a.recordid, a.description, a.title, b.provinceid, b.starttime, b.endtime,   b.address, b.organizer, b.list')
            ->where(array('catid'=>7))
            ->limit(5)
            ->select();
        foreach($hl as $k=>$v) {
            $hl[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        //个人展览
        $gr = M('document')
            ->alias('a')
            ->join("LEFT JOIN yishu_document_exhibit b ON a.id = b.id")
            ->field('a.id, a.recordid, a.description, a.title, b.provinceid, b.starttime, b.endtime,  b.address, b.organizer, b.list')
            ->where(array('catid'=>8))
            ->limit(5)
            ->select();
        foreach($gr as $k=>$v) {
            $hl[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        //机构展览
        $jg = M('document')
            ->alias('a')
            ->join("LEFT JOIN yishu_document_exhibit b ON a.id = b.id")
            ->field('a.id, a.recordid, a.description, a.title, b.provinceid, b.starttime, b.endtime,   b.address, b.organizer, b.list')
            ->where(array('catid'=>9))
            ->limit(5)
            ->select();
        foreach($jg as $k=>$v) {
            $hl[$k]['thumb'] = D('Document')->getPic($v['recordid'], 'thumb');
        }
        $this->assign('special', $special);
        $this->assign('hl', $hl);
        $this->assign('gr', $gr);
        $this->assign('jg', $jg);
        $this->display();
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

    public function typeArea()
    {
        //华东地区
        $arrHd = array('山东省', '江苏省', '安徽省', '浙江省', '福建省', '上海市');
        //华南地区
        $arrHn = array('广东省', '广西壮族自治区', '海南省');
        //华中地区
        $arrHz = array('湖北省', '湖南省', '河南省', '江西省');
        //华北地区
        $arrHb = array('北京市', '天津市', '河北省', '山西省', '内蒙古自治区');
        //西北地区
        $arrXb = array('宁夏回族自治区', '新疆维吾尔自治区', '青海省', '陕西省', '甘肃省');
        //西南地区
        $arrXn = array('四川省', '云南省', '贵州省', '西藏自治区', '重庆市');
        //东北地区
        $arrDb = array('辽宁省', '吉林省', '黑龙江省');
        //港澳台地区
        $arrGa = array('台湾省', '香港特别行政区', '澳门特别行政区');
        $area = M('region')->where(array('pid'=>2))->field('name')->select();
        $typeArea = array();
        foreach( $area as $value){
            $value = $value['name'];
            if(in_array($value, $arrHd)){
                $typeArea['华东'][] = $value;
            }elseif(in_array($value, $arrHn)){
                $typeArea['华南'][] = $value;
            }elseif(in_array($value, $arrHz)){
                $typeArea['华中'][] = $value;
            }elseif(in_array($value, $arrHb)){
                $typeArea['华北'][] = $value;
            }elseif(in_array($value, $arrXb)){
                $typeArea['西北'][] = $value;
            }elseif(in_array($value, $arrXn)){
                $typeArea['西南'][] = $value;
            }elseif(in_array($value, $arrDb)){
                $typeArea['东北'][] = $value;
            }else{
                $typeArea['港澳台'][] = $value;
            }
        }
        dump($typeArea);


    }
}
?>