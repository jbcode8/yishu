<?php
namespace Message\Model;

/**
 * Category模型
 */
class CategoryModel extends MessageModel{
    protected $_validate = array(
        array('name', 'require', '留言板名称不能为空'),
        array('name', '', '留言板名称已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT),
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
        $arr = $this->order('listorder DESC')->select();
        S('Message', $arr);
        if($isReturn){ return $arr; }
    }

    /**
     * 获取缓存数据且返回
     */
    function getMessageCache(){

        // 获取缓存数据
        $arrCache = S('Message');

        // 如果不存在则读取数据库且创建
        if(!$arrCache){
            return $this->caches(true);
        }else{
            return $arrCache;
        }
    }

    /**
     * 获取留言板
     */
    public function getMessageBoard()
    {
        $result = $this->field('id,name')->select();
        foreach($result as $v) {
            $list[$v['id']] = $v['name'];
        }
        return $list;
    }



}
