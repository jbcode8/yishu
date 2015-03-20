<?php
// +----------------------------------------------------------------------
// | 艺术百科用户贡献模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Model;


class ContributionModel extends BaikeModel {


    /**
     * 检测用户存在用户贡献记录
     * @param $uid
     * @return bool
     */
    private function checkUser($uid){
       return  $this->where(array('uid'=>$uid))->select() || false;
    }

    /**
     * 增加用户的创建贡献
     * @param $uid
     */
    public function addCreates($uid){
        $data['uid'] = $uid;
        if($this->checkUser($uid)){
            $data['creates'] = array('exp'=>'`creates`=`creates`+1');
            $this->save($data);
        }
        else{
            $data['creates'] = 1;
            $data['edits'] = 0;
            $this->add($data);
        }
    }

    /**
     * 增加用户的编辑贡献
     * @param $uid
     */
    public function addEdits($uid){
        $data['uid'] = $uid;
        if($this->checkUser($uid)){
            $data['edits'] = array('exp'=>'`edits`=`edits`+1');
            $this->save($data);
        }
        else{
            $data['creates'] = 0;
            $data['edits'] = 1;
            $this->add($data);
        }
    }

    /**
     * 贡献计算
     * @param int $creates  创建次数
     * @param int $edits    编辑次数
     * @return int
     */
    public function getValue($creates=0, $edits=0){
        return ((int)$creates*50)+((int)$edits*10);
    }

} 