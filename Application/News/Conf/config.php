<?php

return array(
	 //路由
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(

        '/^(\d+)\/(\d+)$/'=>'Details/index?param=:2',//新闻详情页把www.yishu.com/news/20141205/123.html ->/News/Details/index?
        '/^(\w+)$/'=>'List/index?param=:1',//新闻列表把www.yishu.com/news/renwen/ ->/News/List/index
		
    ),
	'LOAD_EXT_CONFIG' =>'record',
   
);