<?php

return array(
	 //路由
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
		//'/^(\w+)-(\d+)\/^(\w+)--(\d+)$/'=>'FrontHot/index?cat=:2&ct1=:4',
		'FrontInit' =>'Index/index',
		//'hot' => 'FrontHot/index',//热拍首页
		'yugao' => 'FrontNotice/index',//预告
		'help' => 'FrontHelp/index',//帮助中心
		'zclist' => 'FrontLtopic/index',//专场列表
		'/goods-(\d+)$/' => 'FrontData/index?id=:1',//拍品详情页
		'/so-(.*)$/' => 'FrontSearch/index?keyword=:1',//搜索
		'/^(\w+)~(\d+)-(\d+)$/'=>'FrontHot/index?cat=:2&ct=:3',//属性筛选
		//'/^(\w+)-(\d+)-(\d+)$/'=>'FrontHot/index?cat=:2&ct=:3',//属性筛选
		'/^(\w+)$/'=>'FrontHot/index?param=:1',//分类
		'/^(\w+)-(\d+)-(\d+)$/'=>'FrontHot/index?cat=:2&attrid=:3',//属性 全部 字样
		'/zhuanchang-(\d+)$/' => 'FrontTopic/index?id=:1',//拍品详情页
        '/^(\w+)-(\d+)$/' => 'FrontHot/index?cat=:2',//热拍大分类
		
    ),


	'LOAD_EXT_CONFIG' =>'record',
   
);