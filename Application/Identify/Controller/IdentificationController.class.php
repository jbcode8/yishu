<?php

/**
 * API接口
 * @copyright			(C) 2013-2015
 * @author                     Ethan Lu <838777565@qq.com>
 * @createdate                2013-6-14 0:23:19
 */
namespace Identify\Controller;
use Think\Image;
use Think\Controller;
class IdentificationController extends Controller{
    public function getcode(){
        $width = $_GET['width']?$_GET['width']:80;
        $height = $_GET['height']?$_GET['height']:35;
        $fontsize = $_GET['size']?$_GET['size']:14;
        $mode = $_GET['mode']?$_GET['mode']:2;
        Image::buildVerify(4, $fontsize, $width, $height, "png", $mode,'verify');
    }
}
