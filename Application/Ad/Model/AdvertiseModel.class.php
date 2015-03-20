<?php

namespace Ad\Model;
use Addons\Attachment\Model\AttachmentModel;
use Addons\Attachment\Model\RecordModel;


/**
 * Advertise模型
 */
class AdvertiseModel extends AdModel
{
    protected $_validate = array(
        array('title', 'require', '广告标题不能为空'),
        array('title', '', '广告标题已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT),
        array('starttime', 'require', '开始时间不能为空'),
        array('endtime', 'require', '结束时间不能为空'),
        array('content', 'require', '广告内容不能为空'),
    );

    /**
     * 新增或更新一个广告
     * @param array  $data 手动传入的数据
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     */
    public function update($data = null)
    {
        $space = D('Space');
        /* 获取数据对象 */
        $data = $this->create($data);
        if(empty($data)){
            return false;
        }
        //时间处理
        $data['starttime'] = strtotime($data['starttime']);
        $data['endtime'] = strtotime($data['endtime']);
        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增广告
            $id = $this->add($data); //添加广告
            if(!$id){
                $this->error = '新增广告出错！';
                return false;
            } else {
                //修改广告下的广告数
                $space->where(array('id'=>$data['sid']))->setInc('adcount');
            }
        } else { //更新数据
            $status = $this->save($data); //更新广告
            if(false === $status){
                $this->error ='更新广告出错！';
                return false;
            } else {
                $pastsid = I('post.pastsid', '', 'intval');
                if( $pastsid != $data['sid']){
                    $space->where(array('id'=>$pastsid))->setDec('adcount');
                    $space->where(array('id'=>$data['sid']))->setInc('adcount');
                }
            }
        }
        //广告添加或更新完成
        return $data;
    }

    /**
     * 获取广告
     * @param  integer $sid 广告位id
     * @param  integer $adShow 显示广告数
     * @return array $ads
     */
    public function getAdv($sid, $adShow)
    {
        $res = $this->where(array('sid'=>$sid, 'status'=>1))->order('starttime')->limit($adShow)->select();
        $ads = '';
        foreach($res as $k=>$v) {
            if($v['type'] == 0) {
                $ads .= "<a href='".$v['url']."' target='_blank'>".$v['content']."</a>";
            } elseif($v['type'] == 1){
                $ads .= "<a href='".$v['url']."' target='_blank'><img src='".$this->getImg(intval($v['recordid']))."'></a>";
            } elseif($v['type'] == 2) {
                $ads .= "<script>".htmlspecialchars_decode($v['content'])."</script>";
            } else {
                $ads .= htmlspecialchars_decode($v['content']);
            }
        }
        return $ads;
    }

    /**
     * 获取广告图片
     * @param  integer $recordid 关联id
     * @return array
     */
    public function getImg($recordid)
    {
        $record =  new RecordModel();
        $attachment =  new AttachmentModel();
        $sourceid = $record->where(array('recordid'=>$recordid))->getField('sourceid');
        $res = $attachment->where(array('_id'=>$sourceid[0]))->getField('savepath,savename');
        return $res[0]['savepath'].$res[0]['savename'];
    }

    /**
     * 通过广告id获取广告位id
     * @param  integer $id 广告id
     * @return array $sid
     */
    public function getSid($id)
    {
        $sid =$this->where("id=$id")->getField('sid');
        return $sid;
    }




}
