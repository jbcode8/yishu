<?php
return array(
    //б╥си
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(  
        //'/^question-(\d+)$/' => 'Index/detail?qid=:1',
        '/^(\w+)-(\d+)$/' => 'Index/collectionart?shortname=:1&cid=:2',
		'/^(\w+)-(\d+)-(\d+)$/' => 'Index/collectionart?shortname=:1&cid=:2&p=:3',
		'/^collect-(\d+)-(\d+)$/' => 'Index/collectionart?cid=:1&l=:2',
		'hot' => 'Index/hotentry',
		':did\d' => 'Index/terminal',
        'search' => 'Index/search',
		'edit' => 'Index/edit',
		'tag' => 'Index/tag',

 
    ),
);