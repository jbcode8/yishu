<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 搜索关键字模型
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.18
// +----------------------------------------------------------------------

namespace Mall\Model;

class KeywordsModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('words', 'require', '请输入关键字！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function')
    );

    // 新增关键字，对于已经存在的只需更新(搜索)次数
    public function addWords($kw, $type = 1){
        $info = $this->where(array('words'=>$kw,'type'=>$type))->find();
        if(empty($info)){
            $id = $this->data(array('words'=>$kw, 'type'=>1, 'create_time'=>time()))->add();
        }else{
            $this->where(array('key_id'=>$info['key_id']))->setInc('hits');
			$id = $this->where(array('key_id'=>$info['key_id']))->getField('key_id');
        }
		return $id;
    }
}