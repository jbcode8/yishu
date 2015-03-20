<?php
namespace Ad\Model;

/**
 * Space模型
 */
class SpaceModel extends AdModel{
    protected $_validate = array(
        array('spacename', 'require', '广告位名称不能为空'),
        array('spacename', '', '广告位名称已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT),
        array('spaceheight', 'require', '广告位高度不能为空'),
        array('spacewidth', 'require', '广告位宽度不能为空'),
    );

    /**
     * 添加之后更新缓存
     */
    public function _after_insert($data, $options)
    {
        $this->caches();
    }

    /**
     * 修改之后更新缓存
     */
    public function _after_update()
    {
        $this->caches();
    }

    /**
     * 删除之后更新缓存
     */
    public function _after_delete($data, $options)
    {
        $this->caches();
    }

    /**
     * 创建缓存数据
     */
    function caches($isReturn = false){
        $arr = $this->field(array('id', 'spacename', 'spaceheight', 'spacewidth', 'spaceintro',  'adshow', 'adcount', 'listorder'))->order('listorder DESC')->select();
        S('AdSpace', $arr);
        if($isReturn){ return $arr; }
    }

    /**
     * 获取缓存数据且返回
     */
    function getSpaceCache(){

        // 获取缓存数据
        $arrCache = S('AdSpace');

        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }

    /**
     * 获取广告位
     */
    public function getSpace(){
        foreach($this->getSpaceCache() as $key=>$v){
            $spacename[$key]['name'] = $v['spacename'];
            $spacename[$key]['id'] = $v['id'];
        }
        return $spacename;
    }


}
