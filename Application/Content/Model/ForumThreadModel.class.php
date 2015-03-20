<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Content\Model;
use Think\Model;

/**
 * 论坛主题模型
 */
class ForumThreadModel extends Model{
    protected $tablePrefix = 'pre_';
    protected $connection = 'mysql://root:3712601@192.168.8.221:3306/ultrax#utf8';
}
