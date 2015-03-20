<?php
// +----------------------------------------------------------------------
// | 艺术百科词条模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Model;

class DocModel extends BaikeModel {

    //自动验证规则
    protected $_validate = array(
        array('title', '1,78', '词条标题由1至78个字符组成', self::MUST_VALIDATE, 'length', self::MODEL_INSERT),
        array('cid', 'require', '没有指定词条分类', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('cid', 'checkCid', '请选择最下级分类', self::EXISTS_VALIDATE, 'callback', self::MODEL_BOTH),
        array('letter', 'require', '没有指定词条首字母', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('letter', 'A,Z', '分类排序只能为 A 至 Z 的字母', self::VALUE_VALIDATE, 'between', self::MODEL_BOTH),
        array('tag', 'require', '词条标签不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('summary', 'require', '词条描述不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('content', 'require', '词条内容不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    //填充类容
    protected $_auto = array(
        array('authorid', 'getUid', self::MODEL_INSERT, 'callback'),
        array('lasteditorid', 'getUid', self::MODEL_BOTH, 'callback'),
        //TODO 创建ID标识设为1，待后期改为登录用户ID标识

        array('letter', 'getFirstLetter', self::MODEL_INSERT, 'callback'),
        array('time', 'time', self::MODEL_INSERT, 'function'),
        array('lastedit', 'time', self::MODEL_BOTH, 'function'),
        array('views', 0, self::MODEL_INSERT, 'string'),
        array('edits', 0, self::MODEL_INSERT, 'string'),
        array('editions', 1, self::MODEL_INSERT, 'string'),
        array('visible', 0, self::MODEL_INSERT, 'string'),
        array('type', 0, self::MODEL_INSERT, 'string'),
        array('imginfo', 'splitImg', self::MODEL_BOTH, 'callback'),
        array('content', 'getContent', self::MODEL_BOTH, 'callback')
    );


    //检测分类是否是最下级目录
    public function checkCid($data) {
        $cate = D('Category')->category();
        $childs = category_childids($cate, $data);
        if ($childs)
            return false;
        return true;
    }


    /**
     * 解析词条栏目
     * @param $html
     * @param string $preg
     * @return array
     */
    public function  splithtml($html,$preg='/(<div\s+class=\"?hdwiki_tmml\"?\s*>.+?<\/div>)/i'){
        $arrhtml=preg_split($preg,$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        $count=count($arrhtml);
        for($i=0;$i<$count;$i++){
            if(preg_match($preg,$arrhtml[$i])){
                preg_match('/hdwiki_tmm(l+)/i',$arrhtml[$i],$l_num);
                $resarr[$i]['value']=strip_tags($arrhtml[$i]);
                $resarr[$i]['flag']=strlen($l_num[1]);
                continue;
            }
            $resarr[$i]['value']=$arrhtml[$i];
            $resarr[$i]['flag']=0;
        }
        unset($arrhtml);
        return $resarr;
    }

    /**
     * 解析词条的栏目
     * @param  array    $section
     * @return array
     */
    function getsections($section){
        $sectionlist=array();
        $secounts = count($section);
        for($i=0;$i<$secounts;$i++){
            if($section[$i]['flag']==1){
                $sectionlist[]=array('key'=>$i,'value'=>$section[$i]['value']);
            }
        }
        unset($section);
        return $sectionlist;
    }


    /**
     * 自动填充图片组信息
     */
    public function splitImg(){
        $content = $_POST['content']; 
//        $preg = '/<img\s+title=\"(\S+)\"\s+alt=\"(\S+)\"\s+src=\"(\S+)\"\s+\/>/i';
        $preg = '/<div.*?><a.*?><img\s+title=\"\S+\"\s+alt=\"\S+\"\s+src=\"(\S+)\"\s*?\/*?><\/a><strong>(.*?)<\/strong><\/div>/i';
        $imgArr = array();
        preg_match_all($preg, $content, $imgArr, PREG_SET_ORDER);
        $imginfo = array();
        foreach($imgArr as $key => $value){
            $imginfo[$key]['path'] = $value[1];
            $imginfo[$key]['description'] = $value[2];
        }
        if($imginfo) 
            return json_encode($imginfo);
        return '';
    }

    /**
     * 连接html
     * @param  array $arrhtml
     * @return string
     */
    public function joinhtml($arrhtml){
        $html='';
        $count=count($arrhtml);
        for($i=0;$i<$count;$i++){
            if($arrhtml[$i]['flag']==1){
                $html.="<div class=\"hdwiki_tmml\">".$arrhtml[$i]['value']."</div>";
            }else {
                $html.=$arrhtml[$i]['value'];
            }
        }
        return $html;
    }

    /**
     * 填充词条首字母
     */
    public function getFirstLetter(){
        return get_letter($_POST['title']);
    }

    /**
     * 增加词条的浏览次数
     * @param $did
     */
    public function addViews($did){
        $this->where(array('did'=>$did))->save(array('views'=>array('exp','`views`+1')));
    }


    /**
     * 去除中的js代码
     */
    public function getContent(){
        $content = stripscript($_POST['content']);
        return $content;
    }

	/**
     * 获取用户ID
     */
	public function getUid(){
		return getLoginStatus()['mid'];
	}

}