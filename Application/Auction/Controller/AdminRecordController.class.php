<?php

// +----------------------------------------------------------------------
// | 拍卖模块_出价纪录_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Admin\Controller\AdminController;

class AdminRecordController extends AdminController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionPriceRecord');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
        //C('PAGE_NUMS', 12); // 重置分页条数
       // $this->page = $this->pages();

        // 分页列表
        //$arrRecord = $this->Model->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();

        // 循环获取用户的昵称



        $p=isset($_GET['p'])?$_GET['p']:1;
        //每页显示的数量
        $prePage=10;
        $arrRecord=$this->Model->page($p.','.$prePage)->order('`id` DESC')->select();
        //P($arrRecord);
        if(is_array($arrRecord)){
            $newArr = array();
            foreach($arrRecord as $k => $v){
                $newArr[$k] = $v;
                //
                $newArr[$k]['nickname'] = getNickName($v['mid']);
//                /exit($newArr[$k]['nickname']);
                $newArr[$k]['title'] = getAuctionName($v['aid']);
            }
            //$this->list = $newArr;

            $this->assign("list",$newArr);
        }
        //p($list);
        //$this->assign("list",$list);
        $count= $this->Model->count();// 查询满足要求的总记录数
        $Page= new \Think\Page($count,$prePage);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('header','共%TOTAL_ROW%条记录');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last', '尾页');

        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show       = $Page->show();// 分页显示输出
        //p($show);
        $this->assign('page',$show);// 赋值分页输出
        $this->display('Admin/record_index');
    }

	/**
	 * 批量操作(删除|设置状态)
	 */
    public function batch(){

		if(IS_POST){

			$act = I('post.act','','trim');
			empty($act) AND $this->error('请求错误');

			$ids = I('post.ids','','trim');
			empty($ids) AND $this->error('至少选择一条数据');

			$where['id'] = array('in', $ids);
			
			// 删除操作
			if($act == 'remove'){
								
				$bool = $this->Model->where($where)->delete();
				if($bool){
					$this->success('数据删除成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
				}else{
					$this->error('数据删除失败！');
				}

			}
		}
	}

}

?>
