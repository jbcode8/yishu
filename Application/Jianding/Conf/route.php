<?php
	return array(
		'URL_ROUTER_ON'   => true,
		'URL_ROUTE_RULES' =>array(
		'/^([\d-]+)$/' => 'JiandingGoods/gs_detail?goodsid=:1',//商品列表页
		'/^([a-zA-Z]+)$/' => 'JiandingCategory/cindex?catspell=:1',//商品列表页
		),
	);