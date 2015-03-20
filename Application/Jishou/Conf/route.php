<?php
	return array(
		'URL_ROUTER_ON'   => true,
		'URL_ROUTE_RULES' =>array(
		'/^(\d+)$/' => 'Goods/show?goodsid=:1',//商品列表页
		'/^([a-zA-Z]+)$/' => 'Category/show?catspell=:1',//商品列表页
		),
	);