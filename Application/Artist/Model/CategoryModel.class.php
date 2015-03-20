<?php
// +----------------------------------------------------------------------
// | 艺术家分类模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Model;


class CategoryModel extends ArtistModel {

    protected $_validate = array(
        array('name', 'require', '分类名称不能为空', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '分类名称已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT),
        array('listorder', '0,100', '分类排序只能为 0 至 100 的数字', self::EXISTS_VALIDATE, 'between' , self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('status', '_getStatus', self::MODEL_BOTH, 'callback'),
    );

    public function _getStatus(){
        if(isset($_POST['status']) && $_POST['status']==1)
            return 1;
        return 0;
    }

    //艺术家分类
    public function getCategory(){
        $category_list = $this->field('id,name')->order('id desc')->select();
        return $category_list;
    }



}