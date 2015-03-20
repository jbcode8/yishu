<?php
namespace Exhibit\Model;
use Think\Model;
use Addons\Attachment\Model\AttachmentModel;
use Addons\Attachment\Model\RecordModel;

class ExhibitModel extends Model{
    protected $tablePrefix = 'yishu_';
    protected $v9_prefix = 'v9_';
    /**
     * 获取文章图片
     * @param  integer $recordid 关联id
     * @param  string $type 图片类型
     * @return array
     */
    public function getPic($recordid, $type = 'image')
    {
        $record = new RecordModel();
        $attachement = new AttachmentModel();
        $sourceid = $record->where(array('recordid' => intval($recordid)))->field('sourceid')->select();

        if(!empty($sourceid)){
            $sourceids = '';
            foreach($sourceid as $val){
                $sourceids .= $val['sourceid'].',';
            }
            $sourceids = substr($sourceids,0,-1);
            //$map['_id'] = array('IN', $sourceid);
            //$map['type'] = $type;
            $map = "type = '".$type."' and _id in (".$sourceids.")";
            $res = $attachement->where($map)->getField('savepath,savename,name,addonDesc');
            if($type =='thumb' || $type =='image') {
                if($type =='thumb'){
                    $thumb = '';
                    foreach($res as $val){
                        $thumb = $val['savepath'].$val['savename'];
                    }
                    return $thumb;
                }elseif($type =='image'){
                    $images = array();
                    foreach($res as $val){
                        $images[] = $val['savepath'].$val['savename'];
                    }
                    return $images;
                }
            } else {
                return $res;
            }
        }
    }

    /**
     * 获取首页日历展讯
     * @return array
     */
    public function getHomeExhibit($time){
        $home_exhibit = $this->db(1,'DB_V9')->query("SELECT e.title, e.starttime, e.endtime, ed.address FROM ".$this->v9_prefix."exhibition as e ,".$this->v9_prefix."exhibition_data  as ed WHERE e.id = ed.id AND e.islink=0 AND UNIX_TIMESTAMP(e.starttime) < ".$time." ORDER BY e.starttime DESC LIMIT 1");
        return $home_exhibit;
    }

}