<?php
	namespace Jianding\Controller;
	use Jianding\Controller\AdminJiandingController;
	class AdminCategoryController extends AdminJiandingController{

		//鉴定目录的首页
		public function cindex(){
			$cat_data = M('JiandingCategory')->field('cat_id,cat_name,is_filter,is_enabled,cat_spell')
				->where(array('is_delete'=>0))
				->order('sort_order desc,cat_id asc')
				->select();
			$this->assign('cat_data',$cat_data);
			$this->display('Admin/cat_cindex');
		}
		//鉴定目录添加的页面

		public function add(){
			$cat_lists = M('JiandingCategory')->field('cat_id,cat_name')
						->where(array('is_delete'=>0))->select();

			$this->assign('cat_lists',$cat_lists);

			$this->display('Admin/cat_add');
		}

		//目录数据插入的接口
		public function insert(){
			$cat = I('post.');

			
			$attr_id = $cat['attr_id'];

			//$attr_id =array_unique($attr_id);

			/*foreach($attr_id as $k=>$v){
				if($v == 0 ){
					unset($attr_id[$k]);
				}
			}
		
			$cat['attr_id'] = implode(',',$attr_id);
			*/

			$cat['parent_id']= $cat['cat_pid'];
			unset($cat['cat_pid']);
			

			$jishou_cat = D('JiandingCategory');
			$_POST['add_time']=time();

			

			if($jishou_cat->create($cat)){
				$flag=$jishou_cat->add();
				
				$this->success('添加分类成功！');
			}else{
				$this->error($jishou_cat->getError());
			}

		}


		//目录分类编辑的返回的接口
		public function update(){
			$cat_id = intval(I('get.cat_id'));
			if(empty($cat_id)){
				$this->error('数据错误');
			}
			

			//提取的goods_type信息
			//$goods_type = M('JishouGoodsType')->field('gtype_id,gtype_name')
			//	->where(array('gtype_enabled'=>1))->select();

			//$this->assign('goods_type',$goods_type);

			//提取的要编辑的目录信息
			$catData = D('JiandingCategory');
			if(!($cat_data=$catData->categoryData($cat_id))){
				$this->error('没有数据返回');
			}
			$this->assign('cat_data',$cat_data);

			//目录的全部数据
			$cat_lists = M('JiandingCategory')->field('cat_id,cat_name')
						->where(array('is_delete'=>0))->select();
			$this->assign('cat_lists',$cat_lists);


			$this->display('Admin/cat_update');

		}

		//目录分类后保存数据的接口
		public function save(){
			$cat = I('post.');

			
			

			$cat['parent_id']= $cat['cat_pid'];
			$cat_id = $cat['cat_id'];

			$jishou_cat = D('JiandingCategory');

			

			if($jishou_cat->create($cat)){
				//$flag=$jishou_cat->where(array('cat_id'=>$cat_id))->save();
				$flag=$jishou_cat->save($cat);
				
				//echo '修改分类成功'.dump($flag);
				$this->success('修改分类成功！');
			}else{
				$this->error($jishou_cat->getError());
			}
		
		}

		public function delete(){
			$cat_id=I('get.cat_id');
			if(empty($cat_id)){
				$this->error('数据错误');
			}
			$cat_id = intval($cat_id);

			$flag = M('JiandingCategory')->where(array('cat_id'=>$cat_id))->delete();
			if($flag){
				$this->success('成功删除');
			}else{
				$this->error('删除失败');
			}
		}
		





	}