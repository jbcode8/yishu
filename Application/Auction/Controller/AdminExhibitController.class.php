<?php

// +----------------------------------------------------------------------
// | 拍卖模块_拍卖产品_[后台管理]_控制器(CURD)
// +----------------------------------------------------------------------
// | Author: RealRain <intval@163.com>
// +----------------------------------------------------------------------
namespace Auction\Controller;
use Admin\Controller\AdminController;

class AdminExhibitController extends AdminController {

    /**
     * 初始化
     */
    public function _initialize() {
        parent::_initialize();
        $this->Model = D('AuctionExhibit');
    }

    /**
     * 列表信息
     */
    public function index() {

        // 分页信息
        //C('PAGE_NUMS', 10); // 重置分页条数
        //$this->page = $this->pages();

        // 分页列表
        $field = array('id', 'name', 'isshow', 'sn', 'price', 'addtime');
        //$this->list = $this->Model->field($field)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();
		//dump($this->list);

        //$field = array('id', 'name', 'mid', 'status', 'createtime', 'updatetime');
        //$this->list = $this->Model->field($field)->limit($this->p->firstRow . ', ' . $this->p->listRows)->order('`id` DESC')->select();

        $p=isset($_GET['p'])?$_GET['p']:1;
        //exit('1');
        //每页显示的数量
        $prePage=10;
        $list=$this->Model->field($field)->page($p.','.$prePage)->order('`id` DESC')->select();
        //p($list);
        $this->assign("list",$list);
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


        $this->display('Admin/exhibit_index');
    }

    /**
     * 添加信息
     */
    public function add() {
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            $data = $this->Model->create();
            if ($data) {
            	//根据作者名称查询大师信息
				/*$artist = M('Artist')->field('id')->where(array('name' => $data['author']))->find();
					if(!$artist) {
						$this->error('该大师信息不存在或已被删除！');
					} else {
						$data['author'] = $artist['id'];
					}
				*/
            	//根据拍卖会ID，获取其机构ID,一级拍卖城市ID
            	$agencyid = M('AuctionMeeting')->field('agencyid, areaid')->where(array('id' => $data['meetingid']))->find();
				$data['agencyid'] = $agencyid['agencyid'];
				$data['areaid'] = $agencyid['areaid'];
              	$data['pics'] = array_images($_POST['pics']); // 组图上传需要处理
              	
                $boolean = $this->Model->add($data);
                if ($boolean !== false) {
                    $this->success('信息添加成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
                } else {
                    $this->error('信息添加失败！');
                }
            } else {
                $this->error($this->Model->getError());
            }
        } else {
        	//获取所有拍卖会
        	//$this->Model = M('AuctionMeeting');
			$this->meeting_list = M('AuctionMeeting')->where('pid = 0')->field('id, name')->order('id desc')->select();
        	
            $this->display('Admin/exhibit_add');
        }
    }

    /**
     * 编辑信息
     */
    public function edit() {
        if (isset($_POST['submit']) && !empty($_POST['submit'])) {
            $data = $this->Model->create();
            if ($data) {
                // 读取信息 并 检测信息是否存在
                $list = $this->Model->where(array('id' => $data['id']))->find();
                if ($list) {
                	//根据作者名称查询大师信息
					/*$artist = M('Artist')->field('id')->where(array('name' => $data['author']))->find();
					if(!$artist) {
						$this->error('该大师信息不存在或已被删除！');
					} else {
						$data['author'] = $artist['id'];
					}*/
                	//根据拍卖会ID，获取其机构ID,以及拍卖城市ID
	            	$agencyid = M('AuctionMeeting')->field('agencyid, areaid')->where(array('id' => $data['meetingid']))->find();
					$data['agencyid'] = $agencyid['agencyid'];
					$data['areaid'] = $agencyid['areaid'];
					
                    $data['pics'] = array_images($_POST['pics']); // 组图上传需要处理
                    $boolean = $this->Model->save($data);
                    if ($boolean !== false) {
                        $this->success('信息编辑成功！');
                    } else {
                        $this->error('信息编辑失败！');
                    }
                } else {
                    $this->error('此信息不存在或已经删除！');
                }
            } else {
                $this->error($this->Model->getError());
            }
        } else {
            // 读取信息 并 检测信息是否存在
            $data_res = $this->Model->find(I('get.id', 0, 'int'));
			$this->data = $data_res;
			 
			//获取该拍卖品所属拍卖会和专场的名称
		    $this->meeting_name = M('AuctionMeeting')->where(array('id' => $data_res['meetingid']))->find();
			$this->special_name = M('AuctionMeeting')->where(array('id' => $data_res['specialid']))->find();
			
			//获取所有的拍卖会信息
			$this->meeting_list = M('AuctionMeeting')->where('pid = 0')->field('id, name')->order('id desc')->select();
            if ($this->data) {
                $this->display('Admin/exhibit_edit');
            } else {
                $this->error('此信息不存在或已经删除！');
            }
        }
    }

    /**
     * 更新状态信息
     */
    public function update() {
        // 读取信息并检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        empty($data) AND $this->error('此信息不存在或者已被删除！');
        //更新操作
        $boolean = $this->Model->where(array('id' => $data['id']))->save(array('isshow' => I('get.isshow', 0, 'int')));
        if ($boolean !== false) {
            $this->success('设置成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
        } else {
            $this->error('设置失败！');
        }
    }

    /**
     * 删除信息
     */
    public function delete() {
        // 读取信息并检测信息是否存在
        $data = $this->Model->find(I('get.id', 0, 'int'));
        if ($data) {
            $boolean = $this->Model->delete($data['id']);
            if ($boolean !== false) {
                $this->success('信息删除成功！', U(GROUP_NAME . '/' . MODULE_NAME . '/index'));
            } else {
                $this->error('信息删除失败！');
            }
        } else {
            $this->error('此信息不存在或已经删除！');
        }
    }
	
	public function ajax() {
		if(I('get.meetingid', '', 'int') > 0){
			$result = M('AuctionMeeting')->field('id, name')->where(array('pid' => I('get.meetingid', '', 'int')))->select();
			echo json_encode($result);	
		} 
	}
}

?>
