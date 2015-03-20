<?php
// +----------------------------------------------------------------------
// | 大师频道基类模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Artist\Model;
use Think\Model;
use Think\Model\RelationModel;
class ArtistModel extends RelationModel {

    //大师模块表前缀
    public  $tablePrefix = 'yishu_artist_';

}