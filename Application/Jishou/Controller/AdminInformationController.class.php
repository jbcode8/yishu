<?php
	/**
	* 链接添加的入口文件
	*/

	namespace Jishou\Controller ;
	use Admin\Controller\AdminController;

	class AdminInformationController extends AdminController{

		//添加资讯入口文件
		public function infIndex(){
			$this->assign('info_data',array());
			$data_info = M('JishouNews')->order('add_time desc')->select();
			$this->assign('info_data',$data_info);
			$this->display('Admin:info_index');
		}


		//添加子资讯的界面的入口
		public function info_add(){
			$this->display('Admin/info_add');
		}


		public function insert(){

			$info  =D('JishouNews');
			if($info->create()){
				$info->add();
				$this->success('添加成功');
			}else{
				$this->error($info->getError());
			}

		}

		//删除资讯链接
		public function del($info_id){
			$flag = M('JishouNews')->where(array('info_id'=>$info_id))->limit(1)->delete();
			if($flag){
				$this->success('删除成功');
			}else{
				$this->success('删除失败');
			}
		}
	}


?>