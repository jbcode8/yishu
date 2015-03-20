<?php
	namespace Jishou\Controller;
	use Admin\Controller\AdminController;

	class AdminAttributeController extends AdminController{
		public function index(){
			$gtype_id = I('get.gtype_id');
			if(empty($gtype_id)){
				$this->error('数据错误');
			}
			$gtype_id = intval($gtype_id);

			//取得yishu_JishouGoodsType 里的Gtype_name值
			$gtype_name = M('JishouGoodsType')
				->field('gtype_name')
				->limit(1)->where(array('gtype_id'=>$gtype_id))
				->select();
			$gtype_name = $gtype_name[0]['gtype_name'];

			$gtype['gtype_name']=$gtype_name;
			$gtype['gtype_id'] =$gtype_id;
			
			$this->assign('gtype',$gtype);
			
			$ja = D('JishouAttribute');
			$attrs_data = $ja->singleAttribute($gtype_id);
			$this->assign('attrs_data',$attrs_data);

			

			$this->display('Admin/attr_index');
		}

		//属性值页面

		public function add(){
			$gtype_id = I('get.gtype_id');
			$this->assign('gtype_id',$gtype_id);
			$this->display('Admin/attr_add');
		}

		//添加属性值页面
		public function insert(){
			$attrs = I('post.');
			
			
			$attrmodel = D('JishouAttribute');
		
			if($attrmodel->create($attrs)){
				$flag=$attrmodel->add();
				if($flag){
					$this->success('添加属性值成功');
				}else{
					$this->success('添加失败');
				}

				
			}else{
				$this->error($attrmodel->getError());
			}	
		}

		//编辑属性值页面
		public function update(){
			
			$attr_id = I('get.attr_id');
			//echo 'is ok';
			
			$attrs = M('JishouAttribute')->field('*')
				->where(array('attr_id'=>$attr_id))
				->limit(1)->select();
			if(empty($attrs)){
				$this->error('数据不存在');
			}

			$this->assign('attrs',$attrs[0]);

			//print_r($attrs);
			$this->display('Admin/attr_update');
			
		}


		public function save(){
			$attrs = I('post.');


			if($attrs['attr_input_type']==1){
				$attrs['attr_value']='';
			}

			$attr_id = $attrs['attr_id'];
			unset($attrs['attr_id']);

			$attribute =M('JishouAttribute');
			
			
			if($attribute->create($attrs)){
		
				$flag =$attribute->where(array('attr_id'=>$attr_id))->save();
				if($flag){
					$this->success('成功');
					
				}else{
					$this->error('失败');
					
				}
			
			}else{
					$this->error($attribute->getError());
			}
		}

		//属性删除
		public function delete(){
			$attr_id = I('get.attr_id');

			$attr_id = intval($attr_id);

			$flag = D('JishouAttribute')->where(array('attr_id'=>$attr_id))->delete();

			if($flag){
				$this->success('成功');
			}else{
					$this->error('失败');
				}

		
		}
	
	}

?>