<?php
	namespace Jishou\Controller;
	use Admin\Controller\AdminController;

	class AdminAdsController extends AdminController{

        //广告位添加首页入口
        public function aindex(){

         $ads_data = M('Ads')
              ->where(array('enabled'=>1))
              ->order('sort_order desc')
              ->order('add_time desc')
              ->select();
          $this->assign('ads_data',$ads_data);  
          $this->display('Ads/aindex'); 
        
        }
		//广告位添加入口
        /*
         * @param  $ads_url  商品的链接地址
         * */
		public function add(){
            $ads_url = I('get.ads_url');
            $this->assign('ads_url',$ads_url);
            $this->display('Ads/ads_add');
		}


        public function insert(){
            $up_data = I('post.');
            
            $dir_name = dirname($_SERVER['SCRIPT_FILENAME']);
            //.DIRECTORY_SEPARATOR.
            $web_path = '/Public/upload/Ads/'.date('ym').'/';
            $file_save_path = $dir_name.$web_path;
            if($_FILES['ads_img']['error']!=0){
                  $this->error('图片上传出现错误');
              }
            
            //获取图片的后缀
            //$file_ext = substr(strrchr($_FILES['ads_img']['name'],'.'),1); 
            $file_ext = strrchr($_FILES['ads_img']['name'],'.'); 
            $file_save_name = 'AD'.date('ymdhis').mt_rand(100,999).$file_ext;
            if(!file_exists($file_save_path)){@mkdir($file_save_path);}
            move_uploaded_file($_FILES['ads_img']['tmp_name'],$file_save_path.$file_save_name);
            //生成入库数据
            //生成图片入库信息
            $img_path ='http://www.yishu.com/'.$web_path;
            $img_name = $file_save_name;

            $ads_data = array_merge($up_data,array(
                'img_path' =>$img_path,
                'img_name' =>$img_name,
                'add_time' =>time(),
            ));

            $flag = M('Ads')->data($ads_data)->add();

            if($flag){
                $this->success('添加成功');
            }else{
                $this->success('添加失败');
            }
        }
    
        //广告位删除的按钮
        public function del($ad_id){
            if(empty($ad_id)){
                $this->error('数据错误');
            }

            $flag = M('Ads')->where(array('ad_id'=>$ad_id))->limit(1)->save(array('enabled'=>0));

            if($flag){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        
        }


        //广告位修改的页面
        public function uindex(){
           $ad_id = I('get.ad_id');
           $ad = M('Ads')->where(array('ad_id'=>$ad_id))->find();
           if(empty($ad)){
                exit('广告位不存在');
           }
           $this->ad = $ad;
           $this->display('Ads/uindex'); 
        }


        //广告修改
        public function update(){
            $up_data = I('post.');
            if($_FILES['ads_img']['error']==0){
            $dir_name = dirname($_SERVER['SCRIPT_FILENAME']);
            //.DIRECTORY_SEPARATOR.
            $web_path = '/Public/upload/Ads/'.date('ym').'/';
            $file_save_path = $dir_name.$web_path;
            if($_FILES['ads_img']['error']!=0){
                  $this->error('图片上传出现错误');
              }
            
            //获取图片的后缀
            //$file_ext = substr(strrchr($_FILES['ads_img']['name'],'.'),1); 
            $file_ext = strrchr($_FILES['ads_img']['name'],'.'); 
            $file_save_name = 'AD'.date('ymdhis').mt_rand(100,999).$file_ext;
            if(!file_exists($file_save_path)){@mkdir($file_save_path);}
            move_uploaded_file($_FILES['ads_img']['tmp_name'],$file_save_path.$file_save_name);
            //生成入库数据
            //生成图片入库信息
            $img_path ='http://www.yishu.com/'.$web_path;
            $img_name = $file_save_name;

            $up_data = array_merge($up_data,array(
                'img_path' =>$img_path,
                'img_name' =>$img_name,
            ));
            }

            $up_date['modity_time'] = time();

            $flag = M('Ads')->data($up_data)->save();
            
            if($flag){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }


	}
