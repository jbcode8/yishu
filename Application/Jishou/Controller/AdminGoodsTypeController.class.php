<?php
	namespace Jishou\Controller;
	use Admin\Controller\AdminController;
	class AdminGoodsTypeController extends AdminController{

		//商品分类的显示界面
		public function index(){
			
			$goodsType = M('JishouGoodsType')
				->field('gtype_id,gtype_name,gtype_enabled')
				->select();
			$this->assign('goodstype_data',$goodsType);

			$this->display('Admin/type_index');
			
		
		}
		

		//商品分类添加的显示界面
		public function add(){
			$this->display('Admin/type_add');
		}


		//商品分类添加的属性
		public function insert(){
			//echo 'is ok';
			$typeData = $_POST;
			$goodsType= D('JishouGoodsType');
			if($goodsType->create($typeData)){
				$goodsType->add();
				$this->success('添加成功');
			}else{
				$this->error($goodsType->getError());
			}
		
		}

		public function update(){
			$gtype_id = I('get.gtype_id');
			if(empty($gtype_id)){
				$this->error('数据匹配错误');
			}

			$gtype_goods=D('JishouGoodsType')
				->field('gtype_id,gtype_name,gtype_enabled')
				->where(array('gtype_id'=>$gtype_id))->limit(0)->select();
			$this->assign('gtype_goods',$gtype_goods[0]);
			$this->display('Admin/type_update');
		}
		
		//保存修改的商品属性类型的值
		public function save(){
			$update_type = I('post.');
			$gtype_id = $update_type['gtype_id'];
			if(empty($gtype_id)){
				$this->error('数据传输错误');
			}
			
			unset($update_type['gtype_id']);
			
			$gtype = D('JishouGoodsType');
			
			$_validate=array(
				array('gtype_enabled','0,1','是否启用选项错误',0,'in',3),
				array('gtype_name','require','名称不能为空'),
				//array('gtype_name','','名字重复',2,'unique',1),
		);

			
			if($gtype->validate($_validate)->create($update_type)){
				$flag=$gtype->where(array('gtype_id'=>$gtype_id))->save();
				$this->success('修改成功');
			}else{
				$this->error($gtype->getError());
			}
		}


		//删除商品属性类型
		public function delete(){
			$gtype_id = I('get.gtype_id');
			$gtype_id = intval($gtype_id);

			$flag = M('JishouGoodsType')
				->where(array('gtype_id'=>$gtype_id))
				->delete();

			if($flag){
				M('JishouAttribute')->where(array('gtype_id'=>$gtype_id))->delete();
				$this->success("删除成功");
			}else{
				$this->error('删除失败');
			}
		
		}
	} 

?>