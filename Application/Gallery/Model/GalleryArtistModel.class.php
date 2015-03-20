<?php
// +----------------------------------------------------------------------
// | 画廊大师 模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.22
// +----------------------------------------------------------------------

namespace Gallery\Model;
use Content\Model\DocumentModel;

class GalleryArtistModel extends BaseModel{

    // 自动验证
    protected $_validate = array(
        array('gid', 'require', '请选择画廊！', 0),
        array('name', 'require', '请填写大师姓名！'),
        array('name', '', '此大师已存在！', 1, 'unique'),
        array('brief', 'require', '请填写大师简介！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time','time',1,'function'),
    );

    // 添加之后更新缓存
    public function _after_insert($data, $options) {
        $this->caches();
    }

    // 修改之后更新缓存
    public function _after_update($data, $options) {
        $this->caches();
    }

    // 删除之后更新缓存
    public function _after_delete($data, $options){
        $this->caches();
    }

    // 创建缓存数据
    function caches($isReturn = false){
        $cache = array();
        $arr = $this->where('status > 0')->field(array('id', 'name', 'avatar', 'gid'))->order('listorder ASC')->select();
        if(!empty($arr)){
            foreach($arr as $rs){$cache[$rs['id']] = $rs;}
        }
        S('GalleryArtist', $cache);
        if($isReturn){ return $cache; }
    }

    // 获取缓存数据且返回
    function getCaches(){

        // 获取缓存数据
        $arrCache = S('GalleryArtist');

        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }

    /**
     * 通过艺术家id获取作家姓名
     * @param  integer $id 艺术家id
     * @return array
     */
    public function getOneArtist($id) {
        return $this->where(array('id'=>$id))->getField('name');
    }
    /**
     * 获取艺术家信息
     * @param  array $map 条件
     * @param  integer $limit 大师数
     * @return array
     */
    public function getArtist($limit, $map)
    {
        $gallery_artist= $this->field('id,recordid,name')->limit($limit)->order('create_time DESC')->where($map)->select();
        if($gallery_artist){
            foreach($gallery_artist as $k=>$v) {
                $gallery_artist[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
            }
            return $gallery_artist;
        }else{
            return null;
        }


    }
}