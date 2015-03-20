<?php
// +----------------------------------------------------------------------
// | 艺术百科分类模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Model;

class CategoryModel extends BaikeModel {

    protected $_validate = array(
        array('name', 'require', '分类名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '分类名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_INSERT),
        array('description', 'require', '没有填写分类的描述', self::EXISTS_VALIDATE, 'regex' , self::MODEL_INSERT),
        array('listorder', 'require', '没有输入分类的排序数字', self::EXISTS_VALIDATE, 'regex' , self::MODEL_INSERT),
        array('listorder', '0,100', '分类排序只能为 0 至 100 的数字', self::VALUE_VALIDATE, 'between' , self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('docs', 0, self::MODEL_INSERT, 'string'),
    );


    /**
     * 发挥所有分类的数据库查询结果.
     * @return mixed
     */
    public function category(){
        return $this->order("listorder ASC")->select();
        //TODO 没有缓存
    }


    /**
     * 返回所有分类的一维数组
     * @param  string $html
     * @return array
     */
    public function catetable($html="—"){
        return category_table($this->category(),$html);
        //TODO 没有缓存
    }

    /**
     * 返回所有分类的多维数组（树形结构）
     * @return array
     */
    public function catetree(){
        return category_tree($this->category());
        //TODO 没有缓存
    }

    /**
     * 返回顶级分类
     * @return array
     */
    public function supercate(){
        return $this->field('cid,pid,name,listorder,short_name')->where('pid=0')->order('cid ASC')->select();
    }

    /**
     * 返回一级分类
     * @return array
     */
    public function cateone(){
        return $this->query("select a.cid,a.pid,a.name,a.short_name,a.listorder from ".$this->tablePrefix."category a join ".$this->tablePrefix."category b on a.pid=b.cid where b.pid=0 order by cid asc");
    }

    /**
     * 通过分类ID标识获取分类名称
     * @param $cid
     * @return string
     */
    public function get_name($cid){
       return  $this->getFieldByCid($cid,'name');
    }

    /**
     * 刷新分类词条数量,此方法只用于刷新单一分类
     * @param  int $operate  1 加操作，0 减操作
     * @param  int $num=1    操作的数量缺省值 1
     */
    public function refreshDocs($operate, $num=1){
        $cid = I('cid', 0, 'intval');
        if(is_int($cid) && $cid>0 && is_int($num)){
            $cids = category_parents($this->category(), $cid, true);
            $exp = $operate==1 ? "`docs`+$num" : "`docs`-$num";
            $data['docs'] = array('exp', $exp);
            $map['cid'] = array('IN', $cids);
            $this->where($map)->save($data);
        }
    }

    /**
     * 整体刷新所有分类的词条数量,此方法用于批量删除词条后刷新词条数量
     */
    public function refreshAllDocs(){
        $sql = "SELECT cid,COUNT(did) as num FROM ".$this->tablePrefix."doc GROUP BY cid ORDER BY cid";
        $numArr = $this->query($sql);
        if(is_array($numArr) && $numArr){
            foreach($numArr as $num){
                $this->where(array('cid'=>$num['cid']))->setField('docs',$num['num']);
            }
            $cates = $this->category();
            foreach($cates as $cate){
                $childs =  category_childids($cates, $cate['cid']);
                if($childs){
                    $map['cid'] = array('IN', $childs);
                    $total = $this->where($map)->sum('docs');
                    $this->where(array('cid'=>$cate['cid']))->setField('docs',$total);
                }
            }
        }
    }




}