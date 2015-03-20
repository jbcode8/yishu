<?php
/**
 * 配置
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 yishu. All rights reserved.
 */

namespace Admin\Api;

class ConfigApi {
    /**
     * 获取数据库中的配置列表
     */
    public static function lists($group = 1){
        if(!$data = S('Config'.$group)){
            $data = M('Config')->where(array('status'=>1,'group'=>$group))->getField('name,value',true);
            if(!empty($data)){
                S('Config'.$group,$data);
            }
        }
        return $data;
    }
} 