<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-13
 * Time: ����4:18
 */

namespace Paimai\Model;


use Think\Model;

class PaimaiCommentModel extends Model{
    /**
     * �Զ���֤
     */
    protected $_validate = array(
        array("comment_content","require","�������ݲ���Ϊ��"),
        // array("goods_starttime","0,30","ר�����ܴ���30���ַ�","3","length"),
    );
    //�Զ����
    protected $_auto = array (
        array("comment_createtime","time","1","function"),
    );
} 