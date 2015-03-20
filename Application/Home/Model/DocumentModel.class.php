<?php

namespace Home\Model;
use Think\Model;
use Addons\Attachment\Model\AttachmentModel;
use Addons\Attachment\Model\RecordModel;
/**
 * 文档基础模型
 */
class DocumentModel extends Model
{

    private $v9_prefix = 'v9_';
    private $v9_children_catids = array();
    /**
     * 获取文档列表
     * @param  integer  $catid 分类ID
     * @param  string   $order    排序规则
     * @param  integer  $status   状态
     * @param  boolean   $field    字段 true-所有字段
     * @param  integer   $position    是否推荐 推荐1 不推荐0
     * @return array
     */
    public function lists($catid = 0, $order = '`update_time` DESC', $status = 1, $field = true, $position = 0){
        if(!empty($catid)){
            $map = $this->listMap($catid, $status);
            $return = $this->field($field)->where($map)->order($order)->select();
        }else{
            if($position){
                $map = 'position > 0';
                $return = $this->field($field)->where($map)->order($order)->limit(10)->select();
            }
        }
        if($return) {
            foreach($return as $k=>$v) {
                $return[$k]['url'] = $this->getpic($v['recordid'], 'thumb');
            }
        } else {
            $return = false;
        }
        return $return;
    }

    /**
     * 设置where查询条件
     * @param  number  $catid 分类ID
     * @param  integer $status   状态
     * @return array             查询条件
     */
    public function listMap($catid, $status = 1){
        /* 设置状态 */
        $map = array('status' => $status);
        /* 设置分类 */
        if(!is_null($catid)){
            if(is_numeric($catid)){
                $map['catid'] = $catid;
            } else {
                $map['catid'] = array('IN', str2arr($catid));
            }
        }
        return $map;
    }

    /**
     * 计算列表总数
     * @param  number  $category 分类ID
     * @param  integer $status   状态
     * @return integer           总数
     */
    public function listCount($category, $status = 1){
        $map = $this->listMap($category, $status);
        return $this->where($map)->count('id');
    }

    /**
     * 获取文章详细信息
     * @param  integer $id   文章id
     * @return array
     */
    public function detail($id){
        /* 获取基础数据 */
        $info = $this->field(true)->find($id);
        if(!(is_array($info) || 1 !== $info['status'])){
            $this->error = '文档被禁用或已删除！';
            return false;
        }
        /* 获取模型数据 */
        $logic  = $this->logic($info['model']);
        $detail = $logic->detail($id); //获取指定ID的数据
        if(!$detail){
            $this->error = $logic->getError();
            return false;
        }
        return array_merge($info, $detail);
    }

    /**
     * 获取扩展模型对象
     * @param  integer $model 模型编号
     * @return object         模型对象
     */
    private function logic($model){
        return D(get_document_model($model, 'name'), 'Logic');
    }
//    /**
//     * 获取表全名
//     * @param  integer $model 模型编号
//     * @return string         表全名
//     */
//    public function getTable($model){
//        $logic = $this->logic($model);
//        return $logic->tablePrefix.$logic->getModelName();
//    }

    /**
     * 推荐位
     * @param  integer $catid 栏目id
     * @param  integer $posid 推荐位id
     * @param  integer $type 推荐位类型
     * @param  integer $thumb 是否缩略图
     * @param  string $limit 读取个数
     * @return array $record
     */
    public function getPos($catid, $posid, $type, $thumb, $limit)
    {
        //是否只调用有缩略图的文章
        if($thumb) {
            $mapP = array('catid'=>$catid, 'posid'=>$posid, 'is_thumb'=>$thumb);
        } else {
            $mapP = array('catid'=>$catid, 'posid'=>$posid);
        }
        //获取文章id
        $ids = M('PositionData')->field('id')->where($mapP)->limit($limit)->select();
        foreach($ids as $k=>$v) {
            $id[] = $v['id'];
        }
        $map['id'] = array('IN', $id);
        //获取文章相关信息
        $record= $this->field('id,title,recordid,description,update_time')->order('update_time DESC')->where($map)->select();
        if($type) {
            foreach($record as $k =>$val) {
                $record[$k]['url'] = $this->getPic($val['recordid'], 'thumb');
            }
        }
        return $record;
    }

    /**
     * 获取文章图片
     * @param  integer $recordid 关联id
     * @param  string $type 图片类型
     * @return array
     */
    public function getPic($recordid, $type)
    {
        $record = new RecordModel();
        $attachement = new AttachmentModel();
        $sourceid = $record->where(array('recordid' => intval($recordid)))->getField('sourceid');
        if($sourceid){
            $map['_id'] = array('IN', $sourceid);
            $map['type'] = $type;
            $res = $attachement->where($map)->getField('savepath,savename');
            if($type =='thumb') {
                return $res[0]['savepath'].$res[0]['savename'];
            } else {
                return $res;
            }
        }
    }

    /**
     * 文章调用
     * @param  integer $catid 栏目id
     * @param  string $field 字段信息
     * @param  integer $thumb 缩略图
     * @param  string $order 排序方式
     * @param  integer $limit 调用数量
     * @return array
     */
    public function getList($catid, $field, $thumb, $order, $limit)
    {
        if(is_numeric($catid)) {
            $map['catid'] = $catid;
        } else {
            $map['catid'] = array('IN', $catid);
        }
        if($thumb) {
            $map['thumb'] =  $thumb;
        }
        $map['statu'] =1;
        empty($field) ? true : $field;
        return $this->field($field)->where($map)->order($order)->limit($limit)->select();
    }

    /**
     * 文章tag调用
     * @param  integer $id 文章id
     * @return array
     */
    public function getTag($id)
    {
        $strTag = $this->field('keywords')->find($id);
        return explode(",", $strTag['keywords']);
    }

    /**
     * 广告调用
     * @param  integer $id 广告位id
     * @return array $ads
     */
    public function getAdv($id)
    {
        $Space = new \Ad\Model\SpaceModel();
        $Advertise = new \Ad\Model\AdvertiseModel();
        $adshow = $Space->where(array('id'=>$id))->getField('adshow');
        $ads = $Advertise->getAdv($id, $adshow);
        return  $ads;
    }

    /**
     * 搜索结果
     * @param  array $map 条件
     * @param  array $page 当前页数
     * @param  array $listRow 每页显示数
     * @return array $ads
     */
    public function search($map, $page, $listRow)
    {
        $list = $this->where($map)->order('update_time DESC')->page($page.",".$listRow)->select();
        $count = $this->where($map)->count();
        $Page       = new \Think\Page($count, $listRow);
        $show       = $Page->show();
        return array('list'=>$list, 'count'=>$count, 'show'=>$show,);
    }

    /**
     * 资讯栏目页推荐文章
     * @param  array $catid 条件
     * @return string $return
     */
    public function getIndexPos($catid)
    {
        $len = 0;
        $count = 1;
        //获取推荐文章id
        $mapP = array(
            'status'=>1,
            'posid'=>2,
            'catid'=>array('IN',$catid)
        );
        $ids = M('PositionData')->field('id')->where($mapP)->select();
        foreach($ids as $k=>$v) {
            $id[] = $v['id'];
        }
        $map['id'] = array('IN', $id);
        //获取推荐文章详情
        $article = $this->where($map)->order('update_time DESC')->limit(30)->select();
        //组合推荐文章
        $news[0]['dt'] = array('id'=>$article[0]['id'], 'title'=>new_msubstr($article[0]['title'], 0, 18), 'len'=>$len);
        if($article) {
           unset($article[0]);
           foreach($article as $k => $v) {
               $len += strlen( new_msubstr($v['title'], 0, 12) );
               if($len <= 72){
                   $news[$count-1]['dd'][] = array('id'=>$v['id'], 'title'=>new_msubstr($v['title'], 0, 12), 'len'=>$len);
               } else {
                   if($count == 4) break;
                   $len = 0;
                   $news[$count]['dt'] = array('id'=>$v['id'], 'title'=>new_msubstr($v['title'], 0, 18), 'len'=>$len);
                   $count++;
               }
           }
        } else {
            return false;
        }
        return $news;
    }

    /*
     * phpcmsv9链接 首页碎片
     */

    /**
     * 获取推荐位新闻
     * @param  integer $posid 推荐位id
     * @param  string $sort 排序字段
     * @param  integer $limit 输出条数
     * @param  integer $thumb 是否必须带图片
     * @return array
     */
    public function getVNewsByPosid($posid, $limit = '0,10' , $sort = 'a.listorder desc',$thumb = 0){
        /*
        $catid = $this->db(1,'DB_V9')->query("select catid from ".$this->v9_prefix."_position where posid = ".$posid)[0]['catid'];
        $news_catids_arr = $this->getVChildrenCatids($catid);
        $news_catids_str = implode(',',$news_catids_arr);
        $return_arr = $this->db(1,'DB_V9')->query("select * from ".$this->v9_prefix."_news where catid in (".$news_catids_str.") and posids > 0 order by ".$sort.' limit '.$limit);
        return $return_arr;
        */
        $where_thumb = '';
        if($thumb){
            $where_thumb = ' and a.thumb = 1';
        }
        $return_arr = $this->db(1,'DB_V9')->query("select a.data,b.* from ".$this->v9_prefix."position_data a join ".$this->v9_prefix."news b on a.id = b.id where a.modelid = 1 and a.posid = ".$posid.$where_thumb." order by ".$sort.' limit '.$limit);
		foreach($return_arr as &$val){
			$pos_data = string2array($val['data']);
			if(!empty($pos_data['thumb'])){
				$val['thumb'] = $pos_data['thumb'];
			}
		}
        return $return_arr;
    }

    /**
     * 获取推荐位作品
     * @param  integer $posid 推荐位id
     * @param  string $sort 排序字段
     * @param  integer $limit 输出条数
     * @param  integer $thumb 是否必须带图片
     * @return array
     */
    public function getVOpusByPosid($posid, $limit = '0,10' , $sort = 'a.listorder desc',$thumb = 0,$typeid){
        $where_thumb = '';
        if($thumb){
            $where_thumb = ' and a.thumb = 1';
        }
		if($typeid){
			$where_typeid = ' and b.typeid='.$typeid;
		}
        $return_arr = $this->db(1,'DB_V9')->query("select a.data,b.* from ".$this->v9_prefix."position_data a join ".$this->v9_prefix."opus b on a.id = b.id where a.modelid = 25 and a.posid = ".$posid.$where_typeid.$where_thumb." order by ".$sort.' limit '.$limit);
		foreach($return_arr as &$val){
			$pos_data = string2array($val['data']);
			if(!empty($pos_data['thumb'])){
				$val['thumb'] = $pos_data['thumb'];
			}
		}
        return $return_arr;
    }

    /**
     * 获取推荐位展讯
     * @param  integer $posid 推荐位id
     * @param  string $sort 排序字段
     * @param  integer $limit 输出条数
     * @param  integer $thumb 是否必须带图片
     * @return array
     */
    public function getVExhibitionByPosid($posid, $limit = '0,10' , $sort = 'a.listorder desc',$thumb = 0,$cityid){
        $where_thumb = '';
        $where_city = '';
        if($thumb){
            $where_thumb = ' and a.thumb = 1';
        }
        if($cityid){
            $where_city = 'city='.$cityid;
			$sort = 'updatetime desc';
			$return_arr = $this->db(1,'DB_V9')->query("select * from ".$this->v9_prefix."exhibition where ".$where_city." order by ".$sort.' limit '.$limit);
        }else{
			$where_city = '';
			$return_arr = $this->db(1,'DB_V9')->query("select a.data,b.* from ".$this->v9_prefix."position_data a join ".$this->v9_prefix."exhibition b on a.id = b.id where a.modelid = 17 and a.posid = ".$posid.$where_thumb.$where_city." order by ".$sort.' limit '.$limit);
			foreach($return_arr as &$val){
				$pos_data = string2array($val['data']);
				if(!empty($pos_data['thumb'])){
					$val['thumb'] = $pos_data['thumb'];
				}
			}
		}
		
        
        return $return_arr;
    }

    /**
     * 遍历所有子栏目 找不到父级即为detail中的catid,存入私有变量$v9_children_catids
     * @return array
     */

    public function getVChildrenCatids($catid){
        $children_catids = $this->db(1,'DB_V9')->query("select catid from ".$this->v9_prefix."category where parentid = ".$catid);
        if(!empty($children_catids)){
            foreach($children_catids as $val){
                $this->getVChildrenCatids($val['catid']);
            }
        }else{
            array_push($this->v9_children_catids,$catid);
        }
        return $this->v9_children_catids;
    }

    /**
     * 获取热门专场
     * @param  integer $spaceid
     * @return array
     */
    public function getHotSpecial($spaceid){
        $hot_specials = $this->db(1,'DB_V9')->query("select setting,name from ".$this->v9_prefix."poster where spaceid = ".$spaceid);
        $arr_return = array();
        foreach($hot_specials as $val){
            $temp = string2array($val['setting']);
            $arr_return[] = array('linkurl'=>$temp[1]['linkurl'],'imageurl'=>$temp[1]['imageurl'],'name'=>$val['name']);
        }
        return $arr_return;
    }

    /**
     * 获取收藏知识
     * @param  integer $ispos 是否推荐
     * @param  integer $article_num 文章数
     * @return array
     */
    public function getCollectionKnow($catname,$article_num,$ispos = 0){
		if(empty($catname))
		{
			$arr_cat = $this->db(1,'DB_V9')->query("select catid,catname,url from ".$this->v9_prefix."category where parentid = 204 and description = '' order by catid asc");
			$str_children_ids = '';
			foreach($arr_cat as &$val){
				$arr_children_ids = $this->db(1,'DB_V9')->query("select catid from ".$this->v9_prefix."category where parentid = 204 and description = '".$val['catname']."'");
				foreach($arr_children_ids as $v){
					$str_children_ids .= $v['catid'].',';
				}
			}
		}else{
			$arr_children_ids = $this->db(1,'DB_V9')->query("select catid from ".$this->v9_prefix."category where parentid = 204 and description = '".$catname."'");
			foreach($arr_children_ids as $v){
				$str_children_ids .= $v['catid'].',';
			}
		}
        $str_children_ids = substr($str_children_ids,0,-1);
        if(!$ispos){
            $arr_news = $this->db(1,'DB_V9')->query("select id,title,thumb,url from ".$this->v9_prefix."news where catid in (".$str_children_ids.") order by updatetime desc limit ".$article_num);
        }
        else{
            $arr_news = $this->db(1,'DB_V9')->query("select a.id,a.title,a.thumb,a.url,a.description from ".$this->v9_prefix."news a join ".$this->v9_prefix."position_data b on a.id = b.id where b.posid = 107 and a.catid in (".$str_children_ids.") and a.posids > 0 group by a.id order by a.updatetime desc limit ".$article_num);
        }
        return $arr_news;
    }

    /**
     * 搜索V9资讯展讯
     * @param  string $table 表名
     * @param  string $where 条件
     * @param  array $page 当前页数
     * @param  array $listRow 每页显示数
     * @return array $ads
     */
    public function searchV9($table,$where, $page, $listRow)
    {

        $list = $this->db(1,'DB_V9')->query("select * from ".$this->v9_prefix.$table." where ".$where.' order by updatetime desc limit '.($page-1)*$listRow.','.$listRow);
        $count = $this->db(1,'DB_V9')->query("select count(id) as count from ".$this->v9_prefix.$table." where ".$where)[0]['count'];
        $Page       = new \Think\Page($count, $listRow);
        $show       = $Page->show();
        /*
        $list = $this->where($map)->order('update_time DESC')->page($page.",".$listRow)->select();
        $count = $this->where($map)->count();
        $Page       = new \Think\Page($count, $listRow);
        $show       = $Page->show();
        */
        return array('list'=>$list, 'count'=>$count, 'show'=>$show,);
    }

    /**
     * 尾部导航
     * @return array $ads
     */
    public function footerNav(){
        $catids = $this->db(1,'DB_V9')->query("select arrchildid from ".$this->v9_prefix."category where catid=1");
        $catids = substr($catids[0]['arrchildid'],2);
        $list = $this->db(1,'DB_V9')->query("select catname,url from ".$this->v9_prefix."category where catid in (".$catids.") order by listorder asc");
        return $list;
    }
}
