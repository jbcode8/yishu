<?php

// +----------------------------------------------------------------------
// | 店铺类别 模型
// +----------------------------------------------------------------------
// | Author: Rain.Zen
// +----------------------------------------------------------------------

namespace Mall\Model;

class StoreCategoryModel extends MallBaseModel{

    // 自动验证
    protected $_validate = array(
        array('cate_name', 'require', '请填写类别名称！'),
        array('pid', 'require', '请选择顶级类别！'),
    );

    // 自动完成
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
    );

    // 读取数据
    public function aryStoreCate($store_id = 0, $pid = 0){
        $ary = array();
        $where = empty($store_id) ? '1 = 1' : array('store_id'=>$store_id);
        !empty($pid) && !empty($store_id) AND $where['pid'] = $pid;
        $data = $this->field(array('id', 'cate_name', 'pid', 'listorder'))->where($where)->order('listorder ASC')->select();
        if($data){
            foreach($data as $rs){
                $ary[$rs['id']] = $rs;
            }
        }
        return $ary;
    }

    // 顶级类别的下拉控件
    public function tagSelect($store_id = 0, $id, $pid = 0){
        $ary = $this->aryStoreCate($store_id, $pid);
		$html = '<select name="pid" id="pid">'.PHP_EOL.'<option value="0">顶级类别</option>'.PHP_EOL;
        if($ary){
            foreach($ary as $rs){
                $sel = $rs['id'] == $id ? ' selected="selected"' : '';
                $html .= '<option value="'.$rs['id'].'"'.$sel.'>'.$rs['cate_name'].'</option>'.PHP_EOL;
            }
        }
		$html .= '</select>'.PHP_EOL;
        return $html;
    }

    // 商品页面的顶级类别
    public function goods2storeCate($ary, $pid, $id, $tag){
        $html = '';
        if($ary){
            $html .= '<select name="'.$tag.'" id="'.$tag.'">'.PHP_EOL.'<option value="">顶级类别</option>'.PHP_EOL;
            foreach($ary as $rs){
                if($rs['pid'] == $pid){
                    $sel = $rs['id'] == $id ? ' selected="selected"' : '';
                    $html .= '<option value="'.$rs['id'].'"'.$sel.'>'.$rs['cate_name'].'</option>'.PHP_EOL;
                }
            }
            $html .= '</select>'.PHP_EOL;
        }
        return $html;
    }

	public function getCache(){
		return $this->where(array('status'=>1))->order('listorder')->select();
	}

}
