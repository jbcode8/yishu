<?php
// +----------------------------------------------------------------------
// | PhpStorm
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

class AuthGroupModel extends Model {

    protected $_validate = array(
        array('title','require','用户组名称不能为空!', self::MUST_VALIDATE),
    );

    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT),
    );

    /**
     * 将用户添加权限组
     * @param $uid
     * @param $gid
     * @return bool
     */
    public function addToGroup($uid,$gid){
        $uid = is_array($uid)?implode(',',$uid):trim($uid,',');
        $gid = is_array($gid)?$gid:explode( ',',trim($gid,',') );

        $Access = M('AuthGroupAccess');
        if( isset($_REQUEST['batch']) ){
            //为单个用户批量添加用户组时,先删除旧数据
            $del = $Access->where( array('uid'=>array('in',$uid)) )->delete();
        }

        $uid_arr = explode(',',$uid);
        $uid_arr = array_diff($uid_arr,array(C('USER_ADMINISTRATOR')));
        $add = array();
        if( $del!==false ){
            foreach ($uid_arr as $u){
                foreach ($gid as $g){
                    if( is_numeric($u) && is_numeric($g) ){
                        $add[] = array('group_id'=>$g,'uid'=>$u);
                    }
                }
            }
            $Access->addAll($add);
        }
        if ($Access->getDbError()) {
                $this->error = "不能重复添加"; //TODO 此处需要更细化
            return false;
        }else{
            return true;
        }
    }

    /**
     * 检查id是否全部存在
     * @param $modelname
     * @param $mid
     * @param string $msg
     * @return bool
     */
    public function checkId($modelname,$mid,$msg = '以下id不存在:'){
        if(is_array($mid)){
            $count = count($mid);
            $ids   = implode(',',$mid);
        }else{
            $mid   = explode(',',$mid);
            $count = count($mid);
            $ids   = $mid;
        }

        $s = M($modelname)->where(array('id'=>array('IN',$ids)))->getField('id',true);
        if(count($s)===$count){
            return true;
        }else{
            $diff = implode(',',array_diff($mid,$s));
            $this->error = $msg.$diff;
            return false;
        }
    }

    /**
     * 检查用户组是否全部存在
     * @param $gid
     * @return bool
     */
    public function checkGroupId($gid){
        return $this->checkId('AuthGroup',$gid, '以下用户组id不存在:');
    }

    /**
     * 用户组删除后删除对应权限
     * @param $data
     * @param $option
     */
    public function _after_delete($data,$option){
        M('AuthGroupAccess')->where(array('group_id'=>$data['id']))->delete();
    }
} 