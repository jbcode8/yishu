<?php
// +----------------------------------------------------------------------
// | 画廊作品 模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.22
// +----------------------------------------------------------------------

namespace Gallery\Model;

class GalleryNewsModel extends BaseModel{

    // 自动验证
    protected $_validate = array(
        array('title', 'require', '标题不能为空！'),
        array('gid', 'require', '请选择画廊！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time','time',1,'function'),
    );

    public function update($data = null)
    {
        /* 获取数据对象 */
        $data = $this->create($data);
        if(empty($data)){
            return false;
        }
        $data['create_time'] = time();
        $data['uid'] = $_SESSION['admin_auth']['uid'];
        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增
            $id = $this->add($data); //添加
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
     * 获取画廊资讯
     * @param  integer $map 条件
     * @param  integer $limit 大师数
     * @return array
     */
    public function getNews($limit, $map)
    {
        return $this->field('id,title,description,gid')->limit($limit)->order('create_time DESC')->where($map)->select();
    }

}