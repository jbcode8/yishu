<?php
/**
 * Created by PhpStorm.
 * User: jeff
 * Date: 14-8-22
 */

namespace Home\Controller;
/**
 * 批量转换图片
 */
class ConvertPicsController extends HomeController {

    public function convert(){
		$table = I('get.table');
		$data = D('Document')->convert($table);
		echo "success";
    }
}