<?php
// +----------------------------------------------------------------------
// | CategoryModel.class.php
// +----------------------------------------------------------------------
// | Author: luyanhua <838777565@qq.com>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

class CategoryModel extends Model {
    /* 自动验证规则 */
    protected $_validate = array(
        array('name', 'require', '栏目标识不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '栏目标识已存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT),
        array('title', 'require', '栏目名称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '', '栏目名称已存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_INSERT),
    );

    /**
     * 获取栏目列表
     * @return array
     */
    public function getList(){
        $list = $this->field('catid,name,title,pid,model,listorder,items')->select();
        return D('Common/Tree')->toFormatTree($list,'title','catid');
    }

    /**
     * 新增或更新一个栏目
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

        /* 添加或新增基础内容 */
        if(empty($data['catid'])){ //新增数据
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
        return $data;
    }

    /**
     * 获取栏目树结构
     * @return string
     */
    public function getTree(){
        /* 获取所有分类 */
        $list = $this->order('listorder asc,catid asc')->getField('catid as id,name,title,pid,model,type,items,listorder,items');
        $tablelist = array();
        foreach($list as $k=>$v){
            $tablelist[$k] = $v;
            $tablelist[$k]['type'] = getCateType($v['type']);
            $tablelist[$k]['model'] = getModelName($v['model']);
            $tablelist[$k]['items'] = $v['items']==0?'':$v['items'];
            $tablelist[$k]['pid_node'] = ($v['pid']) ? 'data-tt-parent-id="'.$v['pid'].'"': '';
            $tablelist[$k]['manage'] = "<a class='fa fa-plus-square' href=\"javascript:$.dialog.open('".U('Admin/Category/add?catid='.$v['id'])."',{title:'添加子栏目'});\">添加子栏目</a>
                                        <a class='fa fa-edit' href=\"javascript:$.dialog.open('".U('Admin/Category/edit?catid='.$v['id'])."',{title:'修改栏目'});\">修改栏目</a>
                                        <a class='fa fa-trash-o confirm ajax-get' href=\"".U('Admin/Category/delete?catid='.$v['id'])."\">删除</a>";
        }
        //树型结构处理
        $menu = new \Org\Util\Tree();
        $menu->icon = array('┃','┣','┗');
        $menu->nbsp = "&nbsp;&nbsp;&nbsp;";
        $str = "<tr data-tt-id='\$id' \$pid_node>
                       <td style='width:50px;text-align: left;padding-left: 5px'><input type='text' name='listorder[\$id]' value='\$listorder' size='3'/></td>
                       <td>\$id</td>
                       <td>\$name</td>
                       <td style='text-align: left'>\$spacer \$title</td>
                       <td>\$type</td>
                       <td>\$model</td>
                       <td>\$items</td>
                       <td>\$manage</td>
                    </tr>";
        $menu->init($tablelist);
        return $menu->get_tree(0, $str);
    }

} 