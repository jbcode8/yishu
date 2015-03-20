<?php
/**
 * 会员中心菜单
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 yishu. All rights reserved.
 */

namespace Malladmin\Model;
use Think\Model;

class MemberMenuMallModel extends Model{
    /**
     * 获取菜单列表
     * @param bool $filed
     * @return mixed
     */
    public function lists($filed=true){
        return $this->field($filed)->where('status=1')->order('listorder asc')->select();
    }

    /**
     * 删除后删除关联菜单
     * @param $data
     * @param $options
     */
    public function _after_delete($data,$options){
        $this->where("pid={$data['id']}")->delete();
    }

    /**
     * 写入前清空缓存
     * @param $data
     */
    protected function _before_write($data){
        S('MemberMenu',null);
    }

    protected function _before_delete($data){
        S('MemberMenu',null);
    }

}