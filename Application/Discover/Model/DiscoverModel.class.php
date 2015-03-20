<?php
// +----------------------------------------------------------------------
// | 画廊作品 模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.22
// +----------------------------------------------------------------------

namespace Discover\Model;
use Think\Model;

class DiscoverModel extends Model{
    protected $tablePrefix = 'yishu_';

    // 自动验证
    protected $_validate = array(
        array('name', 'require', '作品名称不能为空！'),
        array('author', 'require', '作家名称不能为空！'),
        array('type', 'require', '作品类型不能为空！'),
        array('price', 'require', '作品价格不能为空！'),
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

}