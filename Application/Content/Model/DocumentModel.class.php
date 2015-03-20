<?php

namespace Content\Model;
use Think\Model;
use Addons\Attachment\Model\AttachmentModel;
use Addons\Attachment\Model\RecordModel;
/**
 * 文档基础模型
 */
class DocumentModel extends Model
{
    /**
     * 获取文档列表
     * @param  integer  $catid 分类ID
     * @param  string   $order    排序规则
     * @param  integer  $status   状态
     * @param  boolean   $field    字段 true-所有字段
     * @return array
     */
    public function lists($catid, $order = '`update_time` DESC', $status = 1, $field = true){
        $map = $this->listMap($catid, $status);
        $return = $this->field($field)->where($map)->order($order)->select();
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
     * 获取文章详细信息不关联子表
     * @param  integer $id   文章id
     * @return array
     */
    public function detailNorelation($id){
        /* 获取基础数据 */
        $info = $this->field(true)->find($id);
        if(!(is_array($info) || 1 !== $info['status'])){
            $this->error = '文档被禁用或已删除！';
            return false;
        }
        return $info;
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
	 * @param  integer $goods_id 商品id
     * @return array
     */
    public function getPic($recordid, $type,$goods_id = null,$select = 5,$third = 0)
    {
		if($recordid){ //本站图片
			if(!$third){
				$record = new RecordModel();
				$attachement = new AttachmentModel();
				$sourceid = $record->where(array('recordid' => intval($recordid)))->field('sourceid')->select();
				if(!empty($sourceid)){
					$sourceids = '';
					foreach($sourceid as $val){
						$sourceids .= $val['sourceid'].',';
					}
					$sourceids = substr($sourceids,0,-1);
					//$map['_id'] = array('IN', $sourceid);
					//$map['type'] = $type;
					$map = "type = '".$type."' and _id in (".$sourceids.")";
					$res = $attachement->where($map)->field('savepath,savename,name,addonDesc')->select();
					if($type =='thumb' || $type =='image') {
						if($type =='thumb'){
							$thumb = '';
							foreach($res as $val){
								$thumb = $val['savepath'].$val['savename'];
							}
							return $thumb;
						}elseif($type =='image'){
							$images = array();
							foreach($res as $val){
								$images[] = $val['savepath'].$val['savename'];
							}
							return $images;
						}
					} else {
						return $res;
					}
				}
			}else{
				if($type == 'thumb'){
					$images = M('paimai_goods')->where('goods_id='.$goods_id)->getField('third_index_img');
				}else{			
					$arr = M('paimai_gallery')->field('img_url')->where('goods_id='.$goods_id)->limit($select)->select();
					$images = array();
					foreach($arr as $val){
						$images[] = $val['img_url'];
					}
				}
				return $images;
			}
		}else{ //第三方站点图片			
			if($type == 'thumb'){
				$images = M('paimai_goods')->where('goods_id='.$goods_id)->getField('third_index_img');
			}else{			
				$arr = M('paimai_gallery')->field('img_url')->where('goods_id='.$goods_id)->limit($select)->select();
				$images = array();
				foreach($arr as $val){
					$images[] = $val['img_url'];
				}
			}
			return $images;
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
		$res = $this->field($field)->where($map)->order($order)->limit($limit)->select();
		if($thumb) {
            foreach($res as $k =>$val) {
                $res[$k]['url'] = $this->getPic($val['recordid'], 'thumb');
            }
        }
		return $res;
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
}
