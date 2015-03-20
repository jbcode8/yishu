<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Content\Model;
use Think\Model;

/**
 * 分类模型
 */
class CategoryModel extends Model{
	/**
	 * 获取分类详细信息
	 * @param  integer   $id 分类ID或标识
	 * @param  boolean $field 查询字段
	 * @return array     分类信息
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function info($id, $field = true){
		/* 获取分类信息 */
		$map = array();
		if(is_numeric($id)){ //通过ID查询
			$map['catid'] = $id;
		} else { //通过标识查询
			$map['name'] = $id;
		}
		return $this->field($field)->where($map)->find();
	}

    /**
     * 查询栏目信息
     * @param  integer $pid 父栏目id
     * @return array
     */
    public function getNav($pid)
    {
        return $this->field('title,name')->where(array('pid'=>$pid))->select();
    }

    /**
     * 获取子栏目id
     * @param  integer $catid 父栏目id
     * @return array
     */
    public function getChildrenId($catid)
    {
        $ids = $this->where(array('pid'=>$catid))->getField('catid', true);
        if($ids) {
            return $ids;
        } else {
            return $catid;
        }
    }

    /**
     * 获取当前位置
     * @param  integer $catid 栏目id
     * @return array
     */
    public function getPos($catid)
    {
        $pos = $this->field('pid,name,title,catid')->find($catid);
        if($pos['pid']) {
            $this->getPos($pos['pid']);
        }
        echo "<span><a href='/Home/Content/lists/category/".$pos['name']."'>".$pos['title']."</a></span> > ";
    }
}
