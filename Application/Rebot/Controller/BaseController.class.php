<?php 
namespace Rebot\Controller;
use Think\Controller;

/**
 * 定期处理任务控制器
 * For Example: http://www.yishu.com/Rebot/Base/RegularlyCleaned
 */
class BaseController extends Controller
{
	/**
	 * 定期清理仿session表 bsm_session
	 */
    public function RegularlyCleaned()
    {
        $time = strtotime('-1 week');   //删除一周前的
        return M('bsm.session', 'bsm_')->where('regtime <= '.$time)->delete();
    }
}