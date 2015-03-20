<?php
// +----------------------------------------------------------------------
// | DocumentModel.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

class DocumentModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('title', 'require', '标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,100', '标题长度不能超过100个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('description', '1,140', '简介长度不能超过140个字符', self::VALUE_VALIDATE, 'length', self::MODEL_BOTH),
        array('create_time', '/^\d{4,4}-\d{1,2}-\d{1,2}(\s\d{1,2}:\d{1,2}(:\d{1,2})?)?$/', '日期格式不合法,请使用"年-月-日 时:分"格式,全部为数字', self::VALUE_VALIDATE  , 'regex', self::MODEL_BOTH),
    );

    /* 自动完成 */
    protected $_auto = array(
        array('uid', 'admin_is_login', self::MODEL_INSERT, 'function'),
        array('title', 'htmlspecialchars', self::MODEL_BOTH, 'function'),
        array('description', 'htmlspecialchars', self::MODEL_BOTH, 'function'),
        array('create_time', 'time', self::MODEL_BOTH,'function'),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
    );

    /**
     * 详细内容
     * @param $id
     * @return array|bool|mixed
     */
    public function detail($id){
        /* 获取基础数据 */
        $info = $this->field(true)->find($id);
        if(!(is_array($info) || 1 !== $info['status'])){
            $this->error = '文档被禁用或已删除！';
            return false;
        }

        /* 获取模型数据 */
        $logic  = $this->logic($info['model']);
        $detail = $logic->detail($id); //获取指定ID的数据
        if(!$detail){
            $this->error = $logic->getError();
            return false;
        }
        $info = array_merge($info, $detail);

        return $info;
    }

    /**
     * 新增或更新一个文档
     * @param array  $data 手动传入的数据
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     */
    public function update($data = null){

        /* 获取数据对象 */
        $data = $this->create($data);
        if(empty($data)){
            return false;
        }

        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增数据
            $id = $this->add(); //添加基础内容
            if(!$id){
                $this->error = '新增基础内容出错！';
                return false;
            }
        } else { //更新数据
            $status = $this->save(); //更新基础内容
            if(false === $status){
                $this->error = '更新基础内容出错！';
                return false;
            }
        }

        /* 添加或新增扩展内容 */
        $logic = $this->logic($data['model']);
        if(!$logic->update($id)){
            if(isset($id)){ //新增失败，删除基础数据
                $this->delete($id);
            }
            $this->error = $logic->getError();
            return false;
        }

        $id = $id?$id:$data['id'];
        $this->position($id,$data);
        //内容添加或更新完成
        return $data;
    }

    public function delData($id,$model){
        if($this->delete($id)){
            $logic = $this->logic($model);
            if(!$logic->delData($id)){
                $this->error = $logic->getError();
                return false;
            }
            return true;
        }else{
            $this->error = '删除失败';
            return false;
        }
    }

    /**
     * 推荐位
     * @param $id
     * @param $data
     */
    public function position($id,$data){
        //获取需要推荐的字段
        $pos_field = get_document_model($data['model'],'position_field');
        $array = array();
        $posdata = array();
        foreach(explode(',',$pos_field) as $v){
            $posdata[$v] = $data[$v];
        }
        $posid = $_POST['posid'];
        foreach($posid as $k=>$v){
            $array[$v]['id'] = $id;
            $array[$v]['catid'] = $data['catid'];
            $array[$v]['model_id'] = $data['model'];
            $array[$v]['posid'] = $v;
            $array[$v]['is_thumb'] = $data['thumb'];
            $array[$v]['data'] = serialize($posdata);
        }
        //判断是否是新增数据
        if(!empty($data['id'])){
            $posDataModel = M('PositionData');
            //获取已存在的推荐位
            $pos = $posDataModel->where(array('id'=>$id,'model_id'=>$data['model']))->getField('posid',true);
            $posDataModel->where(array('posid'=>array('IN',$pos),'model_id'=>$data['model'],'id'=>$id))->delete();  
        }
        shuffle($array);
        M('PositionData')->addAll($array);
    }

    /**
     * 获取扩展模型对象
     * @param  integer $model 模型编号
     * @return object         模型对象
     */
    private function logic($model){
        return D(get_document_model($model, 'name'), 'Logic');
    }
} 