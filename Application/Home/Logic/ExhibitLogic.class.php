<?php
// +----------------------------------------------------------------------
// | ExhibitLogic.class.php
// +----------------------------------------------------------------------
// | Author: mahongbing
// +----------------------------------------------------------------------
namespace Home\Logic;


class ExhibitLogic extends BaseLogic{

    protected $_auto = array(
        array('pictureurls', 'serialize', self::MODEL_BOTH, 'function'),
        array('starttime', 'strtotime', self::MODEL_BOTH, 'function'),
        array('endtime', 'strtotime', self::MODEL_BOTH, 'function'),
    );

    public function _after_select(&$data,$opt){
        foreach($data as $k=>$v){
            $data[$k]['pictureurls'] = unserialize($data[$k]['pictureurls']);
        }
    }

    /**
     * find查询后图片组做反序列化
     * @param $data
     * @param $opt
     */
    public function _after_find(&$data,$opt){
        $data['pictureurls'] = unserialize($data['pictureurls']);
    }

    public function update($id = 0){
        /* 获取文章数据 */
        $data = $this->create();
        if($data === false){
            return false;
        }

        /* 添加或更新数据 */
        if(empty($data['id'])){//新增数据
            $data['id'] = $id;
            $id = $this->add($data);
            if(!$id){
                $this->error = '新增详细内容失败！';
                return false;
            }
        } else { //更新数据
            $status = $this->save($data);
            if(false === $status){
                $this->error = '更新详细内容失败！';
                return false;
            }
        }

        return true;
    }

} 