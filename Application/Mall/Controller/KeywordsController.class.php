<?php

// +----------------------------------------------------------------------
// | 艺术网-古玩城 后台 搜索 控制器文件
// +----------------------------------------------------------------------
// | Author: Rain Zen # 2014.01.18
// +----------------------------------------------------------------------

namespace Mall\Controller;
use Admin\Controller\AdminController;

class KeywordsController extends AdminController {

    // 列表信息
    public function index(){

        // 搜索的条件语句
        $kw = I('kw', '', 'trim');

        // 搜索条件初始化
        $condition = array();

        // 组装搜索条件
        if(!empty($kw)){

            // 关键字
            empty($kw) OR $condition['words'] = array('like', '%'.$kw.'%');
            $this->assign('kw', $kw);
        }

        $list = $this->lists('Keywords', $condition);
        $this->assign('_list', $list);
        $this->display();
    }

    // 状态(0：正常；1：推荐；2：锁定)
    public function editstatus(){

        // 获取信息ID
        isset($_GET['key_id']) AND $key_id = intval($_GET['key_id']);
        isset($key_id) OR $this->error('信息不存在或者已删除！');

        // 获取状态值
        isset($_GET['status']) AND $status = intval($_GET['status']);
        isset($status) OR $this->error('参数有误！');

        $where['key_id'] = $key_id;
        $field['status'] = $status;

        if(D('Keywords')->where($where)->setField($field) !== false){
            $this->success('状态更新成功！');
        }else{
            $this->error('状态更新失败！');
        }
    }

	//导入
    public function import(){
            if (isset($_FILES['excel']['size']) && $_FILES['excel']['size'] != null) {
               $upload = new \Think\Upload();
				$upload->maxSize   =     20000000;
				$upload->exts      =     array('xlsx');
				$upload->savePath  =     '../Uploads/Mall/Excel/';
				$upload->subName    =    array('date','Ymd');
				// 上传文件     
				$info   =   $upload->uploadOne($_FILES['excel']);    
				if(!$info) {
					// 上传错误提示错误信息        
					$this->error($upload->getError());
				}else{
					// 上传成功        
					//print_r($info);
				}

            }

			
            if(is_array($info) && !empty($info)){
                $savePath = '/www/web/test.yishu.com/Uploads/Mall/Excel/'.date('Ymd').'/'. $info['savename'];
            }else{
                //alert('error', '上传失败', U('hrm/punch/index'));
				$this->error('未选择任何文件');
            }

			

            //import("ORG.Util.PHPExcel");
            //$PHPExcel = new \Org\Util\PHPExcel();
			import("Org.Util.PHPExcel");
			$filename=$savePath;
			//创建PHPExcel对象，注意，不能少了\
			$PHPExcel=new \PHPExcel();
			
            //如果excel文件后缀名为.xls，导入这个类
			//import("Org.Util.PHPExcel.Reader.Excel5");
			//$PHPReader=new \PHPExcel_Reader_Excel5();
			//如果excel文件后缀名为.xlsx，导入这下类
			import("Org.Util.PHPExcel.Reader.Excel2007");
			$PHPReader=new \PHPExcel_Reader_Excel2007();

			//echo $filename;exit;
			//载入文件
			$PHPExcel=$PHPReader->load($filename);

			//获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
			$currentSheet=$PHPExcel->getSheet(0);
			//获取总列数
			$allColumn=$currentSheet->getHighestColumn();
			//获取总行数
			$allRow=$currentSheet->getHighestRow();
			
			//循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
			for($currentRow=1;$currentRow<=$allRow;$currentRow++){
			//从哪列开始，A表示第一列
				for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
					//数据坐标
					$address=$currentColumn.$currentRow;
					//读取到的数据，保存到数组$arr中
					$arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
				}
			}

			$Keywords = M('Keywords','yishu_mall_');
			foreach($arr as $val){
				$data['words'] = trim($val['A']);
				$data['create_time'] = time();
				$exist = $Keywords->where(array('words'=>$data['words']))->count();
				if(!$exist){
					$Keywords->add($data);
					
				}
			}
			$this->success('导入成功');
    }
}