<?php
/**
 * 路由配置
 * @author        lu yan hua <838777565@qq.com>
 * @copyright     Copyright (c) 2013-2020 sparui. All rights reserved.
 * @link          http://www.sparui.com
 * @license       http://www.sparui.com/license
 */

return array(
    'URL_ROUTER_ON' => true, //开启路由
    'URL_ROUTE_RULES' => array(//定义路由规则
		'solr' => 'Index/solr',
		'/^(\w+)$/' => 'Filter/index?short_name=:1',
		'/^(\w+)~(\d+)$/' => 'Filter/index?short_name=:1&price=:2',
		'/^goods-(\d+)$/' => 'Goods/index?id=:1',
		'/^store-(\d+)$/' => 'Store/index?id=:1',
		'/^show-(\d+)$/' => 'Article/show?id=:1',
		'/^kw-(\d+)$/' => 'Search/index?kw=:1',
		'/^store-(\d+)-(\d+)$/' => 'Store/lists?id=:1&cid=:2',
		
    ),
);