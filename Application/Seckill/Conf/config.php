<?php

return array(
    'TMPL_CACHE_ON' =>false,
	 //路由
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
	    '/zhuanchang-(\d+)$/' => 'SpecialList/index?id=:1',//拍品详情页	
        '/goods-(\d+)$/' => 'GoodsShow/index?id=:1',//拍品详情页
    ),
   
);
