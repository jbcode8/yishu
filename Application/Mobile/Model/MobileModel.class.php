<?php

namespace Mobile\Model;

use Think\Model;

/**
 * wap拍卖模型
 */
class MobileModel extends Model{
	//获取图片
	public function getPic($record_id, $type='thumb'){
	    $img_where = array(
			'yr.recordid' => $record_id,
		    'ya.type' => $type
		);
		$img_info = M('record')->alias('yr')->join('yishu_attachment ya on yr.sourceid = ya._id')->where($img_where)->find();
		return $img_info['savepath'] . $img_info['savename'];
	}
}