<?php
// +----------------------------------------------------------------------
// | PositionDataModel.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Malladmin\Model;
use Think\Model;

class PositionDataMallModel extends Model {

    /**
     * select查询后执行data反序列化
     * @param $data
     * @param $opt
     */
    public function  _after_select(&$data,$opt){
        foreach($data as $k=>$v){
            $data[$k]['data'] = unserialize($v['data']);
        }
    }

    /**
     * find查询后执行data反序列化
     * @param $data
     * @param $opt
     */
    public function _after_find(&$data,$opt){
        $data['data'] = unserialize($data['data']);
    }

    /**
     * 保存推荐位数据
     * @param $post post提交过来的数据
     * @param $data 原数据库数据
     * @return bool
     */
    public function saveData($post,$data){
        $newData = array_merge($data,$post);
        $newData['data']['update_time'] = strtotime($newData['data']['update_time']);
        $newData['data'] = serialize($newData['data']);
        if($this->save($newData,array('id'=>$data['id'],'posid'=>$data['posid'],'model_id'=>$data['model_id']))){
            return true;
        }
        $this->error = '更新错误!';
        return false;
    }
} 