<?php

// +----------------------------------------------------------------------
// | 用户资料
// +----------------------------------------------------------------------
// | Author: Ethan <838777565@qq.com>
// +----------------------------------------------------------------------
namespace Paimai\Controller;
use Think\Controller;
//use Paimai\Controller\PaimaiPublicController;

//class TestController extends PaimaiPublicController {
class TestController extends Controller {
    public function _initialize()
    {
        /*//11为陶器,3钱币,$ee为这个分类下面的最底层分类的字符串，如137,138,139,140,141,142,143
        $ceshi=new \Mall\Model\CategoryModel();
        $ee=$ceshi->getSubCategoryId(3);*/
    }
	
    public function index(){
        echo date("Y-m-d H:i:s")."<br/>";
        echo time();
    /*$arr=M("PaimaiOrderInfo")->limit(2)->select();
    p($arr);*/
       exit;	
    }
}
