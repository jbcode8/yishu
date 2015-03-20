<?php
// +----------------------------------------------------------------------
// | 问答模块配置文件
// +----------------------------------------------------------------------
// | Author: songfeilont <414545427@qq.com>
// +----------------------------------------------------------------------

return array(
    'DB_PREFIX' => 'yishu_ask_', // 数据库表前缀
    //路由
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(  
        //'/^question-(\d+)$/' => 'Index/detail?qid=:1',
        'question/:qid\d' => 'Index/detail',
        'cate' => 'Index/category',
		'/^(\w+)-(\d+)$/' => 'Index/category?shortname=:1&cid=:2',
        //'cate/:cid\d' => 'Index/category',
        'star' => 'Index/star',
        'categorys' => 'Index/category_detail',
        'index/:status' => 'Index/index',
        'solved' => 'Index/solved_list',
        'ask' => 'Index/ask',
        'besolved' => 'Index/besolved_list',
        'clear' => 'Index/clear',
        'add_comment' => 'Index/add_comment',
        'solve_ajax' => 'Index/solve_ajax',
		'add_count' => 'Index/add_count',
		'cn/:rid\d/:qid\d' => 'Index/cn',
    ),
);