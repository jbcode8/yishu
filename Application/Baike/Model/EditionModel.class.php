<?php
// +----------------------------------------------------------------------
// | 艺术百科词条版本库模型文件
// +----------------------------------------------------------------------
// | Author: mahongbing <2256439041@qq.com>
// +----------------------------------------------------------------------

namespace Baike\Model;


class EditionModel extends BaikeModel {

    protected $_validate = array(
        array('title', 'require', '词条标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('summary', 'require', '词条摘要不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('reason', 'require', '没有输入编辑原因', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('content', 'require', '词条内容不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('tag', 'require', '词条标签不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('type', '1,4', '编辑类型只能为1-4之间的数字', self::MUST_VALIDATE, 'between', self::MODEL_INSERT)
    );
    protected $_auto = array(
        array('authorid', 'getUid', self::MODEL_INSERT, 'callback'),
        //TODO 创建ID标识设为1，待后期改为登录用户ID标识

        array('time', 'time', self::MODEL_INSERT, 'function'),
        array('visible', 0, self::MODEL_INSERT, 'string'),
        array('excellent', 0, self::MODEL_INSERT, 'string'),
        array('big', 0, self::MODEL_INSERT, 'string'),
        array('ip', 'get_client_ip', self::MODEL_INSERT, 'function'),
        array('words', 'countWords', self::MODEL_BOTH, 'callback'),
        array('imginfo', 'splitImg', self::MODEL_BOTH, 'callback'),
        array('content', 'getContent', self::MODEL_BOTH, 'callback')
    );
	/**
     * 获取用户ID
     */
	public function getUid(){
		return getLoginStatus()['mid'];
	}

    /**
     * 计算字符串的字数
     */
    function countWords() {
        return floor(2/3*strlen(htmlspecialchars_decode($_POST['content'])));
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
            $imginfo[$key]['description'] = $value[2];
            $imginfo[$key]['path'] = $value[1];
        }
        if($imginfo)
            return json_encode($imginfo);
        return '';
    }

    /**
     * 去除中的js代码
     */
    public function getContent(){
        $content = stripscript($_POST['content']);
        return $content;
    }
}