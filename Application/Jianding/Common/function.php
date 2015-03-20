<?php 
/**
 * 距离现在的时间
 * @param string $time
 */
function toNow($time)
{
    $date1 = date_create(date('Y-m-d H:i:s', $time));
    $date2 = date_create(date('Y-m-d H:i:s', time()));
    
    $interval = date_diff($date1, $date2);
    $d = $interval->format('%D');
    $h = $interval->format('%H');
    $m = $interval->format('%I');
    $s = $interval->format('%S');
    $l = strlen((int)trim($d.$h.$m.$s));
    $d = (int)$d;$h = (int)$h;
    $m = (int)$m;$s = (int)$s;
    if (in_array($l, array(1, 2)))
    {
    	$diff = $s.'秒前';
    }else if (in_array($l, array(3, 4)))
    {
    	$diff = $m.'分钟前'; 
    }else if (in_array($l, array(5, 6)))
    {
        $diff = $h.'小时前';
    }else if (in_array($l, array(7, 8)))
    {
    	if ($d > 7)
    	{
    	    $diff = '久远之前';
    	}else 
    	{
    		$diff = $d.'天前';
    	}
    }
    return $diff;
}