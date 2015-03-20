<?php
namespace Identify\Model;
use Think\Model;
class HomeIdentifyModel extends Model {
    /**
     * 在线鉴定
     * @param  integer $limit   记录条数
     * @return array
     */
    public function identifyOnline($limit){
        $arrIdentify = $this->query("SELECT `id`,`name`,`thumb`,`createtime`,`hits`,`question` FROM ".$this->tablePrefix."identify_data WHERE `isok` > 0 ORDER BY `createtime` DESC limit ".$limit);
        $arrIdentify2 = $this->query("SELECT `id`,`name`,`thumb`,`createtime`,`hits`,`question` FROM ".$this->tablePrefix."identify_data WHERE `isok` = 0 ORDER BY `createtime` DESC limit ".$limit);
        return array('identify'=>$arrIdentify,'noidentify'=>$arrIdentify2);
    }

}