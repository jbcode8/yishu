<?php
namespace Special\Model;

class TempModel extends SpecialModel{
    protected $_validate = array(
        array('title', 'require', '专题名称不能为空'),
        array('title', '', '专题名称已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT),
        array('name', 'require', '专题标识不能为空'),
        array('name', '', '专题标识已经存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT),
        array('create_time', 'require', '创建时间不能为空'),
        array('template', 'require', '模板内容不能为空'),
    );
    /**
     *	自动完成
     */
    protected $_auto = array(
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 新增或更新一个专题
     * @param array  $data 手动传入的数据
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     */
    public function update($data = null)
    {
        /* 获取数据对象 */
        $data = $this->create($data);
        if(empty($data)){
            return false;
        }
        $data['create_time'] = strtotime($data['create_time']);
        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增专题
            $id = $this->add($data); //添加专题
            if(!$id){
                $this->error = '新增专题出错！';
                return false;
            }
        } else { //更新数据
            $status = $this->save($data); //更新专题
            if(false === $status){
                $this->error = '更新专题出错！';
                return false;
            }
        }
        //专题添加或更新完成
        return $data;
    }

}
