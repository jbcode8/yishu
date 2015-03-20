<?php
namespace Auction\Model;
use Think\Model;
class HomeAuctionModel extends Model {
    /**
     * 拍卖收藏
     * @param  integer $limit   记录条数
     * @return array
     */
    public function auctionCollection($limit){
        $arrAucExhi = $this->query("SELECT M.id,M.starttime,M.name AS eName,Y.name AS aName FROM ".$this->tablePrefix."auction_meeting M,".$this->tablePrefix."auction_agency Y WHERE M.agencyid = Y.id AND UNIX_TIMESTAMP(M.starttime) > '.time().' AND M.pid=0 AND M.status=0 AND Y.status=1  ORDER BY M.starttime DESC limit ".$limit);
        $arrAucExhi2 = $this->query("SELECT M.id,M.starttime,M.name AS eName,M.money AS total,Y.name AS aName FROM ".$this->tablePrefix."auction_meeting M,".$this->tablePrefix."auction_agency Y WHERE M.agencyid = Y.id AND UNIX_TIMESTAMP(M.endtime) < ".time()." AND M.pid=0 AND M.status=0 AND Y.status=1 AND M.money > 0 ORDER BY M.endtime DESC limit ".$limit);
        return array('preview'=>$arrAucExhi,'result'=>$arrAucExhi2);
    }

    /**
     * 热门拍品
     * @param  integer $limit   记录条数
     * @return array
     */
    public function auctionHots($limit){
        $arrAucExhi = $this->query("SELECT `id`,`name`,`price`,`thumb` FROM ".$this->tablePrefix."auction_exhibit WHERE `isshow` = 1 ORDER BY `addtime` DESC limit ".$limit);
        return $arrAucExhi;
    }

    /**
     * 在线拍卖
     * @param  integer $limit   记录条数
     * @param  integer $today   是否今日拍品 首页左上角滚动 取所有的记录
     * @return array
     */
    public function auctionOnline($limit,$today=0){
        /*
        $arrOnline = $this->query("SELECT A.id,A.title,A.startprice,A.currentprice,G.thumb,A.hits FROM ".$this->tablePrefix."auction_data A,".$this->tablePrefix."auction_goods G WHERE A.isok = 0 AND A.gid = G.id ORDER BY A.addtime DESC limit ".$limit);
        $OnlineOne = array_shift($arrOnline);
        return array('first'=>$OnlineOne,'others'=>$arrOnline);
        */
        $today_start = strtotime(date('Y-m-d',time()));
        $today_end = $today_start+86400;
        $where_time = '';
        if($today){
            $where_time = ' and B.goods_starttime>='.$today_start.' and B.goods_starttime<'.$today_end;
            $limit = ' limit '.$limit.',1';
        }else{  //在线拍卖显示正在拍的拍品
            //$where_time .= ' and B.goods_status=1';
            $limit = " limit ".$limit;
        }
        $arrOnline = $this->query("SELECT B.* FROM ".$this->tablePrefix."paimai_special A join ".$this->tablePrefix."paimai_goods B on A.special_id=B.goods_specialid WHERE A.special_status=1 and A.special_isshow=1 and A.special_isdelete=0 and B.goods_isshow=1 and B.goods_isdelete=0 ".$where_time." ORDER BY B.goods_starttime DESC ".$limit);
		if(!empty($arrOnline)){
			foreach($arrOnline as $k=>&$v) {
				$v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
			}
			if($today){
				$today_count = $this->query("SELECT count(B.goods_id) as count FROM ".$this->tablePrefix."paimai_special A join ".$this->tablePrefix."paimai_goods B on A.special_id=B.goods_specialid WHERE A.special_status=1 and A.special_isshow=1 and A.special_isdelete=0 and B.goods_isshow=1 and B.goods_isdelete=0 ".$where_time);
				$arrOnline[0]['length'] = $today_count[0]['count'];
			}
		}else{ //今天没有数据取有数据的最新一天
			$arrLastDataDate = $this->query("SELECT B.goods_starttime FROM ".$this->tablePrefix."paimai_special A join ".$this->tablePrefix."paimai_goods B on A.special_id=B.goods_specialid WHERE A.special_status=1 and A.special_isshow=1 and A.special_isdelete=0 and B.goods_isshow=1 and B.goods_isdelete=0 ORDER BY B.goods_starttime DESC ".$limit);
			$day_start = strtotime(date('Y-m-d',$arrLastDataDate[0]['goods_starttime']));
			$day_end = $today_start+86400;
			$where_time = ' and B.goods_starttime>='.$day_start.' and B.goods_starttime<'.$day_end;
			$arrOnline = $this->query("SELECT B.* FROM ".$this->tablePrefix."paimai_special A join ".$this->tablePrefix."paimai_goods B on A.special_id=B.goods_specialid WHERE A.special_status=1 and A.special_isshow=1 and A.special_isdelete=0 and B.goods_isshow=1 and B.goods_isdelete=0 ".$where_time." ORDER BY B.goods_starttime DESC ".$limit);
			foreach($arrOnline as $k=>&$v) {
				$v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
			}
			if($today){
				$today_count = $this->query("SELECT count(B.goods_id) as count FROM ".$this->tablePrefix."paimai_special A join ".$this->tablePrefix."paimai_goods B on A.special_id=B.goods_specialid WHERE A.special_status=1 and A.special_isshow=1 and A.special_isdelete=0 and B.goods_isshow=1 and B.goods_isdelete=0 ".$where_time);
				$arrOnline[0]['length'] = $today_count[0]['count'];
			}
		}
        return $arrOnline;
    }

    /**
     * 拍卖专场 预告
     * @param  integer $ispre   是否预展
     * @param  integer $limit   记录条数
     * @return array
     */
    public function auctionSpecial($ispre,$limit){
        /*
        if(!$ispre){
            $where_pre = "pre_starttime <=".time()." and pre_endtime >=".time();
        }else{
            $where_pre = "pre_starttime >".time();
        }
        $arrSpecial = $this->query("SELECT id,name,starttime,endtime,thumb from ".$this->tablePrefix."auction_meeting where ".$where_pre." and pid>0 and status=0 order by id desc limit ".$limit);
        foreach($arrSpecial as &$val){
            $goods_num = $this->query("SELECT count(id) as goods_num from ".$this->tablePrefix."auction_exhibit where specialid=".$val['id']." and isshow=1");
            $val['goods_num'] = $goods_num[0]['goods_num'];
        }
        return $arrSpecial;
        */
        if(!$ispre){
            $where_pre = "special_starttime <=".time()." and special_endtime >=".time();
        }else{
            $where_pre = "special_starttime >".time();
        }
        $arrSpecial = $this->query("SELECT * from ".$this->tablePrefix."paimai_special where ".$where_pre." and special_status=1 and special_isshow=1 and special_isdelete=0 order by special_id desc limit ".$limit);
        foreach($arrSpecial as &$val){
            $goods_num = $this->query("SELECT goods_id,count(goods_id) as goods_num from ".$this->tablePrefix."paimai_goods where goods_specialid=".$val['special_id']." and goods_isdelete=0 and goods_isshow=1");
            $val['goods_num'] = $goods_num[0]['goods_num'];
			$val['goods_people_num'] = 0;
            if($val['goods_num'] == 0){
                $val['goods_people_num'] = 0;
            }else{
				foreach($goods_num as $v){
					$goods_people_num = $this->query("select count(distinct(bidrecord_uid)) as people_num from ".$this->tablePrefix."paimai_bidrecord where bidrecord_goodsid=".$v['goods_id']);
					$val['goods_people_num'] += $goods_people_num[0]['people_num'];
				}
                
            }
        }
        foreach($arrSpecial as $k=>&$v) {
            $v['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
        }
        return $arrSpecial;
    }

}