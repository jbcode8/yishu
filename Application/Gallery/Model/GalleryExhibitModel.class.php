<?php
// +----------------------------------------------------------------------
// | 画廊作品 模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.22
// +----------------------------------------------------------------------

namespace Gallery\Model;

class GalleryExhibitModel extends BaseModel{

    // 自动验证
    protected $_validate = array(
        array('title', 'require', '标题不能为空！'),
        array('starttime', 'require', '开始时间不能为空！'),
        array('endtime', 'require', '结束时间不能为空！'),
    );


    public function update($data = null)
    {
        /* 获取数据对象 */
        $data = $this->create($data);
        //挂载钩子
        $param['recordid'] = I('post.recordid');
        $param['sourceid'] = I('post.sourceid');
        hook('uploadComplete', $param);
        if(empty($data)){
            return false;
        }
        $data['uid'] = $_SESSION['admin_auth']['uid'];
        $data['starttime'] = strtotime($data['starttime']);
        $data['endtime'] = strtotime($data['endtime']);
        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增专题
            $id = $this->add($data); //添加专题
            if(!$id){
                $this->error = '新增出错！';
                return false;
            }
        } else { //更新数据
            $status = $this->save($data); //更新
            if(false === $status){
                $this->error = '更新出错！';
                return false;
            }
        }
        //专题添加或更新完成
        return $data;
    }

    /**
     * 获取展讯
     * @param  integer $map 条件
     * @param  integer $limit 展讯数
     * @return array
     */
    public function getExhibit($limit, $map)
    {
        $gallery_exhibit = $this->field('title,starttime,endtime,gid,recordid,description,id,gid')->limit($limit)->order('starttime DESC')->where($map)->select();
        if($gallery_exhibit){
            foreach($gallery_exhibit as $k=>$v) {
                $gallery_exhibit[$k]['thumb'] = D('Content/Document')->getPic($v['recordid'], 'thumb');
                $gallery_exhibit[$k]['gname'] = getgName($v['gid']);
            }
            return $gallery_exhibit;
        }else{
            return null;
        }
    }

    /**
     * 获取展览地区
     * @param  integer $region_id 地区ID
     * @return array
     */
    public function getRegion($region_id){
        return $this->query("select name from ".$this->tablePrefix."region where id=".$region_id)[0]['name'];
    }
}