<?php
	namespace Jianding\Controller;
	use Jianding\Controller\AdminJiandingController;
	
	class AdminExpertController extends AdminJiandingController
	{
	    protected $cat;
	    protected $expert;
	    
	    public function _initialize()
	    {
	    	$this->cat = D('JiandingCategory');
	    	$this->expert = D('JiandingExpert');
	    }
	    
		public function expIndex()
		{
		    $expert = $this->expert;
		    $cat = $this->cat;
		    
		    $experts_data = $expert->expertsData();
		    foreach ($experts_data as $k => $v)
		    {
		    	foreach ($v as $key => $val)
		    	{
		    		if ($key == 'cat_id')
		    		{
		    			$data = $cat->categoryData($val);
		    			$experts_data[$k]['cat_name'] = $data['cat_name'];
		    		}
		    	}
		    }
		    
		    $array = array(
		    	'experts_data' => $experts_data
		    );
		    $this->assign($array);
			$this->display('Expert/exp_index');
		}
		
		/**
		 * 添加专家
		 */
		public function expAdd()
		{
		    $cat = $this->cat;
		    
		    $cat_lists = $cat->field('cat_id,cat_name')
		                  ->where(array('is_delete'=>0))->select();
		    
		    $this->assign('cat_lists',$cat_lists);
		    
			$this->display('Expert/exp_add');
		}
		
		/**
		 * 专家数据写入
		 */
		public function expInsert()
		{
			$post = I('post.');
			
			$file = $this->upload($_FILES['portrait_img']);
			$_POST['portrait_img_name'] = $file[1];
			$_POST['portrait_img_path'] = $file[0];
			
			$expert = $this->expert;
			$_POST['add_time'] = $_SERVER['REQUEST_TIME'];
			
			if ($expert->create($_POST))
			{
				$flag = $expert->add();
				if ($flag)
				{
				    $this->success('添加专家成功！');
				}else 
				{
					$this->error('失败');
				}
				
			}else 
			{
				$this->error($expert->getError());
			}
		}
		/**
		 * 图片上传
		 */
		public function upload($image)
		{
		    $type = explode('/', $image['type']);
		    $exts = array_pop($type);
		    
		    $des = '/Public/img/Jianding/Expert/';
		    $destination = dirname($_SERVER['SCRIPT_FILENAME']).$des;
		    
		    $name = time().mt_rand().'.'.$exts;
		    $fileName = $destination.$name;
		    
		    if (!is_uploaded_file($image['tmp_name']))
		    {
		        return false;
		    }
		    
		    if (move_uploaded_file($image['tmp_name'], $fileName))
		    {
		        return array($des, $name);
		    }else 
		    {
		    	return false;
		    }
		}
		
		
		/**
		 * 修改专家信息
		 */
		public function expUpdate()
		{
		    $expert = $this->expert;
		    $cat = $this->cat;
		    
		    $exp_id = I('get.expert_id');
		    $exp_id = intval($exp_id);
		    
		    $expert_data = $expert->where(array('expert_id' => $exp_id))
		                        ->find();
		    
		    $cat_lists = $cat->field('cat_id,cat_name')
		              ->where(array('is_delete'=>0))->select();
		    
		    $array  = array(
		        'exp' => $expert_data, 
		        'cat_lists' => $cat_lists
		    );
		    $this->assign($array);
		    
			$this->display('Expert/exp_update');
		}
		/**
		 * 专家修改信息写入
		 */
		public function exp_save()
		{
			$post = I('post.');
			
			$exp_id = $post['expert_id'];
			unset($_POST['expert_id']);unset($post['expert_id']);
			
			if (!empty($_FILES['portrait_img']['tmp_name']))
			{
			    $file = $this->upload($_FILES['portrait_img']);
			    $_POST['portrait_img_name'] = $file[1];
			    $_POST['portrait_img_path'] = $file[0];
			}
				
			$expert = $this->expert;
			$_POST['add_time'] = $_SERVER['REQUEST_TIME'];
			
			
			
			if ($expert->create($_POST))
			{
			    $flag = $expert->where(array('expert_id' => $exp_id))->save($_POST);
			    if ($flag)
			    {
			    	$this->success('成功');
			    }else 
			    {
			    	$this->error('失败');
			    }
			    
			}else
			{
			    $this->error($expert->getError());
			}
		}
		
		/**
		 * 删除专家信息
		 */
		public function expDelete()
		{
		    $expert = $this->expert;
		    
			$exp_id = I('get.expert_id');
			$exp_id = intval($exp_id);
			
			$flag = $expert->where(array('expert_id' => $exp_id))
			         ->save(array('is_delete' => 1));
			
			if ($flag)
			{
				$this->success('成功');
			}else 
			{
				$this->error('失败');
			}
		}
	}