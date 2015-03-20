<?php
	namespace Jianding\Controller;
	use Jianding\Controller\AdminJiandingController;
	class AdminAttributeController extends AdminJiandingController{
		//属性显示页面
		public function aindex(){
			$cat_id = I('get.cat_id','','intval');
			if(empty($cat_id)){
				$this->error('数据错误');
			}

			//取得yishu_JishouGoodsType 里的Gtype_name值
			$cat_name = M('JiandingCategory')
				->field('cat_name')
				->where(array('cat_id'=>$cat_id))
				->getField('cat_name');

			$gtype['cat_name']=$cat_name;
			$gtype['cat_id'] =$cat_id;
			
			$this->assign('gtype',$gtype);
			
			$ja = D('JiandingAttribute');
			$attrs_data = $ja->singleAttribute($cat_id);
			$this->assign('attrs_data',$attrs_data);

			

			$this->display('Admin/attr_index');
		}


		//属性添加页面
		public function attr_add(){
			//获取属性的分类 ,
			$cat_id = I('get.cat_id');
			$this->assign('cat_id',$cat_id);
			$this->display('Admin/attr_add');
		}

		//属性添加插入控制入口
		public function insert(){
			$attrs = I('post.');
			
			
			$attrmodel = D('JiandingAttribute');
		
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


		//属性删除操作
		public function delete(){
			$attr_id = I('get.attr_id');

			$attr_id = intval($attr_id);

			$flag = D('JiandingAttribute')->where(array('attr_id'=>$attr_id))->save(array('is_delete'=>1));

			if($flag){
				$this->success('成功');
			}else{
					$this->error('失败');
				}
		}

		public function update(){
			
			$attr_id = I('get.attr_id');
			//echo 'is ok';
			
			$attrs = M('JiandingAttribute')->field('*')
				->where(array('attr_id'=>$attr_id,'is_delete'=>0))
				->find();
			if(empty($attrs)){
				$this->error('数据不存在');
			}

			$this->assign('attrs',$attrs);

			//print_r($attrs);
			$this->display('Admin/attr_update');
			
		}


		public function save(){
			$attrs = I('post.');


			if($attrs['attr_input_type']==1){
				$attrs['attr_value']='';
			}

			$attr_id = $attrs['attr_id'];

			$attribute =M('JiandingAttribute');
			
			
			if($attribute->create($attrs)){
		
				$flag =$attribute->save($attrs);
				if($flag){
					$this->success('成功');
					
				}else{
					$this->error('失败');
					
				}
			
			}else{
					$this->error($attribute->getError());
			}
		}





	}