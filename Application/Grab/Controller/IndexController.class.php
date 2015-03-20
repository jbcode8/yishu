<?php

namespace Grab\Controller;

class IndexController{
	public function index(){
		header("Content-type:text/html ; charset=utf-8");
		$_POST['submit'] = 1;
		$_POST['url'] = 'http://shop.artron.net/works/6319_w184750.html';
		if (!empty($_POST['submit'])){
		$url = $_POST['url'];
		//为了获取相对路径的图片所做的操作
		$url_fields = parse_url($url);
		$main_url = $url_fields['host'];
		$base_url = substr($url,0,strrpos($url, '/')+1);
		//获取网页内容
		//设置代理服务器
		$opts = array('http'=>array('request_fulluri'=>true));
		$context = stream_context_create($opts);
		$content = file_get_contents($url,false,$context);
		//匹配img标签,将所有匹配字符串保存到数组$matches
		$reg = "/<img.*?src=\"(.*?)\".*?>/i";
		preg_match_all($reg, $content, $matches);
		$count = count($matches[0]);
		for ($i=0; $i<$count; $i++){
		/*将所有图片的url转换为小写
		*$matches[1][$i] = strtolower($matches[1][$i]);
		*/
		//如果图片为相对路径就转化为全路径
		if (!strpos('a'.$matches[1][$i], 'http')){
		//因为'/'是第0个位置
		if (strpos('a'.$matches[1][$i], '/')){
		$matches[1][$i] = 'http://'.$main_url.$matches[1][$i];
		}else{
		$matches[1][$i] = $base_url.$matches[1][$i];
		}
		}
		}
		//过滤重复的图片
		$img_arr = array_unique($matches[1]);
		//实例化图片下载类
		$getImg = new DownImage();
		$url_count = count($img_arr);
		for ($i=0; $i<$url_count; $i++){
		$getImg->source = $img_arr[$i];
		$getImg->save_address = './pic/';
		$file = $getImg->download();
		}
		echo "下载完成！哈哈，简单吧！";
		}
		

	}

}

class DownImage{
	public $source;//远程图片URL
	public $save_address;//保存本地地址
	public $set_extension; //设置图片扩展名
	public $quality; //图片的质量（0~100,100最佳，默认75左右）
	//下载方法（选用GD库图片下载）
	public function download(){
		//获取远程图片信息
		$info = @getimagesize($this->source);
		//获取图片扩展名
		$mime = $info['mime'];
		$type = substr(strrchr($mime, '/'), 1);
		//不同的图片类型选择不同的图片生成和保存函数
		switch($type){
		case 'jpeg':
		$img_create_func = 'imagecreatefromjpeg';
		$img_save_func = 'imagejpeg';
		$new_img_ext = 'jpg';
		$image_quality = isset($this->quality) ? $this->quality : 100;
		break;
		case 'png':
		$img_create_func = 'imagecreatefrompng';
		$img_save_func = 'imagepng';
		$new_img_ext = 'png';
		break;
		case 'bmp':
		$img_create_func = 'imagecreatefrombmp';
		$img_save_func = 'imagebmp';
		$new_img_ext = 'bmp';
		break;
		case 'gif':
		$img_create_func = 'imagecreatefromgif';
		$img_save_func = 'imagegif';
		$new_img_ext = 'gif';
		break;
		case 'vnd.wap.wbmp':
		$img_create_func = 'imagecreatefromwbmp';
		$img_save_func = 'imagewbmp';
		$new_img_ext = 'bmp';
		break;
		case 'xbm':
		$img_create_func = 'imagecreatefromxbm';
		$img_save_func = 'imagexbm';
		$new_img_ext = 'xbm';
		break;
		default:
		$img_create_func = 'imagecreatefromjpeg';
		$img_save_func = 'imagejpeg';
		$new_img_ext = 'jpg';
		}
		//根据是否设置扩展名来合成本地文件名
		if (isset($this->set_extension)){
		$ext = strrchr($this->source,".");
		$strlen = strlen($ext);
		$newname = basename(substr($this->source,0,-$strlen)).'.'.$new_img_ext;
		}else{
		$newname = basename($this->source);
		}

		//生成本地文件路径
		$save_address = $this->save_address.$newname;
		$img = @$img_create_func($this->source);
		if (isset($image_quality)){
		$save_img = @$img_save_func($img,$save_address,$image_quality);
		}else{
		$save_img = @$img_save_func($img,$save_address);
		}
		return $save_img;
	}
}