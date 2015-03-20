<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-13
 * Time: 下午4:18
 */

namespace Paimai\Model;


use Think\Model;

class PaimaiCommentModel extends Model{
    /**
     * 自动验证
     */
    protected $_validate = array(
        array("comment_content","require","评论内容不能为空"),
        // array("goods_starttime","0,30","专场不能大于30个字符","3","length"),
    );
    //自动完成
    protected $_auto = array (
        array("comment_createtime","time","1","function"),
    );
} 