<?php
namespace Home\Model;
use Think\Model;
/**
 * 论坛基础模型
 */
class UltraxModel extends Model
{
    private $ultrax_prefix = 'pre_';
    /**
     * 获取论坛热贴
     * @return array
     */
    public function getForumHots(){
        $arrHotBbsT = $this->db(3,'DB_ULTRAX')->query("SELECT T.tid, T.`subject`, A.tableid FROM `".$this->ultrax_prefix."forum_thread` T, `".$this->ultrax_prefix."forum_attachment` A WHERE T.moderated = 1 AND T.attachment > 0 AND T.tid = A.tid AND T.`status` = 32 ORDER BY T.tid DESC");
        $tid = $arrHotBbsT[0]['tid'];
        $table_name = $this->ultrax_prefix.'forum_attachment_'.$arrHotBbsT[0]['tableid'];
        $arrHotBbsImg = $this->db(3,'DB_ULTRAX')->query("SELECT `attachment` FROM ".$table_name." WHERE `tid` = ".$tid." ORDER BY `dateline` ASC limit 1");
        $return_arr['img'] = array('url'=>C(BBS_URL).C(BBS_UPFX).$tid.C(BBS_USFX),'title'=>$arrHotBbsT[0]['subject'],'thumb'=>C(BBS_URL).'data/attachment/forum/'.$arrHotBbsImg[0]['attachment']);
        $arrHotBbs = $this->db(3,'DB_ULTRAX')->query("SELECT `tid`, `subject` FROM `".$this->ultrax_prefix."forum_thread` WHERE moderated = 1 AND tid <> ".$tid." AND status = 32 ORDER BY `dateline` DESC limit 14");
        $hotBbsOne = array_shift($arrHotBbs);
        $hotBbsOther = array_chunk($arrHotBbs, 7);
        $return_arr['news'][] = array('url'=>C(BBS_URL).C(BBS_UPFX).$hotBbsOne['tid'].C(BBS_USFX),'title'=>$hotBbsOne['subject']);
        foreach($hotBbsOther[0] as $val){
            $return_arr['news'][] = array('url'=>C(BBS_URL).C(BBS_UPFX).$val['tid'].C(BBS_USFX),'title'=>$val['subject']);
        }
        foreach($hotBbsOther[1] as $val){
            $return_arr['news'][] = array('url'=>C(BBS_URL).C(BBS_UPFX).$val['tid'].C(BBS_USFX),'title'=>$val['subject']);
        }
        return $return_arr;
    }

    /**
     * 获取一周排行
     * @return array
     */
    public function getWeekTop(){
        $arrWeekBbs = $this->db(3,'DB_ULTRAX')->query("SELECT `tid`, `subject` FROM `".$this->ultrax_prefix."forum_thread` WHERE digest = 3 ORDER BY `tid` DESC limit 8");
        foreach($arrWeekBbs as &$val){
            $val['url'] =C(BBS_URL).C(BBS_UPFX).$val['tid'].C(BBS_USFX);
        }
        return $arrWeekBbs;
    }

    /**
     * 获取网友晒宝
     * @return array
     */
    public function getUserShow(){
        $arrHotBbsT = $this->db(3,'DB_ULTRAX')->query("SELECT T.tid, T.`subject`, A.tableid FROM `".$this->ultrax_prefix."forum_thread` T, `".$this->ultrax_prefix."forum_attachment` A WHERE T.moderated = 1 AND T.attachment > 0 AND T.tid = A.tid AND T.`status` = 32 ORDER BY T.tid DESC");
        $tid = $arrHotBbsT[0]['tid'];
        $arrBbsB = $this->db(3,'DB_ULTRAX')->query("SELECT DISTINCT(T.tid), T.`subject` as title, A.tableid FROM `".$this->ultrax_prefix."forum_thread` T, `".$this->ultrax_prefix."forum_attachment` A WHERE T.moderated = 1 AND T.tid <> '.$tid.' AND T.attachment > 0 AND T.tid = A.tid GROUP BY T.tid ORDER BY T.dateline DESC limit 5");
        foreach($arrBbsB as $val){
            $tid = $val['tid'];
            $table_name = $this->ultrax_prefix.'forum_attachment_'.$val['tableid'];
            $arrBbsBImg = $this->db(3,'DB_ULTRAX')->query("SELECT `attachment` FROM ".$table_name." WHERE `tid` = $tid ORDER BY `dateline` ASC");
            $img = C(BBS_URL).'data/attachment/forum/'.$arrBbsBImg[0]['attachment'];
            $url = C(BBS_URL).C(BBS_UPFX).$tid.C(BBS_USFX);
            $return_arr[] = array('url'=>$url,'title'=>$val['title'],'img'=>$img);
        }
        return $return_arr;
    }
}